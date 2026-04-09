<?php

namespace App\Services\Dashboard;

use Illuminate\Support\Facades\Http;

class TrendsService
{
    public function getTrends($query)
    {
        $queryString = is_array($query)
            ? implode(' ', $query)
            : $query;

        $queryString = trim($queryString);

        // ==========================
        // 🔥 API REQUEST
        // ==========================
        $response = Http::timeout(10)->get('https://serpapi.com/search.json', [
            'engine' => 'google_trends',
            'q' => $queryString,
            'data_type' => 'RELATED_QUERIES',
            'geo' => 'SV',
            'hl' => 'es',
            'api_key' => env('SERPAPI_KEY')
        ]);

        dd(env('SERPAPI_KEY'));

        // ==========================
        // ❌ FALLBACK SI FALLA API
        // ==========================
        if ($response->failed()) {
            return $this->fallbackTrends($queryString);
        }

        $json = $response->json();

        // ==========================
        // 🔥 EXTRAER CORRECTAMENTE
        // ==========================
        $related = $json['related_queries'] ?? [];

        $results = [];

        // PRIORIDAD: rising
        if (!empty($related['rising'])) {
            $results = $related['rising'];
        }

        // fallback: top
        if (empty($results) && !empty($related['top'])) {
            $results = $related['top'];
        }

        // fallback final
        if (empty($results)) {
            return $this->fallbackTrends($queryString);
        }

        // ==========================
        // 🔥 NORMALIZACIÓN SEGURA
        // ==========================
        return collect($results)
            ->filter(fn ($item) => !empty($item['query']))
            ->take(5)
            ->map(function ($item) {

                return [
                    'query' => $item['query'],

                    // 🔥 CLAVE: SIEMPRE extracted_value primero
                    'value' => $item['extracted_value']
                        ?? (is_numeric($item['value'] ?? null)
                            ? (int) $item['value']
                            : 0)
                ];
            })
            ->values();
    }

    // ==========================
    // 🔥 FALLBACK INTELIGENTE
    // ==========================
    private function fallbackTrends($query)
    {
        $query = strtolower($query);

        if (str_contains($query, 'fitness')) {
            return [
                ['query' => 'rutina en casa', 'value' => 100],
                ['query' => 'hiit en casa', 'value' => 95],
                ['query' => 'ejercicios para bajar de peso', 'value' => 90],
            ];
        }

        if (str_contains($query, 'educacion')) {
            return [
                ['query' => 'técnicas de estudio', 'value' => 100],
                ['query' => 'cómo aprender rápido', 'value' => 95],
                ['query' => 'memorización efectiva', 'value' => 90],
            ];
        }

        if (str_contains($query, 'belleza')) {
            return [
                ['query' => 'skincare rutina', 'value' => 100],
                ['query' => 'maquillaje natural', 'value' => 95],
                ['query' => 'cuidado de la piel', 'value' => 90],
            ];
        }

        return [
            ['query' => 'tendencias hoy', 'value' => 80],
            ['query' => 'lo más buscado', 'value' => 70]
        ];
    }
}