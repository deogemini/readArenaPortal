<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class TranslationController extends Controller
{
    public function show(string $locale): JsonResponse
    {
        abort_unless(in_array($locale, ['en', 'sw'], true), 404);

        $translations = $locale === 'sw'
            ? json_decode(file_get_contents(lang_path('sw.json')), true, 512, JSON_THROW_ON_ERROR)
            : new \stdClass;

        return response()->json([
            'data' => [
                'locale' => $locale,
                'translations' => $translations,
            ],
        ])->header('Cache-Control', 'public, max-age=3600');
    }
}
