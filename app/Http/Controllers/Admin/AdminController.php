<?php

namespace App\Http\Controllers\Admin;

use Echo\Framework\Http\Controller;
use Echo\Framework\Routing\Route\{Get, Post};
use Echo\Interface\Admin\Module;

class AdminController extends Controller
{
    public function __construct(private ?Module $module)
    {
        if (is_null($module)) {
            $this->pageNotFound();
        }
    }

    #[Get("/admin/{module}", "admin.index", ["auth"])] 
    public function index(string $module): string
    {
        return $this->render("admin/module.html.twig", $this->module->index());
    }

    #[Get("/admin/{module}/create", "admin.create", ["auth"])] 
    public function create(string $module): string
    {
        return $this->render("admin/form.html.twig", $this->module->create());
    }

    #[Get("/admin/{module}/{id}", "admin.show", ["auth"])] 
    public function show(string $module, int $id): string
    {
        return $this->render("admin/form.html.twig", $this->module->show($id));
    }

    #[Get("/admin/{module}/{id}/edit", "admin.edit", ["auth"])] 
    public function edit(string $module, int $id): string
    {
        return $this->render("admin/form.html.twig", $this->module->edit($id));
    }

    #[Post("/admin/{module}", "admin.store", ["auth"])]
    public function store(string $module)
    {
        $this->module->store();
    }

    #[Post("/admin/{module}/{id}/update", "admin.update", ["auth"])]
    public function update(string $module, int $id)
    {
        $this->module->update($id);
    }

    #[Post("/admin/{module}/{id}/destroy", "admin.destroy", ["auth"])]
    public function destroy(string $module, int $id)
    {
        $this->module->destroy($id);
    }
}
