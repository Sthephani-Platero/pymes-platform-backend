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
        // 🔹 Fechas (puedes hacerlas dinámicas después)
        $dateFrom = "2025-01-01T00:00:00Z";
        $dateTo = "2025-02-01T23:59:59Z";

        // 🔹 Obtener marcas
        $brandsResponse = $this->pulsar->getBrands();
        $brands = $brandsResponse['brands']['brands'] ?? [];

        $totalEngagement = 0;
        $totalComments = 0;
        $totalImpressions = 0;
        $validBrands = [];

        // 🔹 Procesar cada marca
        foreach ($brands as $brand) {

            // Filtrar profiles conectados
            $profiles = array_filter($brand['profiles'], function ($p) {
                return $p['plugged'] === true;
            });

            $profileIds = array_column($profiles, 'id');

            

            $totalEngagementBrand = 0;
            $totalCommentsBrand = 0;
            $totalImpressionsBrand = 0;

            foreach ($profileIds as $profileId) {

                try {
                    // 🔹 Engagement
                    $engagementData = $this->pulsar->getEngagements(
                        $brand['id'],
                        [$profileId],
                        $dateFrom,
                        $dateTo,
                        "SUM"
                    );

                    $totalEngagementBrand += $engagementData['engagements'] ?? 0;

                    // 🔹 Comments
                    $commentsData = $this->pulsar->getComments(
                        $brand['id'],
                        [$profileId],
                        $dateFrom,
                        $dateTo
                    );

                    $totalCommentsBrand += $commentsData['comments'] ?? 0;

                    // 🔹 Impressions
                    $impressionsData = $this->pulsar->getImpressions(
                        $brand['id'],
                        [$profileId],
                        $dateFrom,
                        $dateTo
                    );

                    $totalImpressionsBrand += $impressionsData['impressions'] ?? 0;

                } catch (\Exception $e) {
                    continue;
                }
            }

            // 🔹 Acumular totales globales
            $totalEngagement += $totalEngagementBrand;
            $totalComments += $totalCommentsBrand;
            $totalImpressions += $totalImpressionsBrand;

            // 🔹 Guardar data completa
            $validBrands[] = [
                "brand" => $brand['name'],
                "engagement" => $totalEngagementBrand,
                "comments" => $totalCommentsBrand,
                "impressions" => $totalImpressionsBrand,
                "profiles_count" => count($profileIds)
            ];
        }

        // 🔥 ORDENAR por engagement (TOP marcas)
        usort($validBrands, function ($a, $b) {
            return $b['engagement'] <=> $a['engagement'];
        });

        // 🔮 PREDICCIONES
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

        // 🚀 INNOVACIÓN
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