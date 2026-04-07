<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TrendIntelligenceService;

class TrendsController extends Controller
{
    protected $pulsar;

    public function __construct(PulsarController $pulsar)
    {
        $this->pulsar = $pulsar;
    }

    public function index(TrendIntelligenceService $ai)
    {
        // 📅 Períodos
        $currentFrom = "2025-01-01T00:00:00Z";
        $currentTo   = "2025-02-01T23:59:59Z";

        $prevFrom = "2024-12-01T00:00:00Z";
        $prevTo   = "2024-12-31T23:59:59Z";

        $brandsResponse = $this->pulsar->getBrands();
        $brands = $brandsResponse['brands']['brands'] ?? [];

        $results = [];

        foreach ($brands as $brand) {

            $profiles = array_filter($brand['profiles'], fn($p) => $p['plugged'] === true);
            $profileIds = array_column($profiles, 'id');

            if (empty($profileIds)) continue;

            try {
                // 📊 DATA
                $current = $this->getMetrics($brand['id'], $profileIds, $currentFrom, $currentTo);
                $previous = $this->getMetrics($brand['id'], $profileIds, $prevFrom, $prevTo);

                // 📈 CRECIMIENTO
                $engGrowth = $this->growth($current['engagement'], $previous['engagement']);
                $comGrowth = $this->growth($current['comments'], $previous['comments']);

                // 🧠 IA ANALYSIS
                $analysis = $ai->analyze([
                    "engagement" => $current['engagement'],
                    "comments" => $current['comments'],
                    "impressions" => $current['impressions'],
                    "engagement_growth" => $engGrowth
                ]);

                $results[] = [
                    "brand" => $brand['name'],

                    // 📊 MÉTRICAS
                    "engagement" => $current['engagement'],
                    "comments" => $current['comments'],
                    "impressions" => $current['impressions'],

                    // 📈 CRECIMIENTO
                    "engagement_growth" => round($engGrowth, 2),
                    "comments_growth" => round($comGrowth, 2),

                    // 🧠 IA
                    "score" => $analysis['score'],
                    "prediction" => $analysis['prediction'],
                    "recommendations" => $analysis['recommendations'],
                    "action_plan" => $analysis['action_plan'],

                    // ⚠️ ALERTAS
                    "alert" => $this->generateAlert($engGrowth),
                ];

            } catch (\Exception $e) {
                continue;
            }
        }

        // 🏆 RANKING
        $topGrowth = collect($results)->sortByDesc('engagement_growth')->take(5)->values();
        $topFall   = collect($results)->sortBy('engagement_growth')->take(5)->values();

        return response()->json([
            "trends" => $results,
            "ranking" => [
                "top_growth" => $topGrowth,
                "top_fall" => $topFall
            ]
        ]);
    }

    // 📊 MÉTRICAS
    private function getMetrics($brandId, $profileIds, $from, $to)
    {
        $engagement = $this->pulsar->getEngagements($brandId, $profileIds, $from, $to, "SUM");
        $comments   = $this->pulsar->getComments($brandId, $profileIds, $from, $to);
        $impressions= $this->pulsar->getImpressions($brandId, $profileIds, $from, $to);

        return [
            "engagement" => $engagement['engagements'] ?? 0,
            "comments" => $comments['comments'] ?? 0,
            "impressions" => $impressions['impressions'] ?? 0
        ];
    }

    // 📈 CRECIMIENTO
    private function growth($current, $previous)
    {
        if ($previous == 0) return $current > 0 ? 100 : 0;
        return (($current - $previous) / $previous) * 100;
    }

    // 🚨 ALERTAS
    private function generateAlert($growth)
    {
        if ($growth < -30) return "Caída fuerte de engagement";
        if ($growth > 50) return "Crecimiento acelerado";
        return null;
    }
}