<?php

namespace App\Http\Controllers;

use App\Models\Astrologer;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Horoscope;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MainController extends Controller
{
    public function index()
    {
        return Inertia::render('HomePage/Index', [
            'user' => Auth::user()?->load('wallet'),
            'blogs' => Blog::latest()->take(3)->get(),
            'astrologers' => Astrologer::with('user')->where('online', 1)->latest()->take(9)->get()
        ]);
    }

    public function about()
    {
        return Inertia::render('AboutUs/Index', [
            'user' => Auth::user()?->load('wallet'),
        ]);
    }

    public function services()
    {
        return Inertia::render('Services/Index', [
            'user' => Auth::user()?->load('wallet'),
        ]);
    }

    public function blog()
    {
        return Inertia::render('Blog/Index', [
            'user' => Auth::user()?->load('wallet'),
            'blogs' => Blog::latest()->paginate(20),
            'categories' => BlogCategory::all()
        ]);
    }

    public function categoryBlogs($slug)
    {
        $category = BlogCategory::where('slug', $slug)->first();

        if ($category) {
            return Inertia::render('Blog/Index', [
                'user' => Auth::user()?->load('wallet'),
                'blogs' => $category->blogs()
                    ->latest()
                    ->paginate(20),
                'categories' => BlogCategory::all()
            ]);
        } else {
            return redirect()->back()->with('error', 'No such category exist');
        }
    }

    public function blogDetails($slug)
    {
        $blog = Blog::where('slug', $slug)->first();

        return Inertia::render('Blog/View', [
            'user' => Auth::user()?->load('wallet'),
            'blog' => $blog,
            'categories' => BlogCategory::all()
        ]);
    }

    public function horoscopeType($type)
    {
        return Inertia::render('Horoscope/Index', [
            'type' => ucfirst($type),
            'user' => Auth::user()?->load('wallet')
        ]);
    }

    public function horoscope(Request $request, $type, $sign)
    {

        $today = Carbon::today();
        $horoscope = null;
        $formattedDate = null;

        $user = Auth::user();
        $key = $request->input('key');

        if ($user->hasRole('Admin') && $key) {
            // Admin can fetch specific key
            if ($type === 'daily' || $type === 'yesterday' || $type === 'tomorrow') {
                $date = Carbon::parse($key);
                $horoscope = Horoscope::where('type', 'daily')
                    ->where('sign', $sign)
                    ->whereDate('date', $key)
                    ->firstOrFail();
    
                $formattedDate = $date->format('d M, Y');
            } elseif ($type === 'weekly') {
                $horoscope = Horoscope::where('type', 'weekly')
                    ->where('sign', $sign)
                    ->where('week_key', $key)
                    ->firstOrFail();
            } elseif ($type === 'monthly') {
                $horoscope = Horoscope::where('type', 'monthly')
                    ->where('sign', $sign)
                    ->where('month_key', $key)
                    ->firstOrFail();
            } elseif ($type === 'yearly') {
                $horoscope = Horoscope::where('type', 'yearly')
                    ->where('sign', $sign)
                    ->where('year_key', $key)
                    ->firstOrFail();
            }
        } else {
            if ($type === 'daily') {
                $horoscope = Horoscope::where('type', 'daily')
                    ->where('sign', $sign)
                    ->whereDate('date', $today)
                    ->firstOrFail();
    
                $formattedDate = $today->format('d M, Y');
            } elseif ($type === 'yesterday') {
                $yesterday = Carbon::yesterday();
                $horoscope = Horoscope::where('type', 'daily')
                    ->where('sign', $sign)
                    ->whereDate('date', $yesterday)
                    ->firstOrFail();
    
                $formattedDate = $yesterday->format('d M, Y');
            } elseif ($type === 'tomorrow') {
                $tomorrow = Carbon::tomorrow();
                $horoscope = Horoscope::where('type', 'daily')
                    ->where('sign', $sign)
                    ->whereDate('date', $tomorrow)
                    ->firstOrFail();
    
                $formattedDate = $tomorrow->format('d M, Y');
            } elseif ($type === 'weekly') {
                $year = $today->year;
                $week = $today->weekOfYear;
    
                $weekKey = $year . '-' . $week;
                $horoscope = Horoscope::where('type', 'weekly')
                    ->where('sign', $sign)
                    ->where('week_key', $weekKey)
                    ->firstOrFail();
    
                $startOfWeek = Carbon::now()->setISODate($year, $week)->startOfWeek();
                $endOfWeek   = Carbon::now()->setISODate($year, $week)->endOfWeek();
                $formattedDate = $startOfWeek->format('d M, Y') . ' - ' . $endOfWeek->format('d M, Y');
            } elseif ($type === 'monthly') {
                $monthKey = $today->format('Y-m');
                $horoscope = Horoscope::where('type', 'monthly')
                    ->where('sign', $sign)
                    ->where('month_key', $monthKey)
                    ->firstOrFail();
    
                $formattedDate = Carbon::createFromFormat('Y-m', $monthKey)->format('F, Y');
            } elseif ($type === 'yearly') {
                $yearKey = $today->year;
                $horoscope = Horoscope::where('type', 'yearly')
                    ->where('sign', $sign)
                    ->where('year_key', $yearKey)
                    ->firstOrFail();
    
                $formattedDate = $yearKey;
            }
        }

        return Inertia::render('Horoscope/View', [
            'user' => Auth::user->load('wallet'),
            'sign' => ucfirst($sign),
            'type' => ucfirst($type),
            'date' => $formattedDate,
            'horoscope' => [
                'colors' => $horoscope->colors,
                'numbers' => $horoscope->numbers,
                'alphabets' => $horoscope->alphabets,
                'love' => $horoscope->love,
                'health' => $horoscope->health,
                'career' => $horoscope->career,
                'emotions' => $horoscope->emotions,
                'travel' => $horoscope->travel,
                'cosmic_tip' => $horoscope->cosmic_tip,
                'tip_for_singles' => $horoscope->tip_for_singles,
                'tip_for_couples' => $horoscope->tip_for_couples,
                'description' => $horoscope->description,
            ],
        ]);
    }
    
}
