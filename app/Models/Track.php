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
