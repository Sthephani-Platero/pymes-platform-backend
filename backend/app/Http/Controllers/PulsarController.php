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

    // 🔹 Método base reutilizable
    public function call($query, $variables = [])
{
    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . $this->apiKey,
        'Content-Type' => 'application/json'
    ])->post($this->endpoint, [
        'query' => $query,
        'variables' => $variables
    ]);

    $json = $response->json();

    // 🔥 DEBUG REAL
    if (isset($json['errors'])) {
        dd('GRAPHQL ERROR', $json['errors']);
    }

    return $json['data'] ?? [];
}

    // ==========================
    // 🔹 QUERIES REUTILIZABLES
    // ==========================

    public function getBrands($page = 1, $limit = 10)
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

        return $this->call($query, [
            "page" => $page,
            "limit" => $limit
        ]);
    }

    public function getEngagements($brandId, $profiles, $dateFrom, $dateTo, $metric = "SUM")
    {
        $query = <<<'GRAPHQL'
        query Engagements($filter: Filter!, $metric: ContentMetric) {
            engagements(filter: $filter, metric: $metric)
        }
        GRAPHQL;

        return $this->call($query, [
            "filter" => [
                "dateFrom" => $dateFrom,
                "dateTo" => $dateTo,
                "brandId" => $brandId,
                "profiles" => $profiles
            ],
            "metric" => $metric
        ]);
    }

    public function getComments($brandId, $profiles, $dateFrom, $dateTo)
    {
        $query = <<<'GRAPHQL'
        query comments($filter: Filter!, $metric: ContentMetric!) {
            comments(filter: $filter, metric: $metric)
        }
        GRAPHQL;

        return $this->call($query, [
            "filter" => [
                "dateFrom" => $dateFrom,
                "dateTo" => $dateTo,
                "brandId" => $brandId,
                "profiles" => $profiles
            ],
            "metric" => "SUM"
        ]);
    }

    public function getImpressions($brandId, $profiles, $dateFrom, $dateTo)
    {
        $query = <<<'GRAPHQL'
        query Impressions($filter: Filter!, $metric: ContentMetric) {
            impressions(filter: $filter, metric: $metric)
        }
        GRAPHQL;

        return $this->call($query, [
            "filter" => [
                "dateFrom" => $dateFrom,
                "dateTo" => $dateTo,
                "brandId" => $brandId,
                "profiles" => $profiles
            ],
            "metric" => "SUM"
        ]);
    }
}