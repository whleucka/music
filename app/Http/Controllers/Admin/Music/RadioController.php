<?php

namespace App\Http\Controllers\Admin\Music;

use App\Models\RadioStation;
use Echo\Framework\Http\AdminController;
use Echo\Framework\Routing\Group;

#[Group(path_prefix: "/radio", name_prefix: "radio")]
class RadioController extends AdminController
{
    public function __construct()
    {
        $this->table_columns = [
            "ID" => "id",
            "Cover" => "id as cover",
            "Name" => "name",
            "Country" => "country",
            "Province/State" => "province",
            "City" => "city",
            "Created" => "created_at",
        ];

        $this->table_format = [
            "cover" => function ($column, $value) {
                $radio = RadioStation::find($value);
                return "<img loading='lazy' class='thumbnail rounded' src='{$radio->cover()}' alt='icon' />";
            },
        ];

        $this->search_columns = [
            "Name",
        ];

        $this->form_columns = [
            "Cover" => "cover",
            "Name" => "name",
            "Country" => "country",
            "Province/State" => "province",
            "City" => "city",
            "Source" => "src",
        ];

        $this->form_controls = [
            "cover" => "image",
            "name" => "input",
            "country" => "input",
            "province" => "input",
            "city" => "input",
            "src" => "input",

        ];

        $this->validation_rules = [
            "cover" => [],
            "name" => ["required"],
            "country" => [],
            "province" => [],
            "city" => [],
            "src" => ["required"],
        ];


        parent::__construct("radio_stations");
    }
}
