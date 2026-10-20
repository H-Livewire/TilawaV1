<?php

namespace App\Http\Controllers;

use App\Exceptions\QuranApiException;
use App\Services\AyahOfTheDay;
use App\Services\QuranService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AyahOfTheDayController extends Controller
{
    /**
     * JSON for one day's ayah. The client sends its own local calendar date so
     * "today" matches the user's timezone, not the server's.
     */
    public function __invoke(Request $request, AyahOfTheDay $ayahOfTheDay): JsonResponse
    {
        $data = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d', 'after:1999-12-31', 'before:2100-01-01'],
            'lang' => ['nullable', 'string', 'in:'.implode(',', array_keys(QuranService::LANGUAGE_LABELS))],
        ]);

        try {
            $ayah = $ayahOfTheDay->forDate($data['date'] ?? null, $data['lang'] ?? 'en');
        } catch (QuranApiException) {
            return response()->json(['message' => 'The Qur\'an text is temporarily unavailable.'], 503);
        }

        return response()->json($ayah)->header('Cache-Control', 'public, max-age=3600');
    }
}
