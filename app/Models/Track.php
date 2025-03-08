<?php

namespace App\Models;

use Echo\Framework\Database\Model;
use getid3_lib;
use getID3;
use FFMpeg;

class Track extends Model
{
    public function __construct(?string $id = null)
    {
        parent::__construct('tracks', $id);
    }

    public function meta(): ?TrackMeta
    {
        return TrackMeta::where("track_id", $this->id)->get();
    }

    public function size(): int|false
    {
        return filesize($this->pathname);
    }

    public function tags(): array
    {
        $getID3 = new getID3;
        $tags = $getID3->analyze($this->pathname);
        getid3_lib::CopyTagsToComments($tags);
        return $tags;
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

            $cache_directory = "/tmp/";
            // Generate a unique cache filename based on the parameters.
            $dir_name = dirname($this->pathname);
            $cache_filename = md5($dir_name) . '.png';
            $cache_filepath = $cache_directory . $cache_filename;

            // Check if the cached image exists.
            if (file_exists($cache_filepath)) {
                // Serve the cached image.
                readfile($cache_filepath);
                exit;
            }

            $storage_path = config("paths.covers");
            $cover = $this->meta()->cover;
            $filename = basename($cover);
            $image = $storage_path . $filename;

            if (file_exists($image) && $cover !== "/images/no-album.png") {
                $imagick = new \imagick($image);
                //crop and resize the image
                $imagick->cropThumbnailImage($width, $height);
                //remove the canvas
                $imagick->setImagePage(0, 0, 0, 0);
                $imagick->setImageFormat("png");
                // Save the resized image to the cache directory.
                $imagick->writeImage($cache_filepath);
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
        $pathname = $this->meta()->mime_type !== 'audio/mpeg'
            ? $this->transcode()
            : $this->pathname;

        if (!$pathname) exit;

        $filesize = filesize($pathname);
        $start = 0;
        $length = $filesize;
        $end = $filesize - 1;

        // Handle range request
        if (isset($_SERVER['HTTP_RANGE'])) {
            preg_match('/bytes=(\d+)-(\d*)/', $_SERVER['HTTP_RANGE'], $matches);
            $start = isset($matches[1]) ? intval($matches[1]) : 0;
            $end = isset($matches[2]) && $matches[2] !== '' 
                ? intval($matches[2]) 
                : $filesize - 1;
            $length = $end - $start + 1;

            header("HTTP/1.1 206 Partial Content");
            header("Content-Range: bytes $start-$end/$filesize");
        } else {
            header("HTTP/1.1 200 OK");
        }

        header("Content-Type: audio/mpeg");
        header("Content-Length: $length");
        header("Accept-Ranges: bytes");
        header("Content-Transfer-Encoding: binary");

        $fp = fopen($pathname, 'rb');
        fseek($fp, $start);
        echo fread($fp, $length);
        fclose($fp);
        exit;
    }

    public function transcode(): ?string
    {
        $storage_dir = config("paths.transcode");
        if (!file_exists($storage_dir)) {
            throw new \Exception("transcode directory does not exist");
        }
        $md5_file = $storage_dir . md5($this->pathname) . '.mp3';
        if (!file_exists($md5_file)) {
            $ffmpeg = FFMpeg\FFMpeg::create([
                'ffmpeg.binaries' => '/usr/bin/ffmpeg',
                'ffprobe.binaries' => '/usr/bin/ffprobe',
                'timeout' => 60 * 5,
                'ffmpeg.threads' => 12,
            ]);
            $audio_channels = 2;
            $bitrate = 160;
            $audio = $ffmpeg->open($this->pathname);
            $format = new FFMpeg\Format\Audio\Mp3('libmp3lame');
            $format
                ->setAudioChannels($audio_channels)
                ->setAudioKiloBitrate($bitrate);
            try {
                $audio->save($format, $md5_file);
            } catch (\Exception $e) {
                error_log('transcode error: ' . $e->getMessage());
            }
        }
        return $md5_file;
    }

}
