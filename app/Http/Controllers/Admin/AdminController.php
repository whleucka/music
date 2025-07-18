<?php

namespace App\Http\Controllers\Dashboard;

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
        return $this->render("admin/index.html.twig");
    }

    #[Get("/admin/{module}/create", "admin.create", ["auth"])] 
    public function create(string $module): string
    {
        return $this->render("admin/index.html.twig");
    }

    #[Get("/admin/{id}", "admin.edit", ["auth"])] 
    public function edit(string $module, string $id): string
    {
        return $this->render("admin/index.html.twig");
    }

    #[Post("/admin", "admin.store", ["auth"])]
    public function store(string $module)
    {
    }

    #[Post("/admin/{id}/update", "admin.update", ["auth"])]
    public function update(string $module, string $id)
    {
    }

    #[Post("/admin/{id}/destroy", "admin.destroy", ["auth"])]
    public function destroy(string $module, string $id)
    {
    }
}
