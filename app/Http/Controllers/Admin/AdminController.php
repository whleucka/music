<?php

namespace App\Http\Controllers\Admin;

use Echo\Framework\Http\Controller;
use Echo\Framework\Routing\Route\{Get, Post};

class AdminController extends Controller
{
    protected string $module_icon = '';
    protected string $module_title = '';

    #[Get("/admin/{module}", "admin.index", ["auth"])] 
    public function index(string $module): string
    {
        return $this->render("admin/module.html.twig", $this->getIndex($module));
    }

    #[Get("/admin/{module}/create", "admin.create", ["auth"])] 
    public function create(string $module): string
    {
        return $this->render("admin/form.html.twig", $this->getCreate($module));
    }

    #[Get("/admin/{module}/{id}", "admin.show", ["auth"])] 
    public function show(string $module, int $id): string
    {
        return $this->render("admin/form.html.twig", $this->getShow($module, $id));
    }

    #[Get("/admin/{module}/{id}/edit", "admin.edit", ["auth"])] 
    public function edit(string $module, int $id): string
    {
        return $this->render("admin/form.html.twig", $this->getEdit($module, $id));
    }

    #[Post("/admin/{module}", "admin.store", ["auth"])]
    public function store(string $module)
    {
        dd("WIP");
    }

    #[Post("/admin/{module}/{id}/update", "admin.update", ["auth"])]
    public function update(string $module, int $id)
    {
        dd("WIP");
    }

    #[Post("/admin/{module}/{id}/destroy", "admin.destroy", ["auth"])]
    public function destroy(string $module, int $id)
    {
        dd("WIP");
    }

    protected function indexContent(string $module): string
    {
        return "";
    }

    protected function showContent(string $module, int $id): string
    {
        return "";
    }

    protected function editContent(string $module, int $id): string
    {
        return "";
    }

    protected function createContent(string $module): string
    {
        return "";
    }

    private function getModuleData()
    {
        return [
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

    /**
     * GET index data method
     */
    public function getIndex(string $module): array
    {
        return [
            "user" => $this->getUserData(),
            "module" => $this->getModuleData(),
            "content" => $this->indexContent($module),
        ];
    }

    /**
     * GET create data method
     */
    public function getCreate(string $module): array
    {
        return [
            "user" => $this->getUserData(),
            "module" => $this->getModuleData(),
            "content" => $this->createContent($module),
        ];
    }

    /**
     * GET show data method
     */
    public function getShow(string $module, int $id): array
    {
        return [
            "user" => $this->getUserData(),
            "module" => $this->getModuleData(),
            "content" => $this->showContent($module, $id),
        ];
    }

    /**
     * GET edit data method
     */
    public function getEdit(string $module, int $id): array
    {
        return [
            "user" => $this->getUserData(),
            "module" => $this->getModuleData(),
            "content" => $this->editContent($module, $id),
        ];
    }
}
