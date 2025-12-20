<?php

namespace App\Providers\Music;

use App\Models\Podcast;
use ListenNotes\PodcastApi\Client;


class PodcastService
{
    private $client;

    public function __construct()
    {
        $key = config("podcast.api_key");
        if (!$key) return;
        $this->client = new Client($key);
    }

    public function search(string $query): mixed
    {
        $res = $this->client->search(['q' => $query]);
        return $res ? json_decode($res) : null;
    }

    public function getPodcast(string $hash)
    {
        $res = $this->client->fetchPodcastById(['id' => $hash]);
        return $res ? json_decode($res) : null;
    }

    public function getEpisodeDetails(string $hash)
    {
        $res = $this->client->fetchEpisodeById(['id' => $hash]);
        return $res ? json_decode($res) : null;
    }

    public function getBestPodcasts()
    {
        $res = $this->client->fetchBestPodcasts();
        return $res ? json_decode($res) : null;
    }

    public function getPodcastRecommendations(string $hash)
    {
        $res = $this->client->fetchRecommendationsForPodcast(['id' => $hash]);
        return $res ? json_decode($res) : null;
    }

    public function getEpisodeRecommendations(string $hash)
    {
        $res = $this->client->fetchRecommendationsForEpisode(['id' => $hash]);
        return $res ? json_decode($res) : null;
    }
}
