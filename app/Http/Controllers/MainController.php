<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MainController extends Controller
{
    public function index()
    {
        return Inertia::render('HomePage/Index', [
            'user' => Auth::user(),
            'blogs' => Blog::latest()->take(3)->get()
        ]);
    }

    public function about()
    {
        return Inertia::render('AboutUs/Index', [
            'user' => Auth::user(),
        ]);
    }

    public function services()
    {
        return Inertia::render('Services/Index', [
            'user' => Auth::user(),
        ]);
    }

    public function blog()
    {
        return Inertia::render('Blog/Index', [
            'user' => Auth::user(),
            'blogs' => Blog::latest()->paginate(20),
            'categories' => BlogCategory::all()
        ]);
    }

    public function categoryBlogs($slug)
    {
        $category = BlogCategory::where('slug', $slug)->first();

        if ($category) {
            return Inertia::render('Blog/Index', [
                'user' => Auth::user(),
                'blogs' => $category->blogs()
                    ->latest()
                    ->paginate(20),
                'categories' => BlogCategory::all()
            ]);
        } else {
            return redirect()->back()->with('error', 'No such category exist');
        }
    }

    public function blogDetails($slug){
        $blog = Blog::where('slug', $slug)->first();

        return Inertia::render('Blog/View', [
            'user' => Auth::user(),
            'blog' => $blog,
            'categories' => BlogCategory::all()
        ]);
    }
}
