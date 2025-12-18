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

    public function getStationFromHash(string $hash)
    {
        return RadioStation::where("hash", $hash)->get();
    }
}
