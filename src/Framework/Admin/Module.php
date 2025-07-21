<?php

namespace Echo\Framework\Admin;

use Echo\Interface\Admin\Module as AdminModule;

class Module implements AdminModule
{
    protected string $module_icon = '';
    protected string $module_title = '';

    /**
     * GET index data method
     */
    public function index(): array
    {
        return [
            "user" => $this->getUserData(),
            "module" => $this->getModuleData(),
            "content" => $this->generateContent("index"),
        ];
    }

    /**
     * GET create data method
     */
    public function create(): array
    {
        return [
            "user" => $this->getUserData(),
            "module" => $this->getModuleData(),
            "content" => $this->generateContent("create"),
        ];
    }

    /**
     * GET show data method
     */
    public function show(int $id): array
    {
        return [
            "user" => $this->getUserData(),
            "module" => $this->getModuleData(),
            "content" => $this->generateContent("show"),
        ];
    }

    /**
     * GET edit data method
     */
    public function edit(int $id): array
    {
        return [
            "user" => $this->getUserData(),
            "module" => $this->getModuleData(),
            "content" => $this->generateContent("edit"),
        ];
    }

    public function store() {}

    public function update(int $id) {}

    public function destroy(int $id) {}

    protected function indexContent(): string
    {
        return "";
    }

    protected function editContent(): string
    {
        return "";
    }

    protected function createContent(): string
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

    private function generateContent(string $mode): string
    {
        return match ($mode) {
            "index" => $this->indexContent(),
            "edit" => $this->editContent(),
            "create" => $this->createContent(),
        };
    }
}
