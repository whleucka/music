<?php

namespace App\Console\Commands;

use App\Models\Track;
use App\Models\TrackMeta;

/**
 * Music tracks command
 */
class Tracks extends \ConsoleKit\Command
{
    private array $supported_extensions = [
        "aiff",
        "aif",
        "mp2",
        "mp3",
        "m4a",
        "wav",
        "aac",
        "flac",
        "alac",
        "opus",
        "oga",
        "ogg",
    ];

    /**
     * Synchronize music tracks with database
     */
    public function executeSync(array $args, array $options = []): void
    {
        if (empty($args)) {
            $this->writeerr("You must provide a music directory to sync" . PHP_EOL);
            exit;
        }
        $start = microtime(true);
        $directory = $args[0];
        $files = recursiveFiles($directory);
        $file_count = 0;
        $new_count = 0;
        db()->beginTransaction();
        foreach ($files as $file) {
            if ($file->isFile() && in_array($file->getExtension(), $this->supported_extensions)) {
                $file_count++;
                $hash = md5($file->getPathname());
                $exists = Track::where("hash", $hash)->get();
                if (!$exists) {
                    $id = Track::create([
                        "hash" => $hash,
                        "filename" => $file->getFilename(),
                        "pathname" => $file->getPathname(),
                    ]);
                    if ($id) $new_count++;
                }
            }
        }
        db()->commit();
        $end = microtime(true);
        $time_diff = number_format($end - $start, 2);
        $this->writeln("Successfully synchronized $directory in $time_diff seconds");
        $this->writeln("Total files: " . $file_count);
        $this->writeln("New files: " . $new_count . PHP_EOL);
    }

    /**
     * Update track meta from ID3
     */
    public function executeUpdateMeta(array $args, array $options = []): void
    {
        $start = microtime(true);
        $tracks = Track::where(1, 1)->get();
        if (!$tracks) {
            $this->writeerr("You must first synchronize library." . PHP_EOL);
            exit;
        }
        db()->beginTransaction();
        foreach($tracks as $track) {
            if (!$track->meta()) {
                $tags = $track->tags();
                $comments = $tags["comments_html"] ?? [];
                $genre = $comments["genre"] ?? [];
                TrackMeta::create([
                    "track_id" => $track->id,
                    "cover" => "/images/no-album.png",
                    "artist" => $comments["artist"][0] ?? "(no artist)",
                    "album" => $comments["album"][0] ?? "(no album)",
                    "title" => $comments["title"][0] ?? "(no title)",
                    "genre" => implode(", ", $genre) ?? "?",
                    "year" => $comments["year"][0] ?? "?",
                    "track_number" => $comments["track_number"][0] ?? "?",
                    "playtime_string" => $tags["playtime_string"] ?? "",
                    "bitrate" => $tags["bitrate"] ?? "?",
                    "mime_type" => $tags["mime_type"] ?? "?",
                ]);
            }
        }
        db()->commit();
        $end = microtime(true);
        $time_diff = number_format($end - $start, 2);
        $this->writeln("Successfully updated meta in $time_diff seconds" . PHP_EOL);
    }

    /**
     * Update track covers
     */
    public function executeUpdateCovers(array $args, array $options = []): void
    {
        $start = microtime(true);
        $tracks = Track::where(1, 1)->get();
        if (!$tracks) {
            $this->writeerr("You must first synchronize library." . PHP_EOL);
            exit;
        }
        db()->beginTransaction();
        foreach($tracks as $track) {
            $meta = $track->meta();
            if ($meta) {
                if ($meta->cover === "/images/no-album.png") {
                    $meta->updateCover($track->tags());
                }
            }
        }
        db()->commit();
        $end = microtime(true);
        $time_diff = number_format($end - $start, 2);
        $this->writeln("Successfully updated album covers in $time_diff seconds" . PHP_EOL);
    }

}
