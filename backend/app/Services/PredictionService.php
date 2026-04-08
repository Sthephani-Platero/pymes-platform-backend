<?php

namespace App\Services;

class PredictionService
{
    public function predict($current, $growth)
    {
        $futureEngagement = $this->forecast($current['engagement'], $growth);

        return [
            "next_7_days" => round($futureEngagement * 0.25),
            "next_30_days" => round($futureEngagement),

            // 🧠 inteligencia
            "trend" => $this->trendLabel($growth),
            "risk" => $this->riskLevel($growth),
            "opportunity" => $this->opportunity($growth, $current['engagement']),

            // 🔥 NUEVO
            "score" => $this->score($growth, $current['engagement']),
            "recommendation" => $this->recommendation($growth, $current['engagement']),
            "action_plan" => $this->actionPlan($growth)
        ];
    }

    // 📈 PROYECCIÓN
    private function forecast($value, $growth)
    {
        return $value + ($value * ($growth / 100));
    }

    // 🔮 TENDENCIA
    private function trendLabel($growth)
    {
        if ($growth > 40) return "Explosión de crecimiento 🚀🔥";
        if ($growth > 15) return "Crecimiento constante 📈";
        if ($growth < -20) return "Caída crítica 🚨";
        if ($growth < 0) return "Desaceleración";
        return "Estable";
    }

    // ⚠ RIESGO
    private function riskLevel($growth)
    {
        if ($growth < -30) return "Alto riesgo";
        if ($growth < 0) return "Riesgo medio";
        return "Bajo riesgo";
    }

    // 🚀 OPORTUNIDAD
    private function opportunity($growth, $engagement)
    {
        if ($growth > 30 && $engagement > 1000) {
            return "Escalar campañas publicitarias inmediatamente";
        }

        if ($growth > 20) {
            return "Aprovechar momentum con más contenido";
        }

        if ($growth < 0 && $engagement > 500) {
            return "Optimizar contenido para mejorar interacción";
        }

        if ($engagement < 200) {
            return "Aumentar frecuencia de publicaciones";
        }

        return "Mantener y optimizar estrategia actual";
    }

    // 🧠 SCORE COMPETITIVO (0–100)
    private function score($growth, $engagement)
    {
        $score = 0;

        // crecimiento pesa más
        if ($growth > 30) $score += 40;
        elseif ($growth > 10) $score += 25;
        elseif ($growth > 0) $score += 10;

        // engagement
        if ($engagement > 1000) $score += 40;
        elseif ($engagement > 500) $score += 25;
        elseif ($engagement > 100) $score += 10;

        // penalización
        if ($growth < 0) $score -= 20;

        return max(0, min(100, $score));
    }

    // 💡 RECOMENDACIÓN CLARA
    private function recommendation($growth, $engagement)
    {
        if ($growth > 30) {
            return "Aumenta inversión en ads y duplica contenido esta semana";
        }

        if ($growth > 10) {
            return "Mantén estrategia y prueba nuevos formatos (reels, videos)";
        }

        if ($growth < 0 && $engagement > 500) {
            return "Revisa contenido: tienes alcance pero no engagement";
        }

        if ($growth < 0) {
            return "Haz cambios urgentes en contenido y frecuencia";
        }

        return "Sigue optimizando tu estrategia actual";
    }

    // 🎯 PLAN DE ACCIÓN
    private function actionPlan($growth)
    {
        if ($growth > 30) {
            return "Publicar diariamente + invertir en anuncios + colaborar con influencers";
        }

        if ($growth > 10) {
            return "Publicar 3-4 veces por semana + probar formatos nuevos";
        }

        if ($growth < 0) {
            return "Auditar contenido + analizar competencia + ajustar estrategia";
        }

        return "Mantener constancia y monitorear métricas";
    }

    public function generateAlerts($data)
{
    $alerts = [];

    foreach ($data as $item) {

        $growth = $item['growth'];
        $brand = $item['brand'];

        if ($growth < -30) {
            $alerts[] = [
                "type" => "danger",
                "message" => "⚠ $brand está cayendo fuertemente. Oportunidad de superarlo."
            ];
        }

        if ($growth > 40) {
            $alerts[] = [
                "type" => "success",
                "message" => "🚀 $brand está creciendo rápido. Está dominando el mercado."
            ];
        }

        if ($growth >= -10 && $growth <= 10) {
            $alerts[] = [
                "type" => "info",
                "message" => "😐 $brand está estable. Mercado sin cambios fuertes."
            ];
        }
    }

    return $alerts;
}
}