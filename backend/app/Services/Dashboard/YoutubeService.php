<?php

namespace App\Services\Dashboard;

use Illuminate\Support\Facades\Http;

class YoutubeService
{
    public function getVideos($query)
    {
        if (!env('USE_REAL_APIS')) {
            return $this->mockVideos();
        }

        try {

            $searchResponse = Http::timeout(10)->get(
                'https://www.googleapis.com/youtube/v3/search',
                [
                    'part' => 'snippet',
                    'q' => $query,
                    'type' => 'video',
                    'maxResults' => 5,
                    'key' => env('YOUTUBE_API_KEY')
                ]
            );

            if ($searchResponse->failed()) {
                return $this->mockVideos();
            }

            $items = $searchResponse->json()['items'] ?? [];

            $videoIds = collect($items)
                ->pluck('id.videoId')
                ->filter()
                ->implode(',');

            if (!$videoIds) {
                return $this->mockVideos();
            }

            $statsResponse = Http::timeout(10)->get(
                'https://www.googleapis.com/youtube/v3/videos',
                [
                    'part' => 'statistics',
                    'id' => $videoIds,
                    'key' => env('YOUTUBE_API_KEY')
                ]
            );

            $stats = $statsResponse->failed()
                ? collect()
                : collect($statsResponse->json()['items'] ?? [])->keyBy('id');

            return collect($items)->map(function ($item) use ($stats) {

                $videoId = $item['id']['videoId'] ?? null;
                $stat = $stats->get($videoId, []);
                $s = $stat['statistics'] ?? [];

                return [
                    'title' => $item['snippet']['title'] ?? '',
                    'channel' => $item['snippet']['channelTitle'] ?? '',
                    'thumbnail' => $item['snippet']['thumbnails']['high']['url'] ?? null,
                    'url' => $videoId ? "https://www.youtube.com/watch?v=$videoId" : '#',
                    'publishedAt' => $item['snippet']['publishedAt'] ?? null,

                    'views' => (int) ($s['viewCount'] ?? 0),
                    'likes' => (int) ($s['likeCount'] ?? 0),
                    'comments' => (int) ($s['commentCount'] ?? 0),
                ];
            })->values();

        } catch (\Throwable $e) {
            return $this->mockVideos();
        }
    }

    private function mockVideos()
    {
        return [
            [
                'title' => 'Rutina HIIT en casa',
                'channel' => 'Fitness Pro',
                'thumbnail' => null,
                'url' => '#',
                'publishedAt' => now(),
                'views' => 12000,
                'likes' => 800,
                'comments' => 120,
            ]
        ];
    }
}