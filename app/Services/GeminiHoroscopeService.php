<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiHoroscopeService
{
    public function enrichHoroscope(string $sign, string $type, array $transitContext): array
    {
        $apiKey = config('services.gemini.key');
        if (empty($apiKey)) {
            return [];
        }
        $prompt = "You are an expert Vedic astrologer (Jyotish) like those on Astrotalk. "
            . "Generate a highly detailed, professional, and spiritually insightful {$type} horoscope for the Zodiac Sign {$sign}. "
            . "Transit Context: Moon is in house {$transitContext['moonH']}, Sun in {$transitContext['sunH']}, Venus in {$transitContext['venH']}, Mars in {$transitContext['marsH']}, Jupiter in {$transitContext['jupH']}, Saturn in {$transitContext['satH']}. "
            . "Return a valid JSON object with the following keys: "
            . "\"colors\" (color name with hex code, e.g. 'Coral Red (#FF6F59), Saffron (#FF9933)'), "
            . "\"love\" (detailed love & relationship reading, 3-4 sentences), "
            . "\"tip_for_singles\" (advice for singles, 2-3 sentences), "
            . "\"tip_for_couples\" (advice for couples, 2-3 sentences), "
            . "\"career\" (career, finance & professional growth reading, 3-4 sentences), "
            . "\"health\" (health & physical vitality advice, 2-3 sentences), "
            . "\"emotions\" (mindset & emotional wellbeing, 2-3 sentences), "
            . "\"travel\" (travel & short trips outlook, 2 sentences), "
            . "\"description\" (overall astrological overview for the cycle, 3-4 sentences), "
            . "\"cosmic_tip\" (actionable cosmic remedy or mantra, 2 sentences). "
            . "Ensure the tone is warm, encouraging, authoritative, and deeply Vedic. Return ONLY valid JSON with no extra text or markdown wrapping.";

        try {
            $response = Http::post("https://generativelanguage.googleapis.com/v1beta/models/gemini-3.1-flash-lite:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ]
            ]);
            if ($response->successful()) {
                $text = $response->json('candidates.0.content.parts.0.text', '');
                $text = trim(preg_replace('/^```json\s*|\s*```$/i', '', trim($text)));
                $data = json_decode($text, true);
                if (\is_array($data)) {
                    return $data;
                }
            }
        } catch (\Exception $e) {
            Log::error('Gemini API Horoscope Generation Failed: ' . $e->getMessage());
        }

        return [];
    }
}
