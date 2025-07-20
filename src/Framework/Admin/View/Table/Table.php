<?php

namespace Echo\Framework\Admin\View\Table;

class Table
{
    public array $columns = [];
    public array $types = [];

    public function __construct(private string $table_name) {}

    public function build(): array
    {
        return [
            "headers" => array_keys($this->columns),
            "data" => $this->getData()
        ];
    }

    public function render(array $data)
    {
        return twig()->render("admin/table.html.twig", $data);
    }

    private function getData()
    {
        return qb()->select(array_values($this->columns))
            ->from($this->table_name)
            ->execute();
    }

}
