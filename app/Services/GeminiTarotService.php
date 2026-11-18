<?php

namespace App\Services;

use App\Models\TarotCard;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiTarotService
{
    public function generateReading(string $spreadType = 'three_card', string $category = 'General', ?string $question = null): array
    {
        $positionsMap = [
            'single'     => ['Card of the Day'],
            'three_card' => ['Past Influence', 'Present Situation', 'Future Outcome'],
            'love'       => ['Your Energy', 'Partner Energy', 'Relationship Path'],
            'career'     => ['Current Position', 'Key Opportunity / Challenge', 'Career Trajectory'],
        ];

        $positions = $positionsMap[$spreadType] ?? $positionsMap['three_card'];
        $neededCount = count($positions);

        $allCards = TarotCard::all()->all();
        $total = count($allCards);

        if ($total > 0) {
            for ($i = $total - 1; $i > 0; $i--) {
                $j = random_int(0, $i);
                $temp = $allCards[$i];
                $allCards[$i] = $allCards[$j];
                $allCards[$j] = $temp;
            }
        }

        $drawnCards = [];
        $promptLines = [];

        foreach ($positions as $idx => $positionLabel) {
            /** @var TarotCard $cardModel */
            $cardModel = $allCards[$idx] ?? TarotCard::first();
            $isReversed = random_int(1, 100) <= 30;
            $orientation = $isReversed ? 'Reversed' : 'Upright';

            $fixedMeaning = $cardModel->getMeaningFor($isReversed, $category);
            $keywords = $isReversed ? $cardModel->keywords_reversed : $cardModel->keywords_upright;

            $drawnCards[] = [
                'id'             => $cardModel->id,
                'card_number'    => $cardModel->card_number,
                'position'       => $positionLabel,
                'name'           => $cardModel->name,
                'arcana'         => $cardModel->arcana,
                'suit'           => $cardModel->suit,
                'element'        => $cardModel->element,
                'is_reversed'    => $isReversed,
                'orientation'    => $orientation,
                'keywords'       => $keywords,
                'interpretation' => $fixedMeaning,
            ];

            $promptLines[] = "- [{$positionLabel}]: {$cardModel->name} ({$orientation}) | Keywords: {$keywords} | Meaning: {$fixedMeaning}";
        }

        $userQuestion = $question ? trim($question) : "General guidance for {$category}";
        $apiKey = config('services.gemini.key');

        $overallTheme = 'Clarity & Conscious Direction';
        $synthesis = '';
        $actionableAdvice = '';

        if (!empty($apiKey)) {
            $cardsBlock = implode("\n", $promptLines);
            $prompt = "You are a compassionate, insightful Tarot Reader writing a cohesive spread summary for an astrology platform.\n\n"
                . "CONTEXT:\n"
                . "- Focus Category: {$category}\n"
                . "- User Question: \"{$userQuestion}\"\n"
                . "- Drawn Cards:\n{$cardsBlock}\n\n"
                . "INSTRUCTIONS:\n"
                . "- Do NOT rewrite individual card definitions. Instead, synthesize how these drawn cards work together across their positions to answer the user's question.\n"
                . "- Keep the tone warm, grounded, empowering, and clear. Avoid cliché filler words.\n"
                . "- Return ONLY a valid JSON object with these exact keys:\n"
                . "{\n"
                . "  \"overall_theme\": \"A short 4-6 word headline summarizing this spread\",\n"
                . "  \"synthesis\": \"3-4 sentences weaving all drawn cards together into a unified answer for the user\",\n"
                . "  \"actionable_advice\": \"2 practical, uplifting sentences on what step the user should take next\"\n"
                . "}";

            try {
                $response = Http::timeout(25)->post(
                    "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.1-flash-lite:generateContent?key={$apiKey}",
                    [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => $prompt]
                                ]
                            ]
                        ],
                        'generationConfig' => [
                            'responseMimeType' => 'application/json',
                            'temperature'      => 0.7,
                        ],
                    ]
                );
                if ($response->successful()) {
                    $rawText = $response->json('candidates.0.content.parts.0.text', '');
                    $parsed = json_decode(trim($rawText), true);

                    if (\is_array($parsed) && !empty($parsed['synthesis'])) {
                        $overallTheme = $parsed['overall_theme'] ?? $overallTheme;
                        $synthesis = $parsed['synthesis'];
                        $actionableAdvice = $parsed['actionable_advice'] ?? '';
                    }
                }
            } catch (\Exception $e) {
                Log::error('Gemini Tarot Synthesis Failed: ' . $e->getMessage());
            }
        }

        if (empty($synthesis)) {
            if ($neededCount === 1) {
                $c = $drawnCards[0];
                $synthesis = "Today's focus is guided by {$c['name']} ({$c['orientation']}). {$c['interpretation']}";
            } else {
                $synthesis = "Your spread moves from {$drawnCards[0]['name']} ({$drawnCards[0]['position']}) through {$drawnCards[1]['name']} ({$drawnCards[1]['position']}), pointing toward {$drawnCards[2]['name']} ({$drawnCards[2]['position']}). Together, these cards encourage mindful alignment with your {$category} goals.";
            }
            $actionableAdvice = 'Reflect on the core keywords above and take one clear, intentional step forward today.';
        }

        return [
            'spread_type'       => $spreadType,
            'category'          => $category,
            'question'          => $userQuestion,
            'overall_theme'     => $overallTheme,
            'cards'             => $drawnCards,
            'synthesis'         => $synthesis,
            'actionable_advice' => $actionableAdvice,
        ];
    }
}
