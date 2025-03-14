<?php

namespace App\Models;

use Echo\Framework\Database\Model;

class TrackMeta extends Model
{
    public function __construct(?string $id = null)
    {
        parent::__construct('track_meta', $id);
    }

    public function updateCover(array $tags): void
    {
        $cover = "/images/no-album.png";
        if (isset($tags["comments"]["picture"])) {
            $pictures = $tags["comments"]["picture"];
            foreach ($pictures as $picture) {
                if (isset($picture["picturetype"])) {
                    if (preg_match("/cover/i", $picture["picturetype"])) {
                        $cover_art = $this->extract($tags["filenamepath"], $picture);
                        if ($cover_art) {
                            $cover = $cover_art;
                        }
                    }
                }
            }
        }
        $this->cover = $cover;
        $this->save();
    }

    private function extract(string $filepath, array $picture): ?string
    {
        $cover_directory = config("paths.covers");
        if (!file_exists($cover_directory) && !mkdir($cover_directory, 0775, true)) {
            throw new \Exception("Failed to create cover directory: $cover_directory");
        }
        if (!is_writable($cover_directory)) {
            throw new \Exception("cover directory is not writable");
        }
        $ext = match ($picture["image_mime"]) {
            "image/jpeg" => ".jpg",
            "image/png" => ".png",
            default => false
        };
        if (isset($ext)) {
            $encoded = base64_encode($picture["data"]);
            $image_string = str_replace(" ", "+", $encoded);
            $image_data = base64_decode($image_string);
            $filename = md5(dirname($filepath)) . $ext;
            $storage_path = $cover_directory . $filename;
            $public_path = config("paths.public_covers") . $filename;
            if (!file_exists($storage_path)) {
                file_put_contents($storage_path, $image_data);
            }
            return $public_path;
        }
        return null;
    }
}
