<?php

namespace App\Http\Controllers\Music;

use App\Providers\Music\TrackLikeService;
use App\Providers\Music\TrackService;
use Echo\Framework\Http\Controller;
use Echo\Framework\Routing\Route\Get;

class TrackLikesController extends Controller
{
    public function __construct(
        private TrackLikeService $track_like_provider,
        private TrackService $track_provider,
    ) {}

    // Like component
    #[Get("/like/{hash}", "track-likes.index", ["auth"])]
    public function index(string $hash): ?string
    {
        $track = $this->track_provider->getTrackFromHash($hash);
        if ($track) {
            $liked = $this->track_like_provider->getUserTrackLikeFromDB($this->user->id, $track->id);
            return $this->render("track-likes/index.html.twig", [
                "hash" => $track->hash,
                "liked" => $liked,
            ]);
        }
        return null;
    }

    // Toggle like
    #[Get("/like/toggle/{hash}", "track-likes.toggle", ["auth"])]
    public function toggle(string $hash): ?string
    {
        $track = $this->track_provider->getTrackFromHash($hash);
        if ($track) {
            $liked = $this->track_like_provider->getUserTrackLikeFromDB($this->user->id, $track->id);
            if ($liked) {
                $this->track_like_provider->unlikeTrack($this->user->id, $track->id);
            } else {
                $this->track_like_provider->likeTrack($this->user->id, $track->id);
            }
        }
        return $this->index($hash);
    }
}
