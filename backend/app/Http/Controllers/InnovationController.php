<?php

namespace App\Http\Controllers;

use App\Services\InnovationService;
use Carbon\Carbon;

class InnovationController extends Controller
{
    protected $pulsar;

    public function __construct(PulsarController $pulsar)
    {
        $this->pulsar = $pulsar;
    }

    public function index(InnovationService $innovation)
    {
        // 📅 Fechas en formato ISO requerido por Pulsar
        $dateTo = Carbon::now()->format('Y-m-d\TH:i:s\Z');
        $dateFrom = Carbon::now()->subDays(7)->format('Y-m-d\TH:i:s\Z');

        $brandsResponse = $this->pulsar->getBrands();
        $brands = $brandsResponse['brands']['brands'] ?? [];

        $results = [];

        foreach ($brands as $brand) {

            if (!isset($brand['profiles'])) continue;

            // 🔹 Filtrar perfiles conectados
            $profiles = array_filter($brand['profiles'], fn($p) => $p['plugged'] ?? false);

            // 🔥 Convertir a INT
            $profileIds = array_map('intval', array_column($profiles, 'id'));

            if (empty($profileIds)) continue;

            try {

                $engagementResponse = $this->pulsar->getEngagements(
                    (int) $brand['id'],
                    $profileIds,
                    $dateFrom,
                    $dateTo,
                    "SUM"
                );

                $engagement = $engagementResponse['engagements'] ?? 0;

                // 🔹 Simulación (puedes mejorar luego)
                $growth = rand(-20, 50);

                $strategy = $innovation->generateStrategy([
                    "engagement" => $engagement,
                    "growth" => $growth
                ]);

                $results[] = [
                    "brand" => $brand['name'] ?? 'Sin nombre',
                    "growth" => $growth,
                    "engagement" => $engagement,
                    "strategy" => $strategy
                ];

            } catch (\Exception $e) {

                \Log::error("Error en brand {$brand['name']}", [
                    "error" => $e->getMessage()
                ]);

                continue;
            }
        }

        return response()->json([
            "innovation" => $results
        ]);
    }
}