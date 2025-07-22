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

    protected string $table_name = "";
    protected array $table_columns = [];

    protected array $form_columns = [];

    #[Get("/", "admin.index")]
    public function index(): string
    {
        return $this->renderModule();
    }

    #[Get("/create", "admin.create")]
    public function create(): string
    {
        return $this->renderForm();
    }

    #[Get("/{id}", "admin.show")]
    public function show(int $id): string
    {
        return $this->renderForm($id, true);
    }

    #[Get("/{id}/edit", "admin.edit")]
    public function edit(int $id): string
    {
        return $this->renderForm($id);
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

    protected function renderModule()
    {
        return $this->render("admin/module.html.twig", $this->getModuleData());
    }

    protected function renderForm(?int $id = null, bool $readonly = false)
    {
        return $this->render("admin/form.html.twig", $this->getFormData($id, $readonly));
    }

    protected function renderTable(): string
    {
        if (empty($this->table_columns)) return '';
        $data = qb()->select(array_values($this->table_columns))
            ->from($this->table_name)
            ->execute();
        return $this->render("admin/table.html.twig", [
            "link" => $this->module_link,
            "caption" => "",
            "headers" => array_keys($this->table_columns),
            "data" => $data,
        ]);
    }

    protected function getFormData(?int $id = null, bool $readonly): array
    {
        if (is_null($id)) {
            $data = [];
            foreach ($this->form_columns as $key => $value) {
                $data[$key] = null;
            }
        } else {
            $data = qb()->select(array_values($this->form_columns))
                ->from($this->table_name)
                ->where(["id = ?"], $id)
                ->execute()->fetch();
        }
        return [
            ...$this->getCommonData(),
            "id" => $id,
            "labels" => array_keys($this->form_columns),
            "readonly" => $readonly,
            "data" => $data,
            "title" => $id ? "Edit $id" : "Create New",
            "button" => $id ? "Save changes" : "Create",
        ];
    }

    protected function getModuleData(): array
    {
        return [
            ...$this->getCommonData(),
            "content" => $this->renderTable(),
        ];
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
