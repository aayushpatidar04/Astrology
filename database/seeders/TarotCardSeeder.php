<?php

namespace Database\Seeders;

use App\Models\TarotCard;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TarotCardSeeder extends Seeder
{
    public function run(): void
    {
        $majorArcana = [
            [0,  'The Fool',          'Spirit', 'New beginnings, faith, spontaneity', 'Recklessness, hesitation, naivety',
                'A bold new chapter begins. Step forward with curiosity, trust your instincts, and embrace fresh possibilities without fear.',
                'Avoid rushing blindly into decisions. Pause to evaluate risks and check whether hesitation is holding you back.',
                'Exciting romantic beginnings or a spontaneous spark enters your life. Keep an open heart without overthinking the outcome.',
                'Fear of commitment or impulsive choices may create mixed signals in love. Ground yourself before making promises.',
                'A fresh career opportunity, startup idea, or new role invites you to take a calculated leap of faith.',
                'Scattered focus or unpreparedness at work could cause avoidable setbacks. Organize your plan before acting.',
                'New financial ventures look promising if approached thoughtfully rather than impulsively.',
                'Guard against careless spending or speculative risks without proper research.'
            ],
            [1,  'The Magician',      'Air', 'Manifestation, willpower, skill', 'Manipulation, untapped talent, confusion',
                'You have all the tools, clarity, and resourcefulness needed to turn your intentions into tangible reality right now.',
                'Your energy may be scattered or blocked by self-doubt. Realign your focus and watch out for misleading appearances.',
                'Charisma and clear communication help you attract or deepen a meaningful romantic bond.',
                'Mixed messages or lack of transparency may cloud a relationship. Honest dialogue restores trust.',
                'Strong leadership, sharp communication, and creative problem-solving put you in command of your professional goals.',
                'Underutilized skills or poor planning may delay progress. Focus on one priority at a time.',
                'Smart initiatives and resourceful negotiation open new avenues for income growth.',
                'Avoid get-rich-quick pitches or unclear financial agreements.'
            ],
            [2,  'The High Priestess','Water', 'Intuition, inner wisdom, stillness', 'Ignored intuition, secrets, disconnection',
                'Look beneath the surface and trust your inner voice. Quiet observation reveals answers that logic alone cannot see.',
                'You may be ignoring gut feelings or feeling disconnected from your inner compass. Slow down and listen within.',
                'A deep, unspoken emotional connection is forming. Let intimacy unfold naturally without forcing answers.',
                'Unspoken feelings or hidden doubts need gentle, honest acknowledgment between partners.',
                'Trust your instincts when reading workplace dynamics; wait until all facts emerge before committing.',
                'Information may be incomplete right now—avoid making major career moves until clarity arrives.',
                'Keep financial plans private and rely on careful research rather than outside hype.',
                'Watch for hidden terms or overlooked details in contracts and expenses.'
            ],
            [3,  'The Empress',       'Earth', 'Abundance, nurturing, creativity', 'Creative block, dependence, burnout',
                'A season of warmth, growth, and abundance surrounds you. Nurture your ideas, relationships, and personal well-being.',
                'You may be giving too much energy to others while neglecting your own rest and creative renewal.',
                'Deep affection, harmony, and emotional warmth flourish in your romantic life.',
                'Over-dependence or emotional smothering can create tension; restore healthy personal space.',
                'Creative projects, collaborative ventures, and supportive leadership thrive under patient care.',
                'Creative fatigue or stalled momentum signals a need to step back and recharge.',
                'Steady material comfort and fruitful rewards grow from consistent, patient effort.',
                'Rebalance emotional spending and prioritize practical self-care.'
            ],
            [4,  'The Emperor',       'Fire', 'Authority, structure, stability', 'Rigidity, excessive control, stubbornness',
                'Discipline, clear boundaries, and strategic structure bring lasting stability and command over your path.',
                'Excessive control or stubbornness may create friction. Flexibility will serve you better than force.',
                'Loyalty, protection, and dependable commitment anchor your relationship.',
                'Power struggles or emotional rigidity can block warmth; practice patience and compromise.',
                'Strong leadership, clear rules, and disciplined execution earn respect and authority at work.',
                'Micromanagement or resistance to new methods may slow down team progress.',
                'Structured budgeting, long-term planning, and disciplined saving build solid financial security.',
                'Avoid overly rigid financial choices or power clashes over shared money.'
            ],
            [5,  'The Hierophant',    'Earth', 'Tradition, mentorship, shared values', 'Challenging norms, personal truth, nonconformity',
                'Wisdom comes through proven principles, trusted mentors, and staying aligned with your core values.',
                'Questioning outdated rules allows you to define your own authentic path and personal beliefs.',
                'Shared values, family harmony, and traditional commitment strengthen your bond.',
                'Breaking free from external expectations helps you build a relationship on your own terms.',
                'Guidance from an experienced mentor, structured training, or established institutions supports growth.',
                'Unconventional approaches or independent projects may suit you better than rigid corporate paths.',
                'Conservative, time-tested financial strategies offer steady reliability.',
                'Rethink traditional money habits that no longer fit your current goals.'
            ],
            [6,  'The Lovers',        'Air', 'Harmony, alignment, heartfelt choice', 'Disharmony, indecision, misalignment',
                'Meaningful connection and alignment of values guide an important choice. Choose what resonates with your true self.',
                'Internal conflict or misaligned priorities require honest reflection before committing.',
                'Deep mutual attraction, emotional harmony, and heartfelt partnership take center stage.',
                'Communication gaps or differing expectations call for open-hearted alignment.',
                'A productive business partnership or values-aligned career decision brings clarity.',
                'Avoid mixing personal bias with professional choices; weigh both sides objectively.',
                'Financial decisions made with shared transparency and balance lead to stability.',
                'Resolve disagreements over shared budgets or competing priorities.'
            ],
            [7,  'The Chariot',       'Water', 'Willpower, victory, forward drive', 'Lack of direction, scattered energy, delays',
                'Focused determination and self-discipline carry you past obstacles toward clear victory.',
                'Pulling in too many directions at once stalls momentum. Regain control of your focus.',
                'Taking confident initiative moves a relationship forward past hesitation.',
                'Rushing romantic outcomes or letting ego steer creates unnecessary friction.',
                'High ambition, decisive action, and perseverance drive major professional breakthroughs.',
                'Pause to recalibrate your strategy if workplace obstacles feel overwhelming.',
                'Disciplined focus helps you conquer debt or hit an important savings milestone.',
                'Hold off on impulsive big-ticket purchases until your direction is clear.'
            ],
            [8,  'Strength',          'Fire', 'Inner courage, patience, compassion', 'Self-doubt, impatience, raw emotion',
                'True power lies in calm resilience, patience, and gentle self-mastery rather than force.',
                'Self-doubt or emotional reactivity may test your confidence. Breathe and reconnect with your inner calm.',
                'Patience, empathy, and quiet devotion strengthen trust and emotional intimacy.',
                'Insecurity or impatience can trigger misunderstandings; lead with kindness.',
                'Grace under pressure and steady confidence help you navigate complex workplace challenges.',
                'Don’t let imposter syndrome hold you back from stepping into a bigger role.',
                'Patient, steady financial discipline outperforms reactive decisions.',
                'Avoid stress-driven spending; trust your long-term resilience.'
            ],
            [9,  'The Hermit',        'Earth', 'Introspection, wisdom, solitude', 'Isolation, avoidance, loneliness',
                'Stepping back for quiet reflection brings deep clarity and authentic self-understanding.',
                'Withdrawal can become isolating if prolonged; reach out to trusted people when ready.',
                'Taking time to understand your own emotional needs creates healthier future intimacy.',
                'Emotional distance or shutting down communication may leave your partner feeling excluded.',
                'Deep research, solo focus, and thoughtful planning yield superior work results.',
                'Avoid working in complete isolation—collaborate where feedback is needed.',
                'Mindful minimalism and careful financial review reveal where to simplify.',
                'Don’t ignore practical financial matters out of avoidance.'
            ],
            [10, 'Wheel of Fortune',  'Fire', 'Turning point, destiny, good cycles', 'Temporary delays, resistance to change',
                'A positive turning point is underway. Stay adaptable as favorable shifts open unexpected doors.',
                'Temporary delays are simply preparing better timing; avoid resisting natural transitions.',
                'Serendipity and fresh momentum bring exciting evolution in your love life.',
                'Repeating old relationship patterns can be broken through conscious awareness.',
                'New opportunities, lucky timing, or role transitions elevate your career trajectory.',
                'Short-term workplace delays require patience while cycles realign.',
                'Favorable financial shifts reward preparedness and smart timing.',
                'Maintain an emergency buffer to ride out temporary cash-flow fluctuations.'
            ],
            [11, 'Justice',           'Air', 'Fairness, truth, accountability', 'Imbalance, unfairness, avoidance',
                'Clarity, honesty, and balanced cause-and-effect prevail. Fair outcomes follow principled actions.',
                'Examine any blind spots or imbalances honestly so you can restore fairness.',
                'Mutual respect, honesty, and balanced give-and-take sustain healthy love.',
                'Unresolved resentments or unequal effort need candid, fair conversation.',
                'Contracts, evaluations, and ethical decisions work in your favor when backed by integrity.',
                'Double-check agreements and ensure your contributions are properly documented.',
                'Balanced accounts, legal clarity, and fair settlements bring peace of mind.',
                'Review budgets carefully to correct hidden leaks or unfair obligations.'
            ],
            [12, 'The Hanged Man',    'Water', 'Pause, surrender, new perspective', 'Stalling, resistance, needless sacrifice',
                'A deliberate pause allows you to see your situation from an enlightening new angle.',
                'Indecision or clinging to control prolongs stagnation; let go of what cannot be forced.',
                'Patience and viewing things through your partner’s eyes dissolve tension.',
                'Waiting endlessly without clarity drains energy; decide what truly serves your heart.',
                'Stepping back from a stalled project reveals a smarter, unconventional solution.',
                'Avoid over-sacrificing your time without clear recognition or boundaries.',
                'Hold steady on major investments while you gather fresh perspective.',
                'Release sunk-cost thinking on expenses that no longer bring value.'
            ],
            [13, 'Death',             'Water', 'Transformation, closure, rebirth', 'Resistance to change, clinging to past',
                'One chapter naturally closes so a healthier, more authentic phase can begin.',
                'Holding onto outdated habits or situations delays the renewal waiting for you.',
                'Deep transformation clears old emotional baggage and invites genuine renewal.',
                'Fear of letting go may keep you tied to past patterns; embrace honest closure.',
                'Career reinvention, role transitions, or ending an unfulfilling project clears space for growth.',
                'Resisting necessary workplace changes creates unnecessary stress.',
                'Restructuring financial habits and clearing old debts sets up a fresh start.',
                'Let go of outdated financial attachments and adapt to current realities.'
            ],
            [14, 'Temperance',        'Fire', 'Balance, moderation, harmony', 'Imbalance, excess, impatience',
                'Patience, moderation, and harmonious blending of priorities bring steady peace and progress.',
                'Overdoing things or rushing outcomes disrupts equilibrium; slow down and recalibrate.',
                'Calm understanding, mutual patience, and emotional harmony deepen your connection.',
                'Extreme reactions or mismatched pacing call for gentle middle ground.',
                'Steady collaboration, balanced workload, and diplomatic communication yield strong results.',
                'Avoid burnout by pacing your schedule and harmonizing competing demands.',
                'Balanced saving and mindful spending keep your finances steady and stress-free.',
                'Curb impulsive excess and restore equilibrium to your budget.'
            ],
            [15, 'The Devil',         'Earth', 'Attachment, temptation, limiting habits', 'Breaking free, liberation, self-awareness',
                'Examine any unhealthy attachments, fears, or habits holding you back—you hold the key to your own freedom.',
                'A powerful breakthrough allows you to release limiting patterns and reclaim your personal power.',
                'Intense attraction is present, though mindfulness is needed to avoid possessiveness or codependency.',
                'Releasing toxic dynamics or emotional insecurities restores healthy self-worth in love.',
                'Watch out for overwork, office politics, or feeling trapped in a role purely out of fear.',
                'Stepping away from a draining work environment opens healthier career options.',
                'Guard against impulsive materialism, debt traps, or short-sighted financial temptations.',
                'Clearing debt and breaking unhealthy spending habits brings financial liberation.'
            ],
            [16, 'The Tower',         'Fire', 'Sudden breakthrough, truth, awakening', 'Averting upheaval, resisting truth',
                'A sudden realization clears away fragile foundations so you can rebuild on lasting truth.',
                'Accepting necessary change willingly softens disruption and speeds up recovery.',
                'Honest revelations clear illusions and reveal what is genuinely solid in love.',
                'Holding onto unstable dynamics only prolongs tension; welcome candid clarity.',
                'Unexpected organizational shifts open space for a more authentic professional path.',
                'Adapt quickly to changing plans rather than clinging to rigid expectations.',
                'Unexpected expenses highlight the value of strong financial reserves.',
                'Review risk exposure and strengthen your safety net proactively.'
            ],
            [17, 'The Star',          'Air', 'Hope, renewal, inspiration, peace', 'Discouragement, fatigue, self-doubt',
                'Renewed hope, clarity, and gentle inspiration illuminate your path forward.',
                'Temporary fatigue may dim optimism, yet quiet self-care quickly restores your spark.',
                'Healing energy, openness, and genuine optimism bless your romantic life.',
                'Rebuilding self-love first allows you to welcome healthy affection with ease.',
                'Creative inspiration, long-term vision, and authentic recognition align in your career.',
                'Reconnect with your deeper purpose if daily tasks feel uninspiring.',
                'Steady optimism and clear long-term goals guide positive financial recovery.',
                'Stay patient with gradual progress; avoid discouragement over short-term dips.'
            ],
            [18, 'The Moon',          'Water', 'Intuition, subconscious, mystery', 'Clarity emerging, releasing fear',
                'Not everything is visible yet; navigate uncertainty with intuition rather than anxiety.',
                'Confusion begins to lift as hidden truths come to light and fears subside.',
                'Deep emotional sensitivity calls for reassurance rather than jumping to conclusions.',
                'Honest clarity dissolves misunderstandings and restores emotional trust.',
                'Read between the lines in workplace situations and wait for full clarity before acting.',
                'Ambiguous projects become clearer, allowing confident next steps.',
                'Avoid unclear investments or financial decisions made under emotional stress.',
                'Hidden financial details come into focus, helping you make sound choices.'
            ],
            [19, 'The Sun',           'Fire', 'Joy, vitality, clarity, success', 'Temporary cloudiness, delayed joy',
                'Warmth, clarity, confidence, and well-earned success brighten every area of your life.',
                'Success is still on its way—keep your optimism alive through minor delays.',
                'Joyful connection, openness, and shared happiness illuminate your relationship.',
                'Taking time to appreciate small moments brings warmth back into focus.',
                'High visibility, recognition, leadership, and vibrant momentum propel your career.',
                'Keep confidence grounded and avoid overextending your schedule.',
                'Prosperity, clear opportunities, and rewarding outcomes support financial growth.',
                'Maintain realistic expectations while steady gains materialize.'
            ],
            [20, 'Judgement',         'Fire', 'Awakening, renewal, higher calling', 'Self-doubt, hesitation, ignoring lessons',
                'A moment of clarity and self-evaluation invites you to step boldly into your next chapter.',
                'Release harsh self-criticism and trust the lessons you have already earned.',
                'Honest reflection brings renewal, forgiveness, and deeper understanding in love.',
                'Letting go of past regrets frees you to embrace present affection.',
                'A meaningful career decision or recognition aligns your work with your true strengths.',
                'Don’t hesitate when an opportunity calls you to step up.',
                'Thoughtful evaluation of past financial choices guides smarter future planning.',
                'Learn from past money lessons without dwelling on regret.'
            ],
            [21, 'The World',         'Earth', 'Completion, fulfillment, achievement', 'Near completion, seeking closure',
                'A major cycle reaches successful fulfillment. Celebrate your progress before beginning the next journey.',
                'You are close to the finish line—tie up remaining loose ends for full closure.',
                'Wholeness, mutual fulfillment, and milestone achievements enrich your partnership.',
                'Completing unfinished emotional conversations brings lasting harmony.',
                'Successful project completion, recognition, and global or expanded opportunities arrive.',
                'Finish current commitments thoroughly before taking on brand-new obligations.',
                'Financial goals reach satisfying fruition through sustained discipline.',
                'Complete remaining paperwork or payments to close out a financial cycle cleanly.'
            ],
        ];

        foreach ($majorArcana as $m) {
            $slug = Str::slug($m[1]);
            TarotCard::updateOrCreate(
                ['card_number' => $m[0]],
                [
                    'name'              => $m[1],
                    'slug'              => $slug,
                    'arcana'            => 'Major',
                    'suit'              => 'Trump',
                    'element'           => $m[2],
                    'image_url'         => '/images/tarot/' . $slug . '.jpg',
                    'keywords_upright'  => $m[3],
                    'keywords_reversed' => $m[4],
                    'upright_general'   => $m[5],
                    'reversed_general'  => $m[6],
                    'upright_love'      => $m[7],
                    'reversed_love'     => $m[8],
                    'upright_career'    => $m[9],
                    'reversed_career'   => $m[10],
                    'upright_finance'   => $m[11],
                    'reversed_finance'  => $m[12],
                ]
            );
        }

        // Minor Arcana (56 Cards: 4 Suits x 14 Ranks)
        $suits = [
            'Wands'     => ['element' => 'Fire',  'domain' => 'passion, creativity, and bold action'],
            'Cups'      => ['element' => 'Water', 'domain' => 'emotions, intuition, and heartfelt bonds'],
            'Swords'    => ['element' => 'Air',   'domain' => 'clarity, truth, and decisive communication'],
            'Pentacles' => ['element' => 'Earth', 'domain' => 'work, stability, and material growth'],
        ];

        $ranks = [
            'Ace'    => ['ku' => 'New spark, inspiration, potential', 'kr' => 'Delayed start, blocked energy', 'u' => 'A fresh wave of opportunity energizes your', 'r' => 'A temporary pause invites you to refine your'],
            'Two'    => ['ku' => 'Planning, partnership, choice', 'kr' => 'Indecision, imbalance', 'u' => 'Thoughtful balance and partnership strengthen your', 'r' => 'Weighing options carefully resolves hesitation in your'],
            'Three'  => ['ku' => 'Expansion, teamwork, progress', 'kr' => 'Delays, misalignment', 'u' => 'Collaborative momentum and early results expand your', 'r' => 'Clearer coordination helps overcome minor friction in your'],
            'Four'   => ['ku' => 'Stability, foundation, celebration', 'kr' => 'Restlessness, stagnation', 'u' => 'Solid foundations and grounded harmony support your', 'r' => 'Refreshing stale routines restores vitality to your'],
            'Five'   => ['ku' => 'Challenge, growth, resilience', 'kr' => 'Resolution, recovery', 'u' => 'Constructive challenges sharpen resilience in your', 'r' => 'Tension eases as practical solutions restore calm to your'],
            'Six'    => ['ku' => 'Harmony, recognition, generosity', 'kr' => 'Self-doubt, delayed praise', 'u' => 'Mutual support and well-earned progress uplift your', 'r' => 'Focus on intrinsic validation rather than outside approval in your'],
            'Seven'  => ['ku' => 'Perseverance, strategy, courage', 'kr' => 'Overwhelm, second-guessing', 'u' => 'Steady perseverance and clear boundaries protect your', 'r' => 'Simplifying priorities reduces overwhelm in your'],
            'Eight'  => ['ku' => 'Swift movement, mastery, focus', 'kr' => 'Frustration, feeling stuck', 'u' => 'Focused dedication and swift momentum accelerate your', 'r' => 'Stepping back from repetitive routines refreshes your'],
            'Nine'   => ['ku' => 'Resilience, wisdom, near completion', 'kr' => 'Fatigue, guardedness', 'u' => 'Inner strength and hard-won experience fortify your', 'r' => 'Rest and healthy boundaries replenish energy in your'],
            'Ten'    => ['ku' => 'Fulfillment, culmination, legacy', 'kr' => 'Heavy load, delegating', 'u' => 'A rewarding milestone brings lasting fulfillment to your', 'r' => 'Releasing unnecessary burdens lightens the load on your'],
            'Page'   => ['ku' => 'Curiosity, messages, fresh learning', 'kr' => 'Scattered focus, hesitation', 'u' => 'New insights and curious exploration open doors in your', 'r' => 'Grounding scattered ideas brings focus to your'],
            'Knight' => ['ku' => 'Action, pursuit, determination', 'kr' => 'Impulsiveness, stalled plans', 'u' => 'Dedicated pursuit and active energy drive forward your', 'r' => 'Pacing impulsive moves ensures steady progress in your'],
            'Queen'  => ['ku' => 'Mastery, warmth, intuition', 'kr' => 'Self-neglect, sensitivity', 'u' => 'Confident mastery and balanced wisdom nurture your', 'r' => 'Prioritizing self-care restores equilibrium to your'],
            'King'   => ['ku' => 'Leadership, authority, maturity', 'kr' => 'Rigidity, over-control', 'u' => 'Mature leadership and strategic clarity guide your', 'r' => 'Leading with flexibility rather than strict control strengthens your'],
        ];

        $cardNumber = 22;
        foreach ($suits as $suitName => $suitData) {
            foreach ($ranks as $rankName => $rData) {
                $name = "{$rankName} of {$suitName}";
                $slug = Str::slug($name);
                TarotCard::updateOrCreate(
                    ['card_number' => $cardNumber++],
                    [
                        'name'              => $name,
                        'slug'              => $slug,
                        'arcana'            => 'Minor',
                        'suit'              => $suitName,
                        'element'           => $suitData['element'],
                        'image_url'         => '/images/tarot/' . $slug . '.jpg',
                        'keywords_upright'  => $rData['ku'],
                        'keywords_reversed' => $rData['kr'],
                        'upright_general'   => "{$rData['u']} general path. Stay open and proactive.",
                        'reversed_general'  => "{$rData['r']} general path. Mindful pacing brings clarity.",
                        'upright_love'      => "{$rData['u']} romantic life, bringing sincerity and emotional clarity.",
                        'reversed_love'     => "{$rData['r']} relationship dynamics; gentle honesty restores warmth.",
                        'upright_career'    => "{$rData['u']} professional path, supporting steady achievement and teamwork.",
                        'reversed_career'   => "{$rData['r']} career plans; organize priorities before taking the next step.",
                        'upright_finance'   => "{$rData['u']} financial outlook, rewarding practical and disciplined decisions.",
                        'reversed_finance'  => "{$rData['r']} money management; review budgets and avoid rushed spending.",
                    ]
                );
            }
        }
    }
}
