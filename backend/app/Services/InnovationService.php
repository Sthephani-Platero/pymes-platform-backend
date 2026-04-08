<?php

namespace App\Services;


class InnovationService
{
    public function generateStrategy($data)
    {
        $growth = $data['growth'];
        $engagement = $data['engagement'];

        $strategy = [];

        // 📅 CUÁNDO PUBLICAR (más específico)
        if ($growth > 40) {
            $strategy["posting_time"] = "Publica mañana a las 7PM (pico de audiencia)";
        } elseif ($growth > 10) {
            $strategy["posting_time"] = "Publica hoy entre 6PM - 9PM";
        } else {
            $strategy["posting_time"] = "Prueba horarios 12PM - 3PM para optimizar";
        }

        // 🎯 CONTENIDO (más estratégico)
        if ($growth < -10) {
            $strategy["content"] = "Contenido emocional + preguntas + storytelling";
        } elseif ($growth < 10) {
            $strategy["content"] = "Contenido educativo + carruseles";
        } else {
            $strategy["content"] = "Reels virales + contenido que ya funciona";
        }

        // 📈 FRECUENCIA
        if ($engagement > 20000) {
            $strategy["frequency"] = "2 veces al día";
        } elseif ($engagement > 10000) {
            $strategy["frequency"] = "1 vez al día";
        } else {
            $strategy["frequency"] = "3-4 veces por semana";
        }

        // 💰 ADS
        if ($growth > 40) {
            $strategy["ads"] = "Invierte $50-$100 en ads ahora (alto retorno)";
        } elseif ($growth > 10) {
            $strategy["ads"] = "Testea con $10-$20 en ads";
        } else {
            $strategy["ads"] = "No invertir aún, optimizar contenido orgánico";
        }

        // 🧠 NUEVO: PRIORIDAD
        if ($growth < -20) {
            $strategy["priority"] = "Alta";
        } elseif ($growth < 10) {
            $strategy["priority"] = "Media";
        } else {
            $strategy["priority"] = "Baja";
        }

        // 🎯 NUEVO: ACCIÓN DIRECTA (esto es lo más pro)
        if ($growth > 40) {
            $strategy["action"] = "Escala ya: publica reels hoy + activa ads";
        } elseif ($growth < -10) {
            $strategy["action"] = "Corrige urgente: cambia tipo de contenido";
        } else {
            $strategy["action"] = "Optimiza y prueba nuevas ideas";
        }

        return $strategy;
    }
}