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
        return $this->render("admin/module.html.twig", $this->getTableData());
    }

    #[Get("/create", "admin.create")] 
    public function create(): string
    {
        return $this->render("admin/form.html.twig", $this->getFormData());
    }

    #[Get("/{id}", "admin.show")] 
    public function show(int $id): string
    {
        return $this->render("admin/form.html.twig", $this->getFormData($id));
    }

    #[Get("/{id}/edit", "admin.edit")] 
    public function edit(int $id): string
    {
        return $this->render("admin/form.html.twig", $this->getFormData($id));
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

    private function getModuleData()
    {
        return [
            "link" => $this->module_link,
            "title" => $this->module_title,
            "icon" => $this->module_icon,
        ];
    }

    private function getUserData() 
    {
        $user = user();
        return [
            "name" => $user->first_name . " " . $user->surname,
            "email" => $user->email,
            "avatar" => $user->gravatar(38),
        ];
    }

    public function getTableData(): array
    {
        return [
            "user" => $this->getUserData(),
            "module" => $this->getModuleData(),
            "content" => $this->renderTable(),
        ];
    }

    public function getFormData(?int $id = null): array
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
            "id" => $id,
            "user" => $this->getUserData(),
            "module" => $this->getModuleData(),
            "labels" => array_keys($this->form_columns),
            "data" => $data,
            "title" => $id ? "Edit $id" : "Create New",
            "button" => $id ? "Save changes" : "Create",
        ];
    }
}
