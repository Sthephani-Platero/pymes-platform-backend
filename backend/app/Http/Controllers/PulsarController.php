<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class PulsarController extends Controller
{
    private $endpoint;
    private $apiKey;

    public function __construct()
    {
        $this->endpoint = 'https://data.pulsarplatform.com/graphql/core';
        $this->apiKey = env('PULSAR_API_KEY');
    }

    private function callPulsar($query, $variables = [])
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json'
        ])->post($this->endpoint, [
            'query' => $query,
            'variables' => $variables
        ]);

        return $response->json()['data'] ?? [];
    }

   public function getImpressions()
{
    $query = <<<'GRAPHQL'
    query Impressions($filter: Filter!, $metric: ContentMetric) {
        impressions(filter: $filter, metric: $metric)
    }
    GRAPHQL;

    // Facebook
    $facebook = $this->callPulsar($query, [
        "filter" => [
            "dateFrom" => "2026-01-01T00:00:00Z",
            "dateTo" => "2026-02-01T23:59:59Z",
            "brandId" => 8158,
            "profiles" => [17853]
        ],
        "metric" => "SUM"
    ]);

    // Instagram
    $instagram = $this->callPulsar($query, [
        "filter" => [
            "dateFrom" => "2026-01-01T00:00:00Z",
            "dateTo" => "2026-02-01T23:59:59Z",
            "brandId" => 8158,
            "profiles" => [17854]
        ],
        "metric" => "SUM"
    ]);

    // X (Twitter)
    $x = $this->callPulsar($query, [
        "filter" => [
            "dateFrom" => "2026-01-01T00:00:00Z",
            "dateTo" => "2026-02-01T23:59:59Z",
            "brandId" => 8158,
            "profiles" => [17855]
        ],
        "metric" => "SUM"
    ]);

    return response()->json([
        "facebook" => $facebook["impressions"] ?? 0,
        "instagram" => $instagram["impressions"] ?? 0,
        "x" => $x["impressions"] ?? 0
    ]);
}
    public function getMentionsTrend()
{
    $query = <<<'GRAPHQL'
    query Mentions($filter: Filter!, $metric: ContentMetric!) {
        mentions(filter: $filter, metric: $metric)
    }
    GRAPHQL;

    $variables = [
        'filter' => [
            'dateFrom' => '2025-01-01T00:00:00Z',
            'dateTo' => '2025-01-31T23:59:59Z',
            'brandId' => 8223,
            'profiles' => [18031, 42773, 54568]
        ],
        'metric' => 'SUM'
    ];

    $data = $this->callPulsar($query, $variables);

    dd($data); // 👈 aquí lo pones

    return response()->json($data);
}

    // ==========================
    // Obtener Brands + Profiles
    // ==========================
    public function getBrandsProfiles()
    {
        $query = <<<'GRAPHQL'
        query BrandsPlusProfiles($page: Int, $limit: Int) {
          brands(page: $page, limit: $limit) {
            total
            nextPage
            brands {
              id
              name
              profiles {
                id
                source
                name
                plugged
              }
            }
          }
        }
        GRAPHQL;

        $variables = [
            "page" => 1,
            "limit" => 10
        ];

        return response()->json(
            $this->callPulsar($query, $variables)
        );
    }

    // ==========================
    // Engagements
    // ==========================
    public function getEngagements()
    {
        $query = <<<'GRAPHQL'
        query Engagements($filter: Filter!, $metric: ContentMetric) {
            engagements(filter: $filter, metric: $metric)
        }
        GRAPHQL;

        $variables = [
            'filter' => [
                'dateFrom' => '2025-10-11T00:00:00Z',
                'dateTo' => '2025-11-11T23:59:59Z',
                'brandId' => 8223,
                'profiles' => [18031, 42773, 54568]
            ],
            'metric' => 'SUM'
        ];

        return response()->json(
            $this->callPulsar($query, $variables)
        );
    }

    // ==========================
    // Comments
    // ==========================
    public function getComments()
    {
        $query = <<<'GRAPHQL'
        query comments($filter: Filter!, $metric: ContentMetric!) {
            comments(filter: $filter, metric: $metric)
        }
        GRAPHQL;

        $variables = [
            'filter' => [
                'dateFrom' => '2025-01-01T00:00:00Z',
                'dateTo' => '2025-01-26T23:59:59Z',
                'brandId' => 8223,
                'profiles' => [18031, 42773, 54568]
            ],
            'metric' => 'SUM'
        ];

        return response()->json(
            $this->callPulsar($query, $variables)
        );
    }
}