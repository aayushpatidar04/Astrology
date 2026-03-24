<?php

namespace App\Http\Controllers;

use App\Models\Astrologer;
use App\Models\Banner;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Horoscope;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Pusher\PushNotifications\PushNotifications;

class AdminController extends Controller
{
    protected array $middleware = [
        'auth',
        'role:Admin',
    ];

    public function dashboard()
    {
        return Inertia::render('Dashboard');
    }

    public function blogs()
    {
        $blogs = Blog::with(['category'])->latest()->get();
        $categories = BlogCategory::all();

        return Inertia::render('Dashboard/Admin/Blog/Index', [
            'blogs' => $blogs,
            'categories' => $categories
        ]);
    }

    public function blogCategoryStore(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|unique:blog_categories,name',
            'meta_title' => 'required',
            'meta_description' => 'required',
        ]);

        $data['slug'] = Str::slug($data['name']);

        BlogCategory::create($data);

        return redirect()->route('blogs')->with('success', 'Category added successfully!');
    }

    public function blogStore(Request $request)
    {
        $data = $request->validate([
            'blog_category_id' => 'required|exists:blog_categories,id',
            'title' => 'required|unique:blogs,title',
            'meta_description' => 'required',
            'short_description' => 'required',
            'description1' => 'required',
            'description2' => 'nullable',
            'images' => 'required|array',
            'images.*.image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'images.*.alt_text' => 'required|string',
            'tags' => 'nullable|array'
        ]);

        $data['slug'] = Str::slug($data['title']);

        // Process images: move to public_path and store file paths
        $storedImages = [];
        foreach ($request->images as $index => $img) {
            if (isset($img['image'])) {
                $file = $img['image'];
                $filename = time() . '_' . $index . '_' . Str::slug($img['alt_text']) . '_.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/blogs'), $filename);

                $storedImages[] = [
                    'image' => 'uploads/blogs/' . $filename,
                    'alt_text' => $img['alt_text'],
                ];
            }
        }

        $data['images'] = $storedImages;

        $blog = Blog::create($data);

        return redirect()->route('blogs')->with('success', 'Blog created successfully!');
    }

    public function blogUpdate($id, Request $request)
    {
        $data = $request->validate([
            'blog_category_id' => 'required|exists:blog_categories,id',
            'title' => 'required|unique:blogs,title,' . $id,
            'meta_description' => 'required',
            'short_description' => 'required',
            'description1' => 'required',
            'description2' => 'nullable',
            'images' => 'nullable|array',
            'images.*.image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'images.*.alt_text' => 'nullable|string',
            'removed_images' => 'nullable|array',
            'tags' => 'nullable|array'
        ]);

        $data['slug'] = Str::slug($data['title']);

        $blog = Blog::findOrFail($id);
        $existingImages = $blog->images ?? [];

        // Remove marked images
        if ($request->has('removed_images')) {
            foreach ($request->removed_images as $path) {
                $fullPath = public_path($path);
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
                $existingImages = array_filter($existingImages, fn($img) => $img['image'] !== $path);
            }
        }

        // Process images: move to public_path and store file paths
        $storedImages = [];
        foreach ($request->images as $index => $img) {
            if (isset($img['image'])) {
                $file = $img['image'];
                $filename = time() . '_' . $index . '_' . Str::slug($img['alt_text']) . '_.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/blogs'), $filename);

                $storedImages[] = [
                    'image' => 'uploads/blogs/' . $filename,
                    'alt_text' => $img['alt_text'],
                ];
            }
        }

        $data['images'] = array_merge($existingImages, $storedImages);

        $blog->update($data);

        return redirect()->route('blogs')->with('success', 'Blog created successfully!');
    }

    public function blogDelete($id)
    {
        $blog = Blog::findOrFail($id);

        if (!empty($blog->images)) {
            foreach ($blog->images as $img) {
                $path = public_path($img['image']); // assuming 'image' holds relative path
                if (file_exists($path)) {
                    unlink($path);
                }
            }
        }

        $blog->forceDelete();

        return redirect()->route('blogs')->with('success', 'Blog deleted successfully!');
    }

    public function horoscopes()
    {
        return Inertia::render('Dashboard/Admin/Horoscope/Index', [
            'horoscopes' => Horoscope::latest()->get(),
            'user' => Auth::user()?->load('wallet')
        ]);
    }

    public function storeHoroscope(Request $request)
    {
        $validated = $request->validate([
            'sign' => 'required|string|in:Aries,Taurus,Gemini,Cancer,Leo,Virgo,Libra,Scorpio,Sagittarius,Capricorn,Aquarius,Pisces',
            'type' => 'required|in:daily,weekly,monthly,yearly',

            // Time keys
            'date' => 'nullable|date|required_if:type,daily',
            'week_key' => 'nullable|string|max:7|required_if:type,weekly|regex:/^\d{4}-\d{2}$/',
            'month_key' => 'nullable|string|max:7|required_if:type,monthly|regex:/^\d{4}-\d{2}$/',
            'year_key' => 'nullable|string|max:4|required_if:type,yearly|regex:/^\d{4}$/',

            // Horoscope content
            'colors' => 'required|string',
            'numbers' => 'required|string',
            'alphabets' => 'required|string',
            'love' => 'required|string',
            'health' => 'required|string',
            'career' => 'required|string',
            'emotions' => 'required|string',
            'travel' => 'required|string',
            'description' => 'required|string',
            'cosmic_tip' => 'required|string',
            'tip_for_singles' => 'required|string',
            'tip_for_couples' => 'required|string',
        ]);

        Horoscope::create($validated);

        return redirect()->back()->with('success', 'Horoscope added successfully!');
    }

    public function banners()
    {
        $banners = Banner::orderBy('active', 'desc')->paginate(12);
        return Inertia::render('Dashboard/Admin/Banner/Index', [
            'banners' => $banners,
        ]);
    }

    public function bannerStore(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|unique:banners,title',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'link' => 'nullable',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->image;
            $filename = Str::slug($validated['title']) . '_.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/banners'), $filename);

            $validated['image'] = 'uploads/banners/' . $filename;
        }

        $banner = Banner::create($validated);
        return redirect()->back();
    }

    public function updateBanner(Request $request, $id)
    {
        $rules = [
            'title' => 'required|unique:banners,title,' . $id,
            'link'  => 'nullable',
        ];

        if ($request->hasFile('image')) {
            $rules['image'] = 'image|mimes:jpeg,png,jpg,gif,svg|max:2048';
        } else {
            $rules['image'] = 'nullable|string';
        }

        $validated = $request->validate($rules);

        $flag = False;
        if ($request->hasFile('image')) {
            $file = $request->image;
            $filename = Str::slug($validated['title']) . '_.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/banners'), $filename);

            $validated['image'] = 'uploads/banners/' . $filename;
            $flag = True;
        }

        $banner = Banner::findOrFail($id);
        $oldImage = $banner->image;
        $banner->update($validated);
        if ($flag && $oldImage && File::exists(public_path($oldImage))) {
            File::delete(public_path($oldImage));
        }
        return redirect()->back();
    }

    public function deleteBanner($id){
        $banner = Banner::findOrFail($id);

        $path = public_path($banner->image);
        if (file_exists($path)) {
            unlink($path);
        }

        $banner->delete();

        return redirect()->back();
    }

    public function activeBanner(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);
        $banner->active = $request->boolean('active');
        $banner->save();

        return response()->json([
            'success' => true,
            'message' => 'Banner status updated!',
            'active'  => $banner->active,
        ]);
    }

    public function astrologers()
    {
        $astrologers = User::with(['astrologer.verifier'])->role('Astrologer')->get();
        return Inertia::render('Dashboard/Admin/Astrologers/Index', [
            'astrologers' => $astrologers
        ]);
    }

    public function updateStatus(Request $request, Astrologer $astrologer): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'in:pending_verification,verified,active,rejected'],
        ]);

        $astrologer->status = $request->status;
        $astrologer->verified_by = $request->user()->id;
        $astrologer->save();

        return Redirect::back();
    }

    public function updatePricing(Request $request, Astrologer $astrologer): RedirectResponse
    {
        $validated = $request->validate([
            'asked_call_price'   => ['required', 'numeric', 'min:0'],
            'charged_call_price' => ['required', 'numeric', 'gte:asked_call_price'],
            'asked_text_price'   => ['required', 'numeric', 'min:0'],
            'charged_text_price' => ['required', 'numeric', 'gte:asked_text_price'],
        ]);

        $astrologer->update($validated);

        return Redirect::back();
    }

    public function users()
    {
        $beamsClient = new PushNotifications([
            "instanceId" => config('app.VITE_PUSHER_BEAMS_INSTANCE_ID'),
            "secretKey" => config('app.VITE_PUSHER_BEAMS_SECRET_KEY'),
        ]);

        $beamsClient->publishToInterests(
            ["NewsLetter"],
            [
                "web" => [
                    "notification" => [
                        "title" => "Hey There!",
                        "body" => "Testing Newsletter interests",
                        "icon" => "https://img.icons8.com/?size=100&id=LoL4bFzqmAa0&format=png&color=000000",
                        "deep_link" => "https://aayushpatidar04.github.io",
                        "data" => [],
                    ]
                ]
            ]
        );

        $users = User::with(['details'])->role('User')->get();
        return Inertia::render('Dashboard/Admin/Users/Index', [
            'users' => $users
        ]);
    }
}
