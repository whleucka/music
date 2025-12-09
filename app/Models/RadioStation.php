<?php

namespace App\Models;

use Echo\Framework\Database\Model;

class RadioStation extends Model
{
    public function __construct(?string $id = null)
    {
        parent::__construct('radio_stations', $id);
    }

    public function cover(): ?string
    {
        $fi = new FileInfo($this->cover);
        return $fi ? $fi->path : null;
    }

    public function renderCover(int $width, int $height): void
    {
        try {
            // Set headers
            $expires = 60 * 60 * 24 * 30; // about a month
            header("Cache-Control: public, max-age={$expires}");
            header("Expires: " . gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');
            header("Access-Control-Allow-Origin: *");
            header("Content-Type: image/png");

            $fi = new FileInfo($this->cover);

            $file_path = config("paths.uploads") . '/' . $fi->stored_name;

            if (file_exists($file_path)) {
                $imagick = new \imagick($file_path);
                //crop and resize the image
                $imagick->cropThumbnailImage($width, $height);
                //remove the canvas
                $imagick->setImagePage(0, 0, 0, 0);
                $imagick->setImageFormat("png");
                echo $imagick->getImageBlob();
                exit;
            } else {
                // Serve the no album png
                $no_album = config("paths.root") . "/public/images/no-album.png";
                readfile($no_album);
                exit;
            }
        } catch (\Exception $ex) {
            error_log("imagick error: check logs " . $ex->getMessage());
        }
    }

    public function stream(): void
    {
        // Prevent PHP from timing out
        set_time_limit(0);

        // Turn off output buffering
        while (ob_get_level()) ob_end_clean();

        // Send proper headers
        header('Content-Type: audio/mpeg');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');

        // Build ffmpeg command
        $cmd = sprintf(
            'ffmpeg -i %s -f mp3 -c:a libmp3lame -b:a 256k -',
            escapeshellarg($this->src)
        );

        // Open process
        $process = popen($cmd, 'r');

        if (!$process) {
            http_response_code(500);
            echo "Failed to start ffmpeg process.";
            exit;
        }

        // Stream to browser
        while (!feof($process)) {
            $buffer = fread($process, 8192);
            if ($buffer === false) break;

            echo $buffer;
            flush();
        }

        pclose($process);
    }
}
