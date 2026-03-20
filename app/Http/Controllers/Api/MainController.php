<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageSent;
use App\Events\TypingEvent;
use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Chogdiya;
use App\Models\Horoscope;
use App\Models\Testimonial;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class MainController extends Controller
{
    protected array $middleware = [
        'auth',
    ];

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
        $category = BlogCategory::find($id);
        if (!$category) {
            return response()->json([
                'status' => 'error',
                'error' => 'Category not found!',
                'message' => 'Category not found!',
            ]);
        }
        $blogs = Blog::with('category')->where('blog_category_id', $id)->latest()->paginate(10);
        return response()->json([
            'status' => 'success',
            'message' => 'Blogs fetched successfully!',
            'blogs' => $blogs,
        ]);
    }

    public function showBlog($id)
    {
        $blog = Blog::with('category')->find($id);
        if (!$blog) {
            return response()->json([
                'status' => 'error',
                'error' => 'Blog not found!',
                'message' => 'Blog not found!',
            ]);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Blog fetched successfully!',
            'blog' => $blog,
        ]);
    }

    public function horoscopesByType($type)
    {
        $query = Horoscope::query();
        $now   = Carbon::now();

        switch (strtolower($type)) {
            case 'today':
                $query->where('type', 'daily')
                    ->whereDate('date', $now->toDateString());
                break;

            case 'tomorrow':
                $query->where('type', 'daily')
                    ->whereDate('date', $now->copy()->addDay()->toDateString());
                break;

            case 'yesterday':
                $query->where('type', 'daily')
                    ->whereDate('date', $now->copy()->subDay()->toDateString());
                break;

            case 'weekly':
                $weekKey = $now->format('Y-W');
                $query->where('type', 'weekly')
                    ->where('week_key', $weekKey);
                break;

            case 'monthly':
                $monthKey = $now->format('Y-m'); // e.g. 2026-03
                $query->where('type', 'monthly')
                    ->where('month_key', $monthKey);
                break;

            case 'yearly':
                $yearKey = $now->format('Y'); // e.g. 2026
                $query->where('type', 'yearly')
                    ->where('year_key', $yearKey);
                break;

            default:
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid horoscope type provided.',
                    'error' => 'Invalid horoscope type provided.',
                ], 200);
        }

        $horoscopes = $query->latest()->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Horoscopes fetched successfully!',
            'horoscopes' => $horoscopes,
        ]);
    }

    public function horoscopesByTypeAndSign($type, $sign)
    {
        $query = Horoscope::query();
        $now   = Carbon::now();

        switch (strtolower($type)) {
            case 'today':
                $query->where('type', 'daily')->where('sign', $sign)
                    ->whereDate('date', $now->toDateString());
                break;

            case 'tomorrow':
                $query->where('type', 'daily')->where('sign', $sign)
                    ->whereDate('date', $now->copy()->addDay()->toDateString());
                break;

            case 'yesterday':
                $query->where('type', 'daily')->where('sign', $sign)
                    ->whereDate('date', $now->copy()->subDay()->toDateString());
                break;

            case 'weekly':
                $weekKey = $now->format('Y-W');
                $query->where('type', 'weekly')->where('sign', $sign)
                    ->where('week_key', $weekKey);
                break;

            case 'monthly':
                $monthKey = $now->format('Y-m'); // e.g. 2026-03
                $query->where('type', 'monthly')->where('sign', $sign)
                    ->where('month_key', $monthKey);
                break;

            case 'yearly':
                $yearKey = $now->format('Y'); // e.g. 2026
                $query->where('type', 'yearly')->where('sign', $sign)
                    ->where('year_key', $yearKey);
                break;

            default:
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid horoscope type provided.',
                    'error' => 'Invalid horoscope type provided.',
                ], 200);
        }

        $horoscopes = $query->latest()->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Horoscopes fetched successfully!',
            'horoscopes' => $horoscopes,
        ]);
    }

    public function sendMessage(Request $request, $chatId)
    {
        $userId = Auth::id();

        $chat = Chat::whereHas('participants', fn($q) => $q->where('user_id', $userId))
            ->find($chatId);

        if (!$chat) {
            return response()->json([
                'status' => 'error',
                'message' => 'Chat not found!',
                'error' => 'Chat not found!',
            ]);
        }

        $message = $chat->messages()->create([
            'user_id' => $userId,
            'message' => $request->input('message'),
        ]);

        // Broadcast event (for real-time updates)
        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Message sent successfully!',
            'data' => $message
        ]);
    }

    public function typing(Request $request, $chatId)
    {
        try{
            $validated = $request->validate([
                'typing' => 'required|boolean',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
                'message' => 'Validation failed',
            ], 200);
        }
        $userId = Auth::id();

        $chat = Chat::whereHas('participants', fn($q) => $q->where('user_id', $userId))
            ->find($chatId);

        if (!$chat) {
            return response()->json([
                'status' => 'error',
                'message' => 'Chat not found!',
                'error' => 'Chat not found!',
            ]);
        }
        // Broadcast typing event
        broadcast(new TypingEvent($chatId, $userId, $request->typing))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Typing event broadcasted!'
        ]);
    }

    public function chogdiya(){
        $year = date('Y');
        $day = date('l');

        $chogdiya = Chogdiya::where('year', $year)->where('day', $day)->first();
        return response()->json([
            'status' => 'success',
            'message' => 'Chogdiya fetched successfully!',
            'chogdiya' => $chogdiya,
        ]);
    }

    public function getBanners(){
        $banners = Banner::where('active', 1)->latest()->get();
        return response()->json([
            'status' => 'success',
            'message' => 'Banners fetched successfully!',
            'banners' => $banners
        ]);
    }

    public function getTestimonials(){
        $testimonials = Testimonial::latest()->get();
        return response()->json([
            'status' => 'success',
            'message' => 'Testimonials fetched successfully!',
            'testimonials' => $testimonials
        ]);
    }
}
