<?php

namespace App\Http\Controllers\Admin;

use Echo\Framework\Http\Controller;
use Echo\Framework\Routing\Group;
use Echo\Framework\Routing\Route\{Get, Post};
use Echo\Framework\Session\Flash;
use PDO;
use PDOStatement;
use RuntimeException;
use Throwable;
use Twig\TwigFunction;

#[Group(path_prefix: "/admin", middleware: ["auth"])]
abstract class AdminController extends Controller
{
    protected array $actions = [];

    protected string $module_icon = "";
    protected string $module_link = "";
    protected string $module_title = "";

    protected string $table_pk = "id";
    protected string $table_name = "";
    protected array $table_columns = [];
    protected array $table_actions = [];

    protected int $per_page = 25;
    protected int $page = 1;
    protected int $total_pages = 1;
    protected int $total_results = 0;
    protected int $pagination_links = 2;

    protected array $query_where = [];
    protected array $query_params = [];
    protected array $query_order_by = ["id DESC"];

    protected array $form_columns = [];
    protected array $form_controls = [];
    protected array $form_readonly = [];
    protected array $form_disabled = [];

    protected array $search_columns = [];
    protected string $search_term = "";

    protected bool $has_edit = true;
    protected bool $has_create = true;
    protected bool $has_delete = true;
    protected bool $export_csv = true;

    protected array $validation_rules = [];

    #[Get("/", "admin.index")]
    public function index(): string
    {
        $this->processRequest($this->request->request);
        return $this->renderModule($this->getModuleData());
    }

    #[Get("/modal/create", "admin.create")]
    public function create(): string
    {
        if (!$this->hasCreate()) {
            return $this->permissionDenied();
        }
        return $this->renderModule($this->getFormData(null, 'create'));
    }

    #[Get("/modal/filter", "admin.filters")]
    public function filters(): string
    {
        $this->processRequest($this->request->request);
        return $this->renderFilter();
    }

    #[Post("/modal/filter", "admin.set-filters")]
    public function set_filters(): void
    {
        $this->processRequest($this->request->request);
        header("HX-Redirect: /admin/{$this->module_link}");
        exit;
    }

    #[Get("/modal/{id}", "admin.show")]
    public function show(int $id): string
    {
        if (!$this->hasShow($id)) {
            return $this->permissionDenied();
        }
        return $this->renderModule($this->getFormData($id, 'show'));
    }

    #[Get("/modal/{id}/edit", "admin.edit")]
    public function edit(int $id): string
    {
        if (!$this->hasEdit($id)) {
            return $this->permissionDenied();
        }
        return $this->renderModule($this->getFormData($id, 'edit'));
    }

    #[Post("/", "admin.store")]
    public function store(): string
    {
        if (!$this->hasCreate()) {
            return $this->permissionDenied();
        }
        $valid = $this->validate($this->validation_rules, "store");
        if ($valid) {
            $result = $this->handleStore((array)$valid);
            if ($result) {
                Flash::add("success", "Create successful");
                header("HX-Redirect: /admin/{$this->module_link}");
                exit;
            }
        }
        // Request is invalid
        Flash::add("warning", "Validation error");
        header("HX-Retarget: .modal-dialog");
        header("HX-Reselect: .modal-content");
        return $this->create();
    }

    #[Post("/{id}/update", "admin.update")]
    public function update(int $id): string
    {
        if (!$this->hasEdit($id)) {
            return $this->permissionDenied();
        }
        $valid = $this->validate($this->validation_rules, "update");
        if ($valid) {
            $result = $this->handleUpdate($id, (array)$valid);
            if ($result) {
                Flash::add("success", "Update successful");
                header("HX-Redirect: /admin/{$this->module_link}");
                exit;
            }
        }
        // Request is invalid
        Flash::add("warning", "Validation error");
        header("HX-Retarget: .modal-dialog");
        header("HX-Reselect: .modal-content");
        return $this->edit($id);
    }

    #[Post("/{id}/destroy", "admin.destroy")]
    public function destroy(int $id): string
    {
        if (!$this->hasDelete($id)) {
            return $this->permissionDenied();
        }
        $result = $this->handleDestroy($id);
        if ($result) {
            Flash::add("success", "Delete successful");
        }
        header("HX-Retarget: #module");
        header("HX-Reselect: #module");
        header("HX-Reswap: outerHTML");
        return $this->index();
    }

    private function setSession(string $key, mixed $value): void
    {
        $data = session()->get($this->module_link);
        $data[$key] = $value;
        session()->set($this->module_link, $data);
    }

    private function getSession(string $key): mixed
    {
        $data = session()->get($this->module_link);
        return $data[$key] ?? null;
    }

    function streamCSV(iterable $rows, array $columns = [], string $filename = 'export.csv')
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output_handle = fopen('php://output', 'w');

        if ($output_handle === false) {
            throw new RuntimeException("Unable to open output stream");
        }

        if (!empty($columns)) {
            fputcsv($output_handle, array_keys($columns));
        }

        foreach ($rows as $row) {
            if (!empty($columns) && array_is_list($row) === false) {
                $ordered_row = [];
                foreach ($columns as $title => $subquery) {
                    $key = $this->getAlias($subquery);
                    $ordered_row[] = $row[$key] ?? '';
                }
                fputcsv($output_handle, $ordered_row);
            } else {
                fputcsv($output_handle, $row);
            }

            flush(); // Free memory buffer
        }

        fclose($output_handle);
        exit;
    }


    protected function processRequest(?object $request): void
    {
        // Set current page
        if (isset($request->page) && intval($request->page) > 0) {
            $this->setSession("page", $request->page);
        }

        // Search term
        if (isset($request->filter_clear)) {
            $this->setSession("search_term", null);
        } else {
            if (isset($request->filter_search)) {
                $this->setSession("search_term", $request->filter_search);
                $this->setSession("page", 1);
            }
        }

        // Export CSV
        if (isset($request->export_csv)) {
            $rows = $this->runTableQuery()->fetchAll(PDO::FETCH_ASSOC);
            $this->streamCSV($rows, $this->table_columns, $this->module_link . '_export.csv');
        }

        // Assign properties
        if (!empty($this->table_columns) && $this->table_name) {
            $this->page = $this->getSession("page") ?? 1;
            $this->search_term = $this->getSession("search_term") ?? '';
            // Set where
            if ($this->search_term) {
                $where = [];
                foreach ($this->search_columns as $title) {
                    $query = $this->table_columns[$title];
                    $column = $this->getSubquery($query);
                    if ($column) {
                        $where[] = "$column LIKE ?";
                        $this->query_params[] = "%{$this->search_term}%";
                    }
                }
                $this->query_where[] = implode(" OR ", $where);
            }

            $this->total_results = $this->runTableQuery(false)->rowCount();
            $this->total_pages = ceil($this->total_results / $this->per_page);
        }
    }

    protected function getFormData(?int $id, string $type): array
    {
        return [
            ...$this->getCommonData(),
            "content" => $this->renderForm($id, $type),
        ];
    }

    protected function getModuleData(): array
    {
        return [
            ...$this->getCommonData(),
            "content" => $this->renderTable(),
        ];
    }

    protected function hasCreate(): bool
    {
        return $this->has_create && !empty($this->form_columns);
    }

    protected function hasShow(int $id): bool
    {
        return !empty($this->form_columns);
    }

    protected function hasEdit(int $id): bool
    {
        return $this->has_edit && !empty($this->form_columns);
    }

    protected function hasDelete(int $id): bool
    {
        return $this->has_delete;
    }

    protected function renderModule(array $data): string
    {
        return $this->render("admin/module.html.twig", $data);
    }

    protected function renderFilter(): string
    {
        return $this->render("admin/filter.html.twig", [
            "post" => "/admin/{$this->module_link}/modal/filter",
            "search" => [
                "show" => !empty($this->search_columns),
                "term" => $this->search_term,
            ]
        ]);
    }

    private function registerFunctions()
    {
        $has_create = new TwigFunction("has_create", fn() => $this->hasCreate());
        $has_edit = new TwigFunction("has_edit", fn(int $id) => $this->hasEdit($id));
        $has_show = new TwigFunction("has_show", fn(int $id) => $this->hasShow($id));
        $has_delete = new TwigFunction("has_delete", fn(int $id) => $this->hasDelete($id));
        $has_row_actions = new TwigFunction("has_row_actions", fn() => $this->has_edit || $this->has_delete || !empty($this->table_actions));
        $control = new TwigFunction("control", fn(string $column, ?string $value) => $this->control($column, $value));
        twig()->addFunction($has_create);
        twig()->addFunction($has_edit);
        twig()->addFunction($has_show);
        twig()->addFunction($has_delete);
        twig()->addFunction($has_row_actions);
        twig()->addFunction($control);
    }

    protected function renderTable(): string
    {
        if (empty($this->table_columns) || !$this->table_name) return '';

        $rows = $this->runTableQuery()->fetchAll();

        // Table caption
        $start = 1 + ($this->page * $this->per_page) - $this->per_page;
        $end = min($this->page * $this->per_page, $this->total_results);

        // Setup functions
        $this->registerFunctions();

        return $this->render("admin/table.html.twig", [
            ...$this->getCommonData(),
            "headers" => array_keys($this->table_columns),
            "show_filter" => !empty($this->search_columns),
            "show_export" => $this->export_csv,
            "caption" => $this->total_pages > 1
                ? "Showing {$start}–{$end} of {$this->total_results} results"
                : "",
            "data" => [
                "rows" => $rows,
                "page" => $this->page,
                "total_pages" => $this->total_pages,
                "total_results" => $this->total_results,
                "pagination_links" => $this->pagination_links,
            ]
        ]);
    }

    protected function renderForm(?int $id, string $type): string
    {
        if (empty($this->form_columns) || !$this->table_name) return '';

        $data = $this->runFormQuery($id);

        if ($type === "edit") {
            $data = $data->fetch();
            $title = "Edit $id";
            $submit = "Save Changes";
        } else if ($type === "show") {
            $submit = false;
            $data = $data->fetch();
            $title = "View $id";
            foreach ($data as $column => $value) {
                // All columns are readonly
                if (!in_array($column, $this->form_readonly)) {
                    $this->form_readonly[] = $column;
                }
            }
        } else if ($type === "create") {
            $title = "Create New";
            $submit = "Create";
        }

        // Setup functions
        $this->registerFunctions();

        return $this->render("admin/form-modal.html.twig", [
            ...$this->getCommonData(),
            "type" => $type,
            "id" => $id,
            "title" => $title,
            "post" => $id
                ? "/admin/{$this->module_link}/$id/update"
                : "/admin/{$this->module_link}",
            "submit" => $submit,
            "labels" => array_keys($this->form_columns),
            "data" => $data,
        ]);
    }

    private function runTableQuery(bool $limit = true): bool|PDOStatement
    {

        // Table columns must always contain table_pk
        if (!in_array($this->table_pk, $this->table_columns)) {
            $columns[strtoupper($this->table_pk)] = $this->table_pk;
            $this->table_columns = [
                ...$columns,
                ...$this->table_columns,
            ];
        }

        $q = qb()->select(array_values($this->table_columns))
            ->from($this->table_name)
            ->params($this->query_params)
            ->where($this->query_where)
            ->orderBy($this->query_order_by);

        if ($limit) {
            $limit = $this->per_page;
            $offset = $this->per_page * ($this->page - 1);
            $q->limit($limit)->offset($offset);
        }

        return $q->execute();
    }

    private function runFormQuery(?int $id): array|bool|PDOStatement
    {
        if (is_null($id)) {
            $data = [];
            foreach ($this->form_columns as $value) {
                $column = $this->getAlias($value);
                $data[$column] = null;
            }
            return $data;
        }
        return qb()->select(array_values($this->form_columns))
            ->from($this->table_name)
            ->where(["$this->table_pk = ?"], $id)
            ->execute();
    }

    private function getSubquery(string $str): string
    {
        $str = explode(" as ", $str);
        return $str[0];
    }

    private function getAlias(string $str): string
    {
        $str = explode(" as ", $str);
        return end($str);
    }

    protected function getCommonData(): array
    {
        return [
            "user" => [
                "name" => $this->user->first_name . " " . $this->user->surname,
                "email" => $this->user->email,
                "avatar" => $this->user->gravatar(38),
            ],
            "module" => [
                "link" => $this->module_link,
                "title" => $this->module_title,
                "icon" => $this->module_icon,
            ]
        ];
    }

    protected function handleDestroy(int $id): bool
    {

        try {
            $result = qb()->delete()
                ->from($this->table_name)
                ->where(["{$this->table_pk} = ?"], $id)
                ->execute();
            if ($result) {
                return true;
            }
            return false;
        } catch (Throwable $ex) {
            error_log($ex->getMessage());
            Flash::add("danger", "Delete failed. Check logs.");
            return false;
        }
    }

    protected function handleUpdate(int $id, array $request): bool
    {
        try {
            // Set the params before the where clause
            // so that we get the correct query param count
            $result = qb()->update($request)
                ->params(array_values($request))
                ->table($this->table_name)
                ->where(["{$this->table_pk} = ?"], $id)
                ->execute();
            if ($result) {
                return true;
            }
            return false;
        } catch (Throwable $ex) {
            error_log($ex->getMessage());
            Flash::add("danger", "Update failed. Check logs.");
            return false;
        }
    }

    protected function handleStore(array $request): mixed
    {
        try {
            $result = qb()->insert($request)
                ->into($this->table_name)
                ->params(array_values($request))
                ->execute();
            if ($result) {
                return db()->lastInsertId();
            }
            return false;
        } catch (Throwable $ex) {
            error_log($ex->getMessage());
            Flash::add("danger", "Create failed. Check logs.");
            return false;
        }
    }

    public function addValidationRule(array $rules, string $field, string $rule): array
    {
        $rules[$field][] = $rule;
        return $rules;
    }

    function removeValidationRule(array $rules, string $field, string $remove): array
    {
        if (!isset($rules[$field])) {
            return $rules;
        }

        $rules[$field] = array_filter(
            $rules[$field],
            fn($rule) => explode(':', $rule, 2)[0] !== explode(':', $remove, 2)[0] || $rule !== $remove
        );

        if (empty($rules[$field])) {
            unset($rules[$field]);
        }

        return $rules;
    }

    private function control(string $column, ?string $value)
    {
        return match ($this->form_controls[$column]) {
            "input" => $this->renderControl("input", $column, $value),
            "email" => $this->renderControl("input", $column, $value, [
                "type" => "email",
                "autocomplete" => "email",
            ]),
            "password" => $this->renderControl("input", $column, $value, [
                "type" => "password",
                "autocomplete" => "current-password",
            ]),
            default => $this->renderControl("text", $column, $value),
        };
    }

    private function getClassname(string $column)
    {
        $validation_errors = $this->getValiationErrors();
        $request = $this->request->request;
        $classname = ["form-control"];
        if (isset($request->$column)) {
            $classname[] = isset($validation_errors[$column]) ? 'is-invalid' : 'is-valid';
        }
        return implode(" ", $classname);
    }

    private function renderControl(string $type, string $column, ?string $value, array $data = [])
    {
        $default = [
            "type" => "input",
            "class" => $this->getClassname($column),
            "id" => $column,
            "name" => $column,
            "title" => array_search($column, $this->form_columns),
            "value" => $value,
            "placeholder" => "",
            "alt" => null,
            "minlength" => null,
            "maxlength" => null,
            "size" => null,
            "list" => null,
            "min" => null,
            "max" => null,
            "height" => null,
            "width" => null,
            "step" => null,
            "accpet" => null,
            "pattern" => null,
            "dirname" => null,
            "inputmode" => null,
            "autocomplete" => null,
            "checked" => null,
            "autofocus" => null,
            "readonly" => in_array($column, $this->form_readonly),
            "disabled" => in_array($column, $this->form_disabled),
        ];
        $template_data = array_merge($default, $data);
        return $this->render("admin/controls/$type.html.twig", $template_data);
    }
}
