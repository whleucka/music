<?php

namespace App\Http\Controllers\Admin\Modules;

use App\Http\Controllers\Admin\AdminController;
use Echo\Framework\Admin\View\Form\Form;
use Echo\Framework\Admin\View\Form\Schema as FormSchema;
use Echo\Framework\Admin\View\Table\Table;
use Echo\Framework\Admin\View\Table\Schema as TableSchema;

class UsersController extends AdminController
{
    protected string $module_icon = '<i class="bi bi-people pe-1"></i>';
    protected string $module_title = "Users";

    protected function indexContent(string $module): string
    {
        return TableSchema::create("users", function(Table $table) use ($module) {
            $table->module = $module;
            $table->columns = [
                "ID" => "id",
                "UUID" => "uuid",
                "Name" => "CONCAT(first_name, ' ', surname)",
                "Email" => "email",
                "Created" => "created_at",
                "Updated" => "updated_at",
            ];
        });
    }

    protected function editContent(string $module, int $id): string
    {
        return FormSchema::create("users", function(Form $form) use ($module) {
            $form->module = $module;
            $form->columns = [
                "Email" => "email",
                "First Name" => "first_name",
                "Surname" => "surname",
                "Password" => "password",
            ];
        });
    }
}
