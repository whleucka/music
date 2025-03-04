<?php

namespace App\Providers\Music;

class TracksService
{
    public function getSearchTerm()
    {
        return session()->get("tracks_search");
    }

    public function setSearchTerm(string $term)
    {
        session()->set("tracks_search", $term);
    }

    public function clearSearchTerm()
    {
        session()->delete("tracks_search");
    }
}

