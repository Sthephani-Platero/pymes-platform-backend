<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MarketController extends Controller
{
    protected $pulsar;

    public function __construct(PulsarController $pulsar)
    {
        $this->pulsar = $pulsar;
    }

    public function index()
    {
        $dateFrom = "2025-01-01T00:00:00Z";
        $dateTo = "2025-02-01T23:59:59Z";

        $brandsResponse = $this->pulsar->getBrands();
        $brands = $brandsResponse['brands']['brands'] ?? [];

        $totalEngagement = 0;
        $totalComments = 0;
        $totalImpressions = 0;
        $validBrands = [];

        foreach ($brands as $brand) {

            // 🔹 Filtrar profiles conectados
            $profiles = array_filter($brand['profiles'], fn($p) => $p['plugged'] === true);
            $profileIds = array_column($profiles, 'id');

            
            if (empty($profileIds)) {
                $validBrands[] = [
                    "brand" => $brand['name'],
                    "engagement" => 0,
                    "comments" => 0,
                    "impressions" => 0,
                    "profiles_count" => 0
                ];
                continue;
            }

            try {
                
                $engagementData = $this->pulsar->getEngagements(
                    $brand['id'],
                    $profileIds,
                    $dateFrom,
                    $dateTo,
                    "SUM"
                );

                $commentsData = $this->pulsar->getComments(
                    $brand['id'],
                    $profileIds,
                    $dateFrom,
                    $dateTo
                );

                $impressionsData = $this->pulsar->getImpressions(
                    $brand['id'],
                    $profileIds,
                    $dateFrom,
                    $dateTo
                );

                $totalEngagementBrand = $engagementData['engagements'] ?? 0;
                $totalCommentsBrand = $commentsData['comments'] ?? 0;
                $totalImpressionsBrand = $impressionsData['impressions'] ?? 0;

            } catch (\Exception $e) {
                continue;
            }

            // 🔹 Totales globales
            $totalEngagement += $totalEngagementBrand;
            $totalComments += $totalCommentsBrand;
            $totalImpressions += $totalImpressionsBrand;

            $validBrands[] = [
                "brand" => $brand['name'],
                "engagement" => $totalEngagementBrand,
                "comments" => $totalCommentsBrand,
                "impressions" => $totalImpressionsBrand,
                "profiles_count" => count($profileIds)
            ];
        }

        // 🔥 Ordenar por engagement
        usort($validBrands, fn($a, $b) => $b['engagement'] <=> $a['engagement']);

        // 🔮 Predicciones
        $predictions = array_map(function ($brand) {

            if ($brand['profiles_count'] == 0) {
                $status = "Sin presencia digital";
            } elseif ($brand['engagement'] == 0) {
                $status = "Bajo rendimiento";
            } elseif ($brand['engagement'] < 1000) {
                $status = "Potencial de crecimiento";
            } else {
                $status = "Alto rendimiento";
            }

            return [
                "brand" => $brand['brand'],
                "status" => $status
            ];
        }, $validBrands);

        // 🚀 Innovación
        $innovation = array_map(function ($brand) {

            if ($brand['profiles_count'] == 0) {
                $action = "Crear presencia en redes sociales";
            } elseif ($brand['engagement'] == 0) {
                $action = "Mejorar estrategia de contenido";
            } elseif ($brand['engagement'] < 1000) {
                $action = "Invertir en campañas digitales";
            } else {
                $action = "Escalar con analítica avanzada";
            }

            return [
                "brand" => $brand['brand'],
                "recommendation" => $action
            ];
        }, $validBrands);

        return response()->json([
            "metrics" => [
                "total_brands" => count($brands),
                "total_engagement" => $totalEngagement,
                "total_comments" => $totalComments,
                "total_impressions" => round($totalImpressions, 2)
            ],
            "trends" => $validBrands,
            "predictions" => $predictions,
            "innovation" => $innovation
        ]);
    }
}