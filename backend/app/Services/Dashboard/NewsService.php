<?php

namespace App\Services\Dashboard;

use Illuminate\Support\Facades\Http;

class NewsService
{
    public function getNews($keywords)
    {
        if (!env('USE_REAL_APIS')) {
            return $this->mockNews();
        }

        // 🔹 normalizar keywords
        $normalizedKeywords = array_map(fn($k) => strtolower($k), $keywords);

        $query = implode(' OR ', $keywords);

        // ==========================
        // 🇸🇻 1. Local (SV)
        // ==========================
        $response = Http::get('https://newsapi.org/v2/top-headlines', [
            'q' => $query,
            'country' => 'sv',
            'apiKey' => env('NEWS_API_KEY'),
            'pageSize' => 10
        ]);

        $articles = $response->json()['articles'] ?? [];

        // ==========================
        // 🇪🇸 2. Español global
        // ==========================
        if (empty($articles)) {
            $response = Http::get('https://newsapi.org/v2/everything', [
                'q' => $query,
                'language' => 'es',
                'sortBy' => 'relevancy',
                'apiKey' => env('NEWS_API_KEY'),
                'pageSize' => 10
            ]);

            $articles = $response->json()['articles'] ?? [];
        }

        // ==========================
        // 🇺🇸 3. Fallback inglés
        // ==========================
        if (empty($articles)) {
            $response = Http::get('https://newsapi.org/v2/everything', [
                'q' => $query,
                'language' => 'en',
                'sortBy' => 'relevancy',
                'apiKey' => env('NEWS_API_KEY'),
                'pageSize' => 10
            ]);

            $articles = $response->json()['articles'] ?? [];
        }

        // ==========================
        // 🧠 4. FILTRADO DINÁMICO 🔥
        // ==========================
        return collect($articles)
            ->filter(function ($article) use ($normalizedKeywords) {
                $title = strtolower($article['title'] ?? '');
                $description = strtolower($article['description'] ?? '');

                foreach ($normalizedKeywords as $keyword) {
                    if (str_contains($title, $keyword) || str_contains($description, $keyword)) {
                        return true;
                    }
                }

                return false;
            })
            ->take(5)
            ->map(function ($article) {
                return [
                    'title' => $article['title'] ?? null,
                    'source' => $article['source']['name'] ?? null,
                    'url' => $article['url'] ?? null,
                    'image' => $article['urlToImage'] ?? null,
                    'publishedAt' => $article['publishedAt'] ?? null
                ];
            })
            ->values();
    }

    private function mockNews()
    {
        return [
            [
                'title' => 'Tendencias en negocios 2026',
                'source' => 'Mock News',
                'url' => '#',
                'image' => null,
                'publishedAt' => now()
            ]
        ];
    }
}