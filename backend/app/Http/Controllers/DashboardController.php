<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Dashboard\NewsService;
use App\Services\Dashboard\YoutubeService;
use App\Services\Dashboard\TrendsService;

class DashboardController extends Controller
{
    public function index(
        Request $request, 
        NewsService $newsService, 
        YoutubeService $youtubeService,
        TrendsService $trendsService
    ) {
        $category = $request->get('category', 'fitness');

        $categoryData = config("categories.$category");

        if (!$categoryData) {
            return response()->json([
                'error' => 'Categoría no válida'
            ], 400);
        }

        if (
            !isset($categoryData['news_keywords']) ||
            !isset($categoryData['youtube_keywords']) ||
            !isset($categoryData['trends_keywords'])
        ) {
            return response()->json([
                'error' => 'Configuración incompleta para la categoría'
            ], 500);
        }

        // ==========================
        // 📰 Noticias
        // ==========================
        $news = $newsService->getNews($categoryData['news_keywords']);

        // ==========================
        // 🎥 Videos + engagement
        // ==========================
        $videos = collect(
            $youtubeService->getVideos($categoryData['youtube_keywords'])
        )->map(function ($video) {

            $views = (int) ($video['views'] ?? 0);
            $likes = (int) ($video['likes'] ?? 0);
            $comments = (int) ($video['comments'] ?? 0);

            return [
                ...$video,
                'views' => $views,
                'likes' => $likes,
                'comments' => $comments,
                'engagement' => $likes + $comments
            ];
        })
        ->sortByDesc('engagement')
        ->values();

        // ==========================
        // 📈 Trends (YA INTELIGENTE)
        // ==========================
        $trends = collect(
            $trendsService->getTrends($categoryData['trends_keywords'])
        )->values();

        // ==========================
        // 🧠 MOTOR DE RECOMENDACIONES
        // ==========================
        $topVideo = $videos->first();
        $topTrend = $trends->first();

        $recommendations = [];

        // ==========================
        // 🎓 EDUCACIÓN
        // ==========================
        if ($category === 'educacion' && $topVideo) {
            $title = strtolower($topVideo['title']);

            if (str_contains($title, 'estudiar') || str_contains($title, 'aprend')) {
                $recommendations[] = "Publica tips para mejorar la forma de estudiar";
                $recommendations[] = "Crea un video corto con técnicas de estudio efectivas";
            }

            if (str_contains($title, 'memoriz')) {
                $recommendations[] = "Comparte técnicas de memorización rápida";
            }

            $recommendations[] = "Explica un método de estudio en menos de 1 minuto";
        }

        // ==========================
        // 💪 FITNESS
        // ==========================
        elseif ($category === 'fitness' && $topVideo) {
            $title = strtolower($topVideo['title']);

            if (str_contains($title, 'hiit')) {
                $recommendations[] = "Publica una rutina HIIT de 15-20 minutos en casa";
            }

            if (str_contains($title, 'peso')) {
                $recommendations[] = "Comparte tips para bajar de peso con ejercicio";
            }

            if (str_contains($title, 'casa')) {
                $recommendations[] = "Rutina en casa sin equipo (contenido rápido)";
            }
        }

        // ==========================
        // 📈 Trends (solo si aportan valor)
        // ==========================
        if (
            $topTrend &&
            isset($topTrend['query']) &&
            !str_contains($topTrend['query'], 'tendencias') &&
            !str_contains($topTrend['query'], 'hoy')
        ) {
            $recommendations[] = "Habla sobre: " . $topTrend['query'];
        }

        // ==========================
        // 📰 Noticias (adaptadas)
        // ==========================
        if (!empty($news)) {
            if ($category === 'educacion') {
                $recommendations[] = "Comparte un recurso educativo o curso gratuito";
            } else {
                $recommendations[] = "Comparte una noticia relevante de tu sector";
            }
        }

        // ==========================
        // 🧹 limpiar
        // ==========================
        $recommendations = collect($recommendations)
            ->unique()
            ->take(4)
            ->values();

        // ==========================
        // 🚀 respuesta final
        // ==========================
        return response()->json([
            'category' => $category,

            'news' => $news,
            'videos' => $videos,
            'trends' => $trends,

            'insights' => [
                'top_video' => $topVideo,
                'top_trend' => $topTrend,
                'total_videos' => $videos->count(),
                'total_trends' => $trends->count(),
            ],

            'recommendations' => $recommendations
        ]);
    }
}