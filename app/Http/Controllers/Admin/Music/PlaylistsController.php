<?php

namespace App\Http\Controllers\Admin\Music;

use Echo\Framework\Http\AdminController;
use Echo\Framework\Routing\Group;

#[Group(path_prefix: "/playlists", name_prefix: "playlists")]
class PlaylistsController extends AdminController
{
    public function __construct()
    {
        $this->table_pk = "playlists.id";

        $this->table_columns = [
            "ID" => "playlists.id",
            "Name" => "playlists.name",
            "User" => "users.email", 
            "Created" => "playlists.created_at",
        ];

        $this->table_joins = [
            "INNER JOIN users ON users.id = playlists.user_id",
        ];

        $this->search_columns = [
            "Name",
            "User",
        ];

        $this->form_columns = [
            "Name" => "name",
        ];

        $this->form_controls = [
            "name" => "input",
        ];

        parent::__construct("playlists");
    }
}
