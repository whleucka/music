<?php

namespace App\Providers\Music;

use App\Models\RadioStation;

class RadioService
{
    public function getRadioStationsFromDB()
    {
        return db()->fetchAll("SELECT radio_stations.*, file_info.stored_name as cover
            FROM radio_stations 
            INNER JOIN file_info WHERE file_info.id = radio_stations.cover
            ORDER BY country,province,city");
    }

    public function getRadioStationIndex()
    {
        return brain()->radio->station_index;
    }

    public function clearRadioStationIndex()
    {
        brain()->radio->station_index = null;
    }

    public function setRadioStationIndex(int $index)
    {
        brain()->radio->station_index = $index;
    }

    public function getStationFromHash(string $hash)
    {
        return RadioStation::where("hash", $hash)->get();
    }

    public function setPlayer(string $id, string $source, string $cover, string $city, string $province, string $country, string $name): void
    {
        $player = [
            "id" => $id,
            "source" => $source,
            "cover" => $cover,
            "artist" => $city . ', ' . $province . ', ' . $country,
            "album" => $name,
            "title" => $name,
            "shuffle" => false,
            "type" => "radio",
        ];
        brain()->player->setState($player);
    }
}
