<?php

namespace App\Http\Controllers\Admin;

use Echo\Framework\Http\Controller;
use Echo\Framework\Routing\Route\{Get, Post};
use Echo\Framework\Routing\Group;

#[Group(middleware: ["auth"])]
class AdminController extends Controller
{
    protected string $module_icon = "";
    protected string $module_link = "";
    protected string $module_title = "";

    protected string $table_pk = "id";
    protected string $table_name = "";
    protected array $table_columns = [];

    protected int $per_page = 10;
    protected int $page = 1;
    protected int $total_pages = 1;
    protected int $total_results = 0;
    protected int $pagination_links = 3;

    protected array $query_where = [];
    protected array $query_params = [];
    protected array $query_order_by = ["id DESC"];

    protected array $form_columns = [];

    protected array $validation_rules = [
        "page" => ["integer"]
    ];

    #[Get("/", "admin.index")]
    public function index(): string
    {
        $valid = $this->validate($this->validation_rules);
        $this->processRequest($valid);
        return $this->renderModule($this->getModuleData());
    }

    #[Get("/create", "admin.create")]
    public function create(): string
    {
        return $this->renderModule($this->getFormData(null, false));
    }

    #[Get("/{id}", "admin.show")]
    public function show(int $id): string
    {
        return $this->renderModule($this->getFormData($id, true));
    }

    #[Get("/{id}/edit", "admin.edit")]
    public function edit(int $id): string
    {
        return $this->renderModule($this->getFormData($id, false));
    }

    #[Post("/{module}", "admin.store")]
    public function store()
    {
        dd("WIP");
    }

    #[Post("/{id}/update", "admin.update")]
    public function update(string $module, int $id)
    {
        dd("WIP");
    }

    #[Post("/destroy", "admin.destroy")]
    public function destroy(string $module, int $id)
    {
        dd("WIP");
    }

    private function setSession(string $key, mixed $value)
    {
        $data = session()->get($this->module_link);
        $data[$key] = $value;
        session()->set($this->module_link, $data);
    }

    private function getSession(string $key)
    {
        $data = session()->get($this->module_link);
        return $data[$key] ?? null;
    }

    protected function processRequest(?object $request)
    {
        if (!empty($this->table_columns) && $this->table_name) {
            $this->total_results = $this->runTableQuery(false)->rowCount();
            $this->total_pages = ceil($this->total_results / $this->per_page);
        }

        // Set current page
        if (isset($request->page) && $request->page > 0 && $request->page <= $this->total_pages) {
            $this->setSession("page", $request->page);
        }

        // Assign properties
        $this->page = $this->getSession("page") ?? 1;
    }

    protected function renderModule(array $data)
    {
        return $this->render("admin/module.html.twig", $data);
    }

    protected function getFormData(?int $id = null, bool $readonly): array
    {
        return [
            ...$this->getCommonData(),
            "content" => $this->renderForm($id, $readonly),
        ];
    }

    protected function getModuleData(): array
    {
        return [
            ...$this->getCommonData(),
            "content" => $this->renderTable(),
        ];
    }

    protected function renderTable(): string
    {
        if (empty($this->table_columns) || !$this->table_name) return '';
        // Table columns must always contain table_pk
        if (!in_array($this->table_pk, $this->table_columns)) {
            $columns[strtoupper($this->table_pk)] = $this->table_pk;
            $this->table_columns = [
                ...$columns,
                ...$this->table_columns,
            ];
        }

        $rows = $this->runTableQuery()->fetchAll();

        return $this->render("admin/table.html.twig", [
            ...$this->getCommonData(),
            "headers" => array_keys($this->table_columns),
            "data" => [
                "rows" => $rows,
                "page" => $this->page,
                "total_pages" => $this->total_pages,
                "total_results" => $this->total_results,
                "pagination_links" => $this->pagination_links,
            ]
        ]);
    }

    protected function renderForm(?int $id, bool $readonly): string
    {
        if (empty($this->form_columns) || !$this->table_name) return '';

        if ($id && !$readonly) {
            $title = "Edit $id";
            $button = "Save Changes";
        } else if ($id && $readonly) {
            $title = "View $id";
        } else {
            $title = "Create New";
            $button = "Create";
        }

        $data = $this->runFormQuery($id);

        return $this->render("admin/form.html.twig", [
            ...$this->getCommonData(),
            "readonly" => $readonly,
            "id" => $id,
            "title" => $title,
            "button" => $button,
            "labels" => array_keys($this->form_columns),
            "data" => is_array($data) ? $data : $data->fetch(),
        ]);
    }

    private function runTableQuery(bool $limit = true)
    {
        $q = qb()->select(array_values($this->table_columns))
            ->from($this->table_name)
            ->orderBy($this->query_order_by);

        if ($limit) {
            $limit = $this->per_page;
            $offset = $this->per_page * ($this->page - 1);
            $q->limit($limit)->offset($offset);
        }

        return $q->execute();
    }

    private function runFormQuery(?int $id)
    {
        if (is_null($id)) {
            $data = [];
            foreach ($this->form_columns as $key => $value) {
                $data[$key] = null;
            }
            return $data;
        }
        return qb()->select(array_values($this->form_columns))
            ->from($this->table_name)
            ->where(["$this->table_pk = ?"], $id)
            ->execute();
    }

    private function getCommonData()
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
}
