<?php

namespace App\Console\Commands;

use App\Models\Track;

/**
 * Music tracks command
 */
class Tracks extends \ConsoleKit\Command
{
    private array $supported_extensions = [
        "mp3",
        "flac",
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
        $this->writeln("Successfully synchronized $directory");
        $this->writeln("Total files: " . $file_count);
        $this->writeln("New files: " . $new_count . PHP_EOL);
    }
}
