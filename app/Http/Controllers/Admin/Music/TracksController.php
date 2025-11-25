<?php

namespace App\Http\Controllers\Admin\Music;

use Echo\Framework\Http\AdminController;
use Echo\Framework\Routing\Group;

#[Group(path_prefix: "/tracks", name_prefix: "tracks")]
class TracksController extends AdminController
{
    public function __construct()
    {
        $this->has_create = $this->has_edit = $this->has_delete = false;

        $this->table_pk = "tracks.id";

        $this->table_columns = [
            "ID" => "tracks.id",
            "Hash" => "tracks.hash",
            "Artist" => "track_meta.artist",
            "Album" => "track_meta.album",
            "Title" => "track_meta.title",
            "Created" => "tracks.created_at",
        ];

        $this->table_joins = [
            "INNER JOIN track_meta ON track_meta.track_id = tracks.id",
        ];

        $this->search_columns = [
            "Artist",
            "Album",
            "Title",
        ];

        parent::__construct("tracks");
    }
}
