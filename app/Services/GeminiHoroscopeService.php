<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiHoroscopeService
{
    public function enrichHoroscope(string $sign, string $type, array $transitContext, ?string $targetDateLabel = null): array
    {
        $apiKey = config('services.gemini.key');
        if (empty($apiKey)) {
            return array();
        }

        $prompt = $this->buildHoroscopePrompt($sign, $type, $transitContext, $targetDateLabel);

        try {
            $response = Http::timeout(30)->post(
                "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.1-flash-lite:generateContent?key={$apiKey}",
                array(
                    'contents' => array(
                        array(
                            'parts' => array(
                                array('text' => $prompt)
                            )
                        )
                    ),
                    'generationConfig' => array(
                        'responseMimeType' => 'application/json',
                        'temperature' => 0.7,
                    ),
                )
            );

            if ($response->successful()) {
                $text = $response->json('candidates.0.content.parts.0.text', '');
                $text = trim(preg_replace('/^```json\s*|\s*```$/i', '', trim($text)));
                $data = json_decode($text, true);

                if (is_array($data)) {
                    return $data;
                }
            } else {
                Log::warning('Gemini API Horoscope Non-200 Response: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Gemini API Horoscope Generation Failed: ' . $e->getMessage());
        }

        return array();
    }

    protected function buildHoroscopePrompt(string $sign, string $type, array $transitContext, ?string $targetDateLabel = null): string
    {
        $normalizedType = strtolower(trim($type));
        $dateLabel = $targetDateLabel ?: now('Asia/Kolkata')->format('F j, Y');

        $moonH = $transitContext['moonH'] ?? 1;
        $sunH = $transitContext['sunH'] ?? 1;
        $mercH = $transitContext['mercH'] ?? $sunH;
        $venH = $transitContext['venH'] ?? 1;
        $marsH = $transitContext['marsH'] ?? 1;
        $jupH = $transitContext['jupH'] ?? 1;
        $satH = $transitContext['satH'] ?? 1;

        $timeframeRules = match ($normalizedType) {
            'daily', 'yesterday', 'tomorrow' => array(
                'label' => "Daily ({$dateLabel})",
                'anchor_note' => "Focus primarily on fast-moving Chandra (Moon in house {$moonH}) and Budha (Mercury in house {$mercH}) for immediate day-to-day mood, interactions, and timing.",
                'length_spec' => "Keep every field crisp, practical, and scannable: 'description', 'love', and 'career' must be 2–3 sentences (35–50 words) each. 'tip_for_singles', 'tip_for_couples', 'health', 'emotions', 'travel', and 'cosmic_tip' must be 1–2 concise sentences (20–30 words) each.",
            ),
            'weekly' => array(
                'label' => "Weekly ({$dateLabel})",
                'anchor_note' => "Focus on Budha (Mercury in house {$mercH}), Shukra (Venus in house {$venH}), and Mangal (Mars in house {$marsH}) to map out momentum, mid-week shifts, and weekend dynamics.",
                'length_spec' => "Provide moderate, actionable depth: 'description', 'love', and 'career' must be 3–4 sentences (55–75 words) each, noting early-week vs. weekend shifts. 'tip_for_singles', 'tip_for_couples', 'health', 'emotions', and 'travel' must be 2–3 sentences (35–45 words) each. 'cosmic_tip' must be 2 sentences.",
            ),
            'monthly' => array(
                'label' => "Monthly ({$dateLabel})",
                'anchor_note' => "Focus on Surya (Sun in house {$sunH}), Mangal (Mars in house {$marsH}), Shukra (Venus in house {$venH}), and Budha (Mercury in house {$mercH}) to outline key phases, financial cycles, and relationship milestones across the month.",
                'length_spec' => "Provide rich, structured multi-week guidance: 'description', 'love', and 'career' must be 4–6 sentences (85–115 words) each. 'tip_for_singles', 'tip_for_couples', 'health', and 'emotions' must be 3–4 sentences (50–65 words) each. 'travel' and 'cosmic_tip' must be 2–3 sentences (35–45 words) each.",
            ),
            'yearly' => array(
                'label' => "Yearly ({$dateLabel})",
                'anchor_note' => "Focus heavily on major karmic transits: Brihaspati (Jupiter in house {$jupH}) for expansion/fortune, Shani (Saturn in house {$satH}) for discipline/long-term rewards, and Rahu/Ketu for karmic breakthroughs across quarters of the year.",
                'length_spec' => "Provide a comprehensive, deeply detailed annual forecast: 'description', 'love', and 'career' must be 6–8 sentences (130–170 words) each, breaking down overarching themes, opportunities, and turning points across the year. 'tip_for_singles', 'tip_for_couples', 'health', and 'emotions' must be 4–5 sentences (70–90 words) each. 'travel' and 'cosmic_tip' must be 3–4 sentences (50–65 words) each.",
            ),
            default => array(
                'label' => ucfirst($normalizedType) . " ({$dateLabel})",
                'anchor_note' => "Balance Moon (house {$moonH}), Sun (house {$sunH}), Jupiter (house {$jupH}), and Saturn (house {$satH}).",
                'length_spec' => "Write 3–4 sentences for main sections and 2 sentences for tips.",
            ),
        };

        return <<<PROMPT
You are a senior Vedic Astrologer (Jyotish Acharya) writing personalized, culturally authentic horoscopes for an Indian astrology platform (similar in clarity and warmth to Astrotalk).

TARGET PROFILE:
- Zodiac Sign (Rashi): {$sign}
- Horoscope Timeframe: {$timeframeRules['label']}
- Live Gochar (Transit) Positions from {$sign}:
  * Moon (Chandra): House {$moonH}
  * Sun (Surya): House {$sunH}
  * Mercury (Budha): House {$mercH}
  * Venus (Shukra): House {$venH}
  * Mars (Mangal): House {$marsH}
  * Jupiter (Brihaspati): House {$jupH}
  * Saturn (Shani): House {$satH}

ASTROLOGICAL FOCUS FOR THIS TIMEFRAME:
{$timeframeRules['anchor_note']}

STRICT LENGTH & DEPTH REQUIREMENTS:
{$timeframeRules['length_spec']}

STYLE & QUALITY GUARDRAILS:
- Ground insights naturally in the house placements above (e.g., referencing career visibility for 10th house, gains for 11th, reflection for 12th) without sounding robotic or overly technical.
- Use warm, empowering, practical language. Avoid generic AI clichés (never use words like "tapestry", "symphony", "cosmic dance", "navigate the waters", or "delve").
- Give concrete, real-world advice (e.g., specific communication habits, budgeting tips, rest routines, or simple Vedic remedies like chanting a Beej mantra, offering water to Surya, or lighting a diya).

REQUIRED JSON OUTPUT FORMAT:
Return ONLY a raw, valid JSON object with these exact keys:
{
  "colors": "2 auspicious color names with hex codes based on favorable transits, e.g. 'Emerald Green (#2E8B57), Ivory Cream (#FFFFF0)'",
  "love": "Love and relationship forecast following the length requirement above",
  "tip_for_singles": "Practical dating/self-growth advice for singles following the length requirement above",
  "tip_for_couples": "Actionable harmony advice for committed couples following the length requirement above",
  "career": "Career, business, and financial outlook following the length requirement above",
  "health": "Physical vitality, energy, and wellness guidance following the length requirement above",
  "emotions": "Mental clarity, mood, and emotional balance guidance following the length requirement above",
  "travel": "Short trips, commuting, or long-distance travel outlook following the length requirement above",
  "description": "High-level astrological overview summarizing the core theme of this {$normalizedType} period",
  "cosmic_tip": "One specific, easy-to-follow Vedic remedy, mantra, or mindful habit suited to {$sign}"
}
PROMPT;
    }
}
