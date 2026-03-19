<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\PulsarController;
use App\Models\Brand;
use App\Models\Profile;

class SyncMarketData extends Command
{
    protected $signature = 'market:sync';
    protected $description = 'Sincroniza brands y profiles desde Pulsar';

    public function handle()
{
    $pulsar = app(\App\Http\Controllers\PulsarController::class);

    $page = 1;

    do {
        $response = $pulsar->getBrands($page);

        $brands = $response['brands']['brands'] ?? [];
        $nextPage = $response['brands']['nextPage'] ?? null;

        foreach ($brands as $brandData) {

            // 🔹 Guardar brand
            $brand = \App\Models\Brand::updateOrCreate(
                ['pulsar_id' => $brandData['id']],
                ['name' => $brandData['name']]
            );

            // 🔹 Guardar profiles
            foreach ($brandData['profiles'] as $profileData) {
                
                \App\Models\Profile::updateOrCreate(
                    ['pulsar_id' => $profileData['id']],
                    [
                        'brand_id' => $brand->id,
                        'name' => $profileData['name'],
                        'source' => $profileData['source'],
                        'plugged' => $profileData['plugged'],
                    ]
                );
            }
        }

        $this->info("Página {$page} guardada ✅");

        $page = $nextPage;

    } while ($page !== null);

    $this->info('🔥 TODAS las marcas guardadas correctamente');
}
}