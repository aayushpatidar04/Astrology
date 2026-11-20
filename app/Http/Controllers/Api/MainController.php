<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageSent;
use App\Events\TypingEvent;
use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Chat;
use App\Models\Chogdiya;
use App\Models\Horoscope;
use App\Models\Testimonial;
use App\Services\AstroAiService;
use App\Services\FirebaseNotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class MainController extends Controller
{
    protected array $middleware = [
        'auth',
    ];

    private function sendNotification(?string $fcmToken, string $title, string $body, array $data): bool
    {
        if (blank($fcmToken)) {
            return false;
        }

        try {
            app(FirebaseNotificationService::class)->sendNotification($fcmToken, $title, $body, $data);

            return true;
        } catch (Throwable $exception) {
            Log::warning('Firebase notification delivery failed.', [
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    public function testNotification(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string'],
            'body' => ['required', 'string'],
            'data' => ['sometimes', 'array'],
        ]);

        $deviceToken = Auth::user()->device_token;
        if (blank($deviceToken)) {
            return response()->json([
                'message' => 'No device token is registered for this account.',
            ], 422);
        }

        if (! $this->sendNotification(
            $deviceToken,
            $validated['title'],
            $validated['body'],
            $validated['data'] ?? []
        )) {
            return response()->json([
                'message' => 'Firebase notification service is unavailable or misconfigured.',
            ], 503);
        }

        return response()->json([
            'message' => 'sent',
        ]);
    }

    public function blogCategories()
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Categories fetched successfully',
            'categories' => BlogCategory::all(),
        ]);
    }

    public function blogs()
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Blogs fetched successfully!',
            'blogs' => Blog::with('category')->latest()->paginate(10),
        ]);
    }

    public function blogsByCategory($id)
    {
        BlogCategory::findOrFail($id);
        $blogs = Blog::with('category')->where('blog_category_id', $id)->latest()->paginate(10);

        return response()->json([
            'status' => 'success',
            'message' => 'Blogs fetched successfully!',
            'blogs' => $blogs,
        ]);
    }

    public function showBlog($id)
    {
        $blog = Blog::with('category')->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Blog fetched successfully!',
            'blog' => $blog,
        ]);
    }

    public function horoscopesByType($type)
    {
        $signs = ['Aries', 'Taurus', 'Gemini', 'Cancer', 'Leo', 'Virgo', 'Libra', 'Scorpio', 'Sagittarius', 'Capricorn', 'Aquarius', 'Pisces'];
        $now = Carbon::now();
        $t = strtolower($type);
        $dateStr = null;
        $key = null;

        if ($t === 'today') {
            $type = 'daily';
            $dateStr = $now->toDateString();
        } elseif ($t === 'tomorrow') {
            $type = 'daily';
            $dateStr = $now->copy()->addDay()->toDateString();
        } elseif ($t === 'yesterday') {
            $type = 'daily';
            $dateStr = $now->copy()->subDay()->toDateString();
        } elseif ($t === 'weekly') {
            $type = 'weekly';
            $key = $now->format('Y-W');
        } elseif ($t === 'monthly') {
            $type = 'monthly';
            $key = $now->format('Y-m');
        } elseif ($t === 'yearly') {
            $type = 'yearly';
            $key = $now->format('Y');
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid horoscope type provided.',
            ], 422);
        }

        $horoscopes = [];
        $astroService = app(AstroAiService::class);

        foreach ($signs as $sign) {
            $query = Horoscope::where('sign', $sign)->where('type', $type);
            if ($type === 'daily' && $dateStr) {
                $query->whereDate('date', $dateStr);
            } elseif ($type === 'weekly' && $key) {
                $query->where('week_key', $key);
            } elseif ($type === 'monthly' && $key) {
                $query->where('month_key', $key);
            } elseif ($type === 'yearly' && $key) {
                $query->where('year_key', $key);
            }

            $horoscope = $query->first() ?? Horoscope::create($astroService->buildHoroscopePayload($sign, $t === 'yesterday' || $t === 'tomorrow' ? 'daily' : $type, $dateStr));
            $horoscopes[] = $horoscope;
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Horoscopes fetched successfully!',
            'horoscopes' => $horoscopes,
        ]);
    }

    public function horoscopesByTypeAndSign($type, $sign)
    {
        $now = Carbon::now();
        $t = strtolower($type);
        $dateStr = null;
        $key = null;

        if ($t === 'today') {
            $type = 'daily';
            $dateStr = $now->toDateString();
        } elseif ($t === 'tomorrow') {
            $type = 'daily';
            $dateStr = $now->copy()->addDay()->toDateString();
        } elseif ($t === 'yesterday') {
            $type = 'daily';
            $dateStr = $now->copy()->subDay()->toDateString();
        } elseif ($t === 'weekly') {
            $type = 'weekly';
            $key = $now->format('Y-W');
        } elseif ($t === 'monthly') {
            $type = 'monthly';
            $key = $now->format('Y-m');
        } elseif ($t === 'yearly') {
            $type = 'yearly';
            $key = $now->format('Y');
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid horoscope type provided.',
            ], 422);
        }

        $query = Horoscope::where('sign', $sign)->where('type', $type);
        if ($type === 'daily' && $dateStr) {
            $query->whereDate('date', $dateStr);
        } elseif ($type === 'weekly' && $key) {
            $query->where('week_key', $key);
        } elseif ($type === 'monthly' && $key) {
            $query->where('month_key', $key);
        } elseif ($type === 'yearly' && $key) {
            $query->where('year_key', $key);
        }

        $horoscope = $query->first() ?? Horoscope::create(app(AstroAiService::class)->buildHoroscopePayload($sign, $t === 'yesterday' || $t === 'tomorrow' ? 'daily' : $type, $dateStr));

        return response()->json([
            'status' => 'success',
            'message' => 'Horoscope fetched successfully!',
            'horoscope' => $horoscope,
        ]);
    }

    public function sendMessage(Request $request, $chatId)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);
        $userId = Auth::id();

        $chat = Chat::whereHas('participants', fn ($q) => $q->where('user_id', $userId))
            ->findOrFail($chatId);

        $message = $chat->messages()->create([
            'user_id' => $userId,
            'message' => $validated['message'],
        ]);

        // Broadcast event (for real-time updates)
        broadcast(new MessageSent($message))->toOthers();

        $recipient = $chat->participants()
            ->with('user')
            ->where('user_id', '!=', $userId)
            ->first()
            ?->user;

        $this->sendNotification(
            $recipient?->device_token,
            Auth::user()->name,
            $message->message,
            [
                'type' => 'chat_message',
                'chat_id' => $chat->id,
                'sender_id' => $userId,
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Message sent successfully!',
            'data' => $message,
        ], 201);
    }

    public function typing(Request $request, $chatId)
    {
        $validated = $request->validate([
            'typing' => ['required', 'boolean'],
        ]);
        $userId = Auth::id();

        $chat = Chat::whereHas('participants', fn ($q) => $q->where('user_id', $userId))
            ->findOrFail($chatId);
        // Broadcast typing event
        broadcast(new TypingEvent($chatId, $userId, $validated['typing']))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Typing event broadcasted!',
        ]);
    }

    public function chogdiya()
    {
        $year = date('Y');
        $day = date('l');

        $chogdiya = Chogdiya::where('year', $year)->where('day', $day)->first();

        return response()->json([
            'status' => 'success',
            'message' => 'Chogdiya fetched successfully!',
            'chogdiya' => $chogdiya,
        ]);
    }

    public function getBanners()
    {
        $banners = Banner::where('active', 1)->latest()->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Banners fetched successfully!',
            'banners' => $banners,
        ]);
    }

    public function getTestimonials()
    {
        $testimonials = Testimonial::latest()->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Testimonials fetched successfully!',
            'testimonials' => $testimonials,
        ]);
    }
}
