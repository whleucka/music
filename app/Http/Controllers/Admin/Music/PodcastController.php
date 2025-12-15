<?php

namespace App\Http\Controllers\Admin\Music;

use Echo\Framework\Http\AdminController;
use Echo\Framework\Routing\Group;

#[Group(path_prefix: "/podcasts", name_prefix: "podcasts")]
class PodcastController extends AdminController
{
    public function __construct()
    {
        $this->table_columns = [
            "ID" => "podcasts.id",
            "User" => "users.email", 
            "Thumbnail" => "podcasts.thumbnail",
            "Title" => "podcasts.title",
            "Publisher" => "podcasts.publisher",
            "Created" => "podcasts.created_at",
        ];

        $this->table_joins = [
            "INNER JOIN users ON users.id = podcasts.user_id",
        ];

        $this->search_columns = [
            "Title",
            "Publisher",
            "User",
        ];

        parent::__construct("podcasts");
    }
}
