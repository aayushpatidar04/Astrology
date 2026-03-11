<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

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

        return Inertia::render('Dashboard/Blog/Index', [
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
}
