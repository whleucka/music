<?php

namespace Echo\Framework\Admin;

use Echo\Interface\Admin\Module as AdminModule;
use Echo\Framework\Admin\View\Table\Table;
use Echo\Framework\Admin\View\Table\Schema as TableSchema;

class Module implements AdminModule
{
    protected string $module_title = '';

    /**
     * GET index data method
     */
    public function index(): array
    {
        return [
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
            "module" => $this->getModuleData(),
            "content" => $this->generateContent("create"),
        ];
    }

    /**
     * GET edit data method
     */
    public function edit(int $id): array
    {
        return [
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
