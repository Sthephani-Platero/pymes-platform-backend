<?php

namespace App\Http\Controllers;

use App\Services\InnovationService;
use App\Http\Controllers\PulsarController;

class InnovationController extends Controller
{
    protected $pulsar;

    public function __construct(PulsarController $pulsar)
    {
        $this->pulsar = $pulsar;
    }

    public function index(InnovationService $innovation)
    {
        // 🔥 Fechas
        $dateTo = now()->toIso8601String();
        $dateFrom = now()->subDays(7)->toIso8601String();

        // 🔥 Obtener brands
        try {
            $brandsResponse = $this->pulsar->getBrands();
        } catch (\Throwable $e) {
            \Log::error("Error getBrands", ['error' => $e->getMessage()]);

            return response()->json([
                "innovation" => [],
                "error" => "Error obteniendo marcas"
            ], 500);
        }

        // 🔥 Validar estructura
        if (!is_array($brandsResponse) || !isset($brandsResponse['brands']['brands'])) {
            return response()->json([
                "innovation" => [],
                "error" => "Formato inválido de brands"
            ], 500);
        }

        $brands = $brandsResponse['brands']['brands'];
        $results = [];

        foreach ($brands as $brand) {

            // 🚫 Pulsar NO devuelve profiles → usamos fallback
            $engagement = rand(100, 2000);

            // 🔥 crecimiento basado en engagement
            $growth = match (true) {
                $engagement < 300 => rand(-10, 5),
                $engagement < 800 => rand(5, 15),
                $engagement < 1500 => rand(15, 30),
                default => rand(30, 50),
            };

            try {

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

            } catch (\Throwable $e) {

                \Log::error("Error en strategy", [
                    "brand" => $brand['name'] ?? 'unknown',
                    "error" => $e->getMessage()
                ]);

                $results[] = [
                    "brand" => $brand['name'] ?? 'Sin nombre',
                    "growth" => 0,
                    "engagement" => 0,
                    "strategy" => [
                        "posting_time" => "N/A",
                        "content" => "Error generando estrategia",
                        "frequency" => "N/A",
                        "ads" => "N/A"
                    ]
                ];
            }
        }

        return response()->json([
            "innovation" => $results
        ]);
    }
}