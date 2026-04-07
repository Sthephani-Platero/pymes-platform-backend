<?php

namespace App\Services;

class TrendIntelligenceService
{
    public function analyze($data)
    {
        $growth = $data['engagement_growth'];
        $engagement = $data['engagement'];
        $impressions = $data['impressions'];
        $comments = $data['comments'];

        return [
            "score" => $this->calculateScore($growth, $engagement),
            "prediction" => $this->predictTrend($growth),
            "recommendations" => $this->generateSmartRecommendations($data),
            "action_plan" => $this->generateWeeklyPlan($data)
        ];
    }

    private function calculateScore($growth, $engagement)
    {
        return round(($growth * 0.6) + ($engagement * 0.4));
    }

    private function predictTrend($growth)
    {
        if ($growth > 30) {
            return "Alta probabilidad de crecimiento en los próximos días 🚀";
        }

        if ($growth < -10) {
            return "Riesgo de caída en los próximos días ⚠";
        }

        return "Comportamiento estable";
    }

    private function generateSmartRecommendations($data)
    {
        $rec = [];

        if ($data['engagement_growth'] > 30) {
            $rec[] = "Aumentar frecuencia de publicación";
            $rec[] = "Invertir en anuncios";
        }

        if ($data['comments'] < 50) {
            $rec[] = "Usar contenido interactivo (preguntas, encuestas)";
        }

        if ($data['impressions'] > 1000 && $data['comments'] < 100) {
            $rec[] = "Optimizar engagement con reels o contenido emocional";
        }

        return $rec;
    }

    private function generateWeeklyPlan($data)
    {
        if ($data['engagement_growth'] > 20) {
            return [
                "Lunes: Publicar contenido educativo",
                "Miércoles: Reel o video corto",
                "Viernes: Promoción o CTA"
            ];
        }

        return [
            "Martes: Post interactivo",
            "Jueves: Historias con encuesta",
            "Sábado: Video corto"
        ];
    }
}