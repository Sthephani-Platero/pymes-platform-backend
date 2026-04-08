<?php

namespace App\Http\Controllers;

use App\Services\PredictionService;

class PredictionController extends Controller
{
    protected $pulsar;

    public function __construct(PulsarController $pulsar)
    {
        $this->pulsar = $pulsar;
    }

    public function index(PredictionService $predictionService)
    {
        // 📅 FECHAS (🔥 AQUÍ ESTABA EL ERROR)
        $currentFrom = "2025-01-01T00:00:00Z";
        $currentTo   = "2025-02-01T23:59:59Z";

        $prevFrom = "2024-12-01T00:00:00Z";
        $prevTo   = "2024-12-31T23:59:59Z";

        $brandsResponse = $this->pulsar->getBrands();
        $brands = $brandsResponse['brands']['brands'] ?? [];

        $results = [];

        foreach ($brands as $brand) {

            $profiles = array_filter($brand['profiles'] ?? [], function ($p) {
                return isset($p['plugged']) && $p['plugged'] === true;
            });

            $profileIds = array_column($profiles, 'id');

            if (empty($profileIds)) continue;

            try {
                $current = $this->getMetrics($brand['id'], $profileIds, $currentFrom, $currentTo);

                $previous = $this->getMetrics($brand['id'], $profileIds, $prevFrom, $prevTo);

                $growth = $this->growth($current['engagement'], $previous['engagement']);

                $prediction = $predictionService->predict($current, $growth);

                $results[] = [
                    "brand" => $brand['name'] ?? 'Sin nombre',
                    "current_engagement" => $current['engagement'],
                    "growth" => $growth,
                    "prediction" => $prediction
                ];

            } catch (\Exception $e) {
                return response()->json([
                    "error" => $e->getMessage()
                ], 500);
            }
        }

        return response()->json([
            "predictions" => $results,
            "alerts" => $predictionService->generateAlerts($results)
        ]);
    }

    private function getMetrics($brandId, $profileIds, $from, $to)
    {
        $engagement = $this->pulsar->getEngagements(
            $brandId,
            $profileIds,
            $from,
            $to,
            "SUM"
        );

        return [
            "engagement" => $engagement['engagements'] ?? 0
        ];
    }

    private function growth($current, $previous)
    {
        if ($previous == 0) return $current > 0 ? 100 : 0;
        return (($current - $previous) / $previous) * 100;
    }
}