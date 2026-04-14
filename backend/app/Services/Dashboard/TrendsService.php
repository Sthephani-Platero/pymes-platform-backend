<?php

namespace App\Services\Dashboard;

use Illuminate\Support\Facades\Http;

class TrendsService
{
    public function getTrends($category)
    {
        $category = strtolower(trim($category));
        $category = $this->normalizeCategory($category);

        try {

            // 🔥 PRIMER INTENTO
            $queryString = $this->buildQueryByCategory($category);
            $results = $this->fetchTrends($queryString);

            // 🔥 SEGUNDO INTENTO SOLO PARA FITNESS (FIX REAL)
            if (empty($results) && $category === 'fitness') {
                $queryString = 'home workout weight loss routine';
                $results = $this->fetchTrends($queryString);
            }

            // 🚨 SI NO HAY → SEARCH
            if (empty($results)) {
                return $this->searchFallback($queryString);
            }

            // 🧠 FILTRAR
            $filtered = $this->filterByCategory($results, $category);

            // ⚠️ SI FILTRO VACÍO → USAR RAW LIMPIO
            if ($filtered->isEmpty()) {
                return collect($results)
                    ->take(5)
                    ->map(fn ($item) => [
                        'query' => $this->extractText($item),
                        'value' => rand(40, 80)
                    ])
                    ->values();
            }

            return $filtered->take(5)->values();

        } catch (\Exception $e) {
            return $this->fallbackTrends($category);
        }
    }

    // ==========================
    // 🔥 FETCH CENTRALIZADO
    // ==========================
    private function fetchTrends($query)
    {
        $response = Http::timeout(10)->get('https://serpapi.com/search.json', [
            'engine' => 'google_trends',
            'q' => $query,
            'data_type' => 'RELATED_QUERIES',
            'geo' => 'US',
            'hl' => 'es',
            'api_key' => config('services.serpapi.key')
        ]);

        $json = $response->json();

        if ($response->failed() || isset($json['error'])) {
            return [];
        }

        return data_get($json, 'related_queries.rising')
            ?? data_get($json, 'related_queries.top')
            ?? data_get($json, 'related_topics.rising')
            ?? data_get($json, 'related_topics.top')
            ?? [];
    }

    // ==========================
    // 🎯 NORMALIZAR
    // ==========================
    private function normalizeCategory($category)
    {
        if (str_contains($category, 'beauty') || str_contains($category, 'skin') || str_contains($category, 'makeup')) {
            return 'belleza';
        }

        if (str_contains($category, 'fitness') || str_contains($category, 'gym')) {
            return 'fitness';
        }

        if (str_contains($category, 'education') || str_contains($category, 'study')) {
            return 'educacion';
        }

        return $category;
    }

    // ==========================
    // 🔎 QUERY OPTIMIZADA
    // ==========================
    private function buildQueryByCategory($category)
    {
        return match ($category) {
            'belleza' => 'skincare beauty trends',
            'fitness' => 'fitness workout routine gym',
            'educacion' => 'learning study tips',
            default => $category,
        };
    }

    // ==========================
    // 🧠 EXTRAER TEXTO
    // ==========================
    private function extractText($item)
    {
        return $item['query']
            ?? $item['topic_title']
            ?? $item['title']
            ?? 'sin titulo';
    }

    // ==========================
    // 🧠 FILTRO PRO (FIX REAL FITNESS)
    // ==========================
    private function filterByCategory($results, $category)
    {
        $keywords = match ($category) {
            'belleza' => ['skin', 'skincare', 'beauty', 'makeup', 'piel'],
            'fitness' => [
                'fitness', 'gym', 'workout', 'exercise', 'training',
                'cardio', 'weight', 'muscle', 'fat', 'routine',
                'hiit', 'strength', 'home workout', 'lose weight'
            ],
            'educacion' => ['study', 'learning', 'curso', 'educacion', 'aprender'],
            default => [],
        };

        return collect($results)
            ->map(function ($item) use ($keywords) {

                $text = strtolower($this->extractText($item));

                $score = 0;

                foreach ($keywords as $word) {
                    if (str_contains($text, $word)) {
                        $score += 2;
                    }
                }

                return [
                    'query' => $this->extractText($item),
                    'value' => $item['extracted_value']
                        ?? (isset($item['value'])
                            ? (int) filter_var($item['value'], FILTER_SANITIZE_NUMBER_INT)
                            : rand(40, 90)),
                    'score' => $score
                ];
            })
            ->filter(fn ($item) => $item['score'] >= 2) // 🔥 CLAVE
            ->sortByDesc('score')
            ->values();
    }

    // ==========================
    // 🔍 SEARCH FALLBACK
    // ==========================
    private function searchFallback($query)
    {
        $response = Http::timeout(10)->get('https://serpapi.com/search.json', [
            'engine' => 'google',
            'q' => $query,
            'hl' => 'es',
            'geo' => 'US',
            'api_key' => config('services.serpapi.key')
        ]);

        $json = $response->json();
        $organic = data_get($json, 'organic_results', []);

        if (!empty($organic)) {
            return collect($organic)
                ->take(5)
                ->map(fn ($item) => [
                    'query' => $item['title'] ?? 'sin titulo',
                    'value' => rand(50, 100)
                ])
                ->values();
        }

        return $this->dynamicFallback($query);
    }

    // ==========================
    // 🔥 FALLBACK DINÁMICO
    // ==========================
    private function dynamicFallback($query)
    {
        return collect([
            ['query' => $query . ' tips', 'value' => 60],
            ['query' => $query . ' ideas', 'value' => 55],
            ['query' => $query . ' 2026', 'value' => 50],
        ]);
    }

    // ==========================
    // 🔥 FALLBACK BASE
    // ==========================
    private function fallbackTrends($category)
    {
        return match ($category) {
            'belleza' => [
                ['query' => 'rutina skincare', 'value' => 100],
                ['query' => 'maquillaje natural', 'value' => 95],
            ],
            'fitness' => [
                ['query' => 'rutina gym', 'value' => 100],
                ['query' => 'ejercicio en casa', 'value' => 90],
            ],
            'educacion' => [
                ['query' => 'cómo estudiar mejor', 'value' => 100],
                ['query' => 'técnicas de estudio', 'value' => 90],
            ],
            default => [
                ['query' => 'tendencias hoy', 'value' => 80],
            ],
        };
    }
}