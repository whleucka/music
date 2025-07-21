<?php

namespace Echo\Framework\Admin\View\Form;

class Form
{
    public string $module = '';
    public array $columns = [];
    public array $types = [];

    public function __construct(private string $table_name) {}

    public function build(): array
    {
        return [
            "module" => $this->module,
            "headers" => array_keys($this->columns),
            "data" => $this->getData()
        ];
    }

    public function render(array $data)
    {
        return twig()->render("admin/form.html.twig", $data);
    }

    private function getData()
    {
        return qb()->select(array_values($this->columns))
            ->from($this->table_name)
            ->execute();
    }

}
