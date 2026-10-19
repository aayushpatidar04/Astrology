<?php

namespace App\Http\Controllers;

use App\Models\Astrologer;
use App\Models\Banner;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Horoscope;
use App\Models\RechargePackage;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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

    private const DAYS = 180;

    /*
    |--------------------------------------------------------------------------
    | EDIT THESE to match your own models / columns
    |--------------------------------------------------------------------------
    | Every block is wrapped in safe(), so a wrong model name simply shows an
    | empty widget instead of breaking the page. Set APP_ENV=local to see the
    | exception in your log while you wire things up.
    */

    /** Money users paid in. Needs: amount, created_at. */
    protected function rechargesQuery()
    {
        return Transaction::query()->where('type', 'credit');
    }

    /** Payouts to astrologers (the admin "Transactions" page). Needs: amount, transacted_at, astrologer_id. */
    protected function payoutsQuery()
    {
        return Transaction::query();
    }

    /** Chat sessions. Needs: astrologer_id, created_at. */
    protected function chatsQuery()
    {
        return \App\Models\ChatSession::query();
    }

    /** Call sessions. Needs: astrologer_id, created_at. */
    protected function callsQuery()
    {
        return \App\Models\CallHistory::query();
    }

    /** User wallets. Needs: balance. */
    protected function walletsQuery()
    {
        return \App\Models\UserWallet::query();
    }

    /** User profile details. Needs: gender, preferred_language. */
    protected function detailsQuery()
    {
        return \App\Models\UserDetail::query();
    }

    public function dashboard()
    {
        $users = fn() => User::query()->whereHas('roles', fn($q) => $q->where('name', 'User'));

        $signups = $this->safe(fn() => $this->perDay($users(), 'created_at'), []);
        $revenue = $this->safe(fn() => $this->perDay($this->rechargesQuery(), 'created_at', 'amount'), []);
        $payouts = $this->safe(fn() => $this->perDay($this->payoutsQuery(), 'transacted_at', 'amount'), []);
        $chats = $this->safe(fn() => $this->perDay($this->chatsQuery(), 'created_at'), []);
        $calls = $this->safe(fn() => $this->perDay($this->callsQuery(), 'created_at'), []);

        $daily = collect(range(self::DAYS - 1, 0))->map(function ($i) use ($signups, $revenue, $payouts, $chats, $calls) {
            $d = now()->subDays($i)->toDateString();

            return [
                'date' => $d,
                'signups' => (int) ($signups[$d] ?? 0),
                'revenue' => (float) ($revenue[$d] ?? 0),
                'payouts' => (float) ($payouts[$d] ?? 0),
                'sessions' => (int) (($chats[$d] ?? 0) + ($calls[$d] ?? 0)),
            ];
        })->values();

        return Inertia::render('Dashboard', [
            'totals' => [
                'users' => $this->safe(fn() => $users()->count(), 0),
                'recharge_total' => $this->safe(fn() => (float) $this->rechargesQuery()->sum('amount'), 0),
                'payouts_total' => $this->safe(fn() => (float) $this->payoutsQuery()->sum('amount'), 0),
                'sessions' => $this->safe(fn() => $this->chatsQuery()->count(), 0)
                    + $this->safe(fn() => $this->callsQuery()->count(), 0),
                'wallet_balance' => $this->safe(fn() => (float) $this->walletsQuery()->sum('balance'), 0),
                'busy_astrologers' => $this->safe(fn() => Astrologer::where('is_busy', true)->count(), 0),
                'blogs' => $this->safe(fn() => \App\Models\Blog::count(), 0),
                'banners' => $this->safe(fn() => \App\Models\Banner::count(), 0),
                'banners_active' => $this->safe(fn() => \App\Models\Banner::where('active', true)->count(), 0),
                'horoscopes' => $this->safe(fn() => \App\Models\Horoscope::count(), 0),
                'packages' => $this->safe(fn() => \App\Models\RechargePackage::count(), 0),
            ],

            'daily' => $daily,

            'astrologerStatus' => $this->safe(
                fn() => Astrologer::query()->selectRaw('status, COUNT(*) as c')->groupBy('status')->pluck('c', 'status'),
                collect()
            ),

            'topAstrologers' => $this->safe(fn() => $this->topAstrologers(), []),
            'gender' => $this->safe(fn() => $this->gender(), []),
            'languages' => $this->safe(fn() => $this->languages(), []),
            'horoscopeTypes' => $this->safe(
                fn() => \App\Models\Horoscope::query()->selectRaw('type, COUNT(*) as c')->groupBy('type')->pluck('c', 'type'),
                collect()
            ),

            'pendingAstrologers' => $this->safe(fn() => Astrologer::with('user:id,name,email')
                ->where('status', 'pending_verification')->latest()->limit(5)->get()
                ->map(fn($a) => [
                    'id' => $a->id,
                    'name' => $a->user?->name,
                    'email' => $a->user?->email,
                    'experience_years' => $a->experience_years,
                ]), []),

            'recentPayouts' => $this->safe(fn() => $this->payoutsQuery()->with('astrologer.user:id,name')
                ->latest('transacted_at')->limit(5)->get()
                ->map(fn($t) => [
                    'name' => $t->astrologer?->user?->name,
                    'amount' => $t->amount,
                    'mode' => $t->mode,
                    'reference_no' => $t->reference_no,
                    'date' => $t->transacted_at,
                ]), []),

            'recentUsers' => $this->safe(fn() => $users()->latest()->limit(5)->get(['id', 'name', 'email', 'phone', 'created_at']), []),
        ]);
    }

    /** { 'YYYY-MM-DD' => count | sum } for the last DAYS days. */
    protected function perDay($query, string $dateColumn, ?string $sumColumn = null): array
    {
        $from = now()->subDays(self::DAYS - 1)->startOfDay();
        $aggregate = $sumColumn ? "SUM($sumColumn)" : 'COUNT(*)';

        return $query->where($dateColumn, '>=', $from)
            ->selectRaw("DATE($dateColumn) as d, $aggregate as v")
            ->groupBy('d')
            ->pluck('v', 'd')
            ->all();
    }

    protected function topAstrologers(): array
    {
        $counts = collect();

        foreach ([fn() => $this->chatsQuery(), fn() => $this->callsQuery()] as $make) {
            $rows = $this->safe(
                fn() => $make()->selectRaw('astrologer_id, COUNT(*) as c')->groupBy('astrologer_id')->pluck('c', 'astrologer_id'),
                collect()
            );

            foreach ($rows as $id => $c) {
                $counts[$id] = ($counts[$id] ?? 0) + $c;
            }
        }

        $top = $counts->sortDesc()->take(5);
        if ($top->isEmpty()) {
            return [];
        }

        $paid = $this->safe(
            fn() => $this->payoutsQuery()->whereIn('astrologer_id', $top->keys())
                ->selectRaw('astrologer_id, SUM(amount) as s')->groupBy('astrologer_id')->pluck('s', 'astrologer_id'),
            collect()
        );
        $names = Astrologer::with('user:id,name')->whereIn('id', $top->keys())->get()->keyBy('id');

        return $top->map(fn($c, $id) => [
            'name' => $names[$id]->user?->name ?? "Astrologer #$id",
            'sessions' => $c,
            'paid' => (float) ($paid[$id] ?? 0),
        ])->values()->all();
    }

    protected function gender(): array
    {
        return $this->detailsQuery()
            ->selectRaw('LOWER(gender) as g, COUNT(*) as c')
            ->whereNotNull('gender')
            ->groupBy('g')
            ->pluck('c', 'g')
            ->all();
    }

    /** preferred_language may hold "Hindi, English" - split and count each language. */
    protected function languages(): array
    {
        return $this->detailsQuery()->whereNotNull('preferred_language')->pluck('preferred_language')
            ->flatMap(fn($v) => is_array($v) ? $v : preg_split('/[,\/|]+/', (string) $v))
            ->map(fn($v) => Str::title(trim((string) $v)))
            ->filter()
            ->countBy()
            ->sortDesc()
            ->take(6)
            ->map(fn($c, $l) => ['label' => $l, 'value' => $c])
            ->values()
            ->all();
    }

    protected function safe(callable $fn, $default = null)
    {
        try {
            return $fn();
        } catch (\Throwable $e) {
            if (app()->isLocal()) {
                report($e);
            }

            return $default;
        }
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
            'link' => 'nullable',
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

    public function deleteBanner($id)
    {
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
            'active' => $banner->active,
        ]);
    }

    public function rechargePackages()
    {
        $packages = RechargePackage::all();
        return Inertia::render('Dashboard/Admin/RechargePackage/Index', [
            'packages' => $packages
        ]);
    }

    public function rechargePackageStore(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric',
            'bonus_amount' => 'nullable|numeric',
            'label' => 'nullable',
            'type' => 'required|in:first_time,regular,special',
            'recommended' => 'nullable',
        ]);

        if ($request->recommended && $request->recommended != "0") {
            $validated['recommended'] = 1;
        } else {
            $validated['recommended'] = 0;
        }

        $package = RechargePackage::create($validated);

        return redirect()->back();
    }

    public function updateRechargePackage(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric',
            'bonus_amount' => 'nullable|numeric',
            'label' => 'nullable',
            'type' => 'required|in:first_time,regular,special',
            'recommended' => 'nullable',
        ]);

        if ($request->recommended && $request->recommended != "0") {
            $validated['recommended'] = 1;
        } else {
            $validated['recommended'] = 0;
        }

        $package = RechargePackage::findOrFail($id);

        $package->update($validated);

        return redirect()->back();
    }

    public function deleteRechargePackage($id)
    {
        $package = RechargePackage::findOrFail($id);

        $package->delete();

        return redirect()->back();
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
            'asked_call_price' => ['required', 'numeric', 'min:0'],
            'charged_call_price' => ['required', 'numeric', 'gte:asked_call_price'],
            'asked_text_price' => ['required', 'numeric', 'min:0'],
            'charged_text_price' => ['required', 'numeric', 'gte:asked_text_price'],
        ]);

        $astrologer->update($validated);

        return Redirect::back();
    }

    public function users()
    {
        $beamsClient = new PushNotifications([
            'instanceId' => config('services.pusher_beams.instance_id'),
            'secretKey' => config('services.pusher_beams.secret_key'),
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

    public function recordTransaction(Request $request)
    {
        $validated = $request->validate([
            'astrologer_id' => 'required|exists:astrologers,id',
            'amount' => 'required|numeric',
            'mode' => 'required|string',
            'reference_no' => 'nullable|string',
            'remarks' => 'nullable|string',
            'proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048', // allow image/pdf proof
        ]);

        // Handle file upload
        $proofPath = null;
        if ($request->hasFile('proof')) {
            $file = $request->file('proof');
            $filename = time() . '_' . $file->getClientOriginalName();

            // Move file into /public/transaction_proofs
            $file->move(public_path('transaction_proofs'), $filename);

            // Save relative path (so you can easily build URL later)
            $proofPath = 'transaction_proofs/' . $filename;
        }


        $transaction = Transaction::create([
            'astrologer_id' => $validated['astrologer_id'],
            'amount' => $validated['amount'],
            'mode' => $validated['mode'],
            'reference_no' => $validated['reference_no'] ?? null,
            'remarks' => $validated['remarks'] ?? null,
            'proof' => $proofPath, // store relative path
        ]);

        return response()->json([
            'message' => 'Transaction recorded successfully',
            'data' => $transaction
        ]);
    }

    public function transactions()
    {
        $transactions = Transaction::with('astrologer.user')
            ->orderBy('transacted_at', 'desc')
            ->paginate(20);

        return inertia('Dashboard/Admin/Transactions/Index', [
            'transactions' => $transactions,
        ]);
    }

    // Store new transaction (manual entry)
    public function transactionStore(Request $request)
    {
        $validated = $request->validate([
            'astrologer_id' => 'required|exists:astrologers,id',
            'amount' => 'required|numeric',
            'mode' => 'required|string',
            'reference_no' => 'nullable|string',
            'remarks' => 'nullable|string',
            'proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $proofPath = null;
        if ($request->hasFile('proof')) {
            $file = $request->file('proof');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('transaction_proofs'), $filename);
            $proofPath = 'transaction_proofs/' . $filename;
        }

        Transaction::create([
            'astrologer_id' => $validated['astrologer_id'],
            'amount' => $validated['amount'],
            'mode' => $validated['mode'],
            'reference_no' => $validated['reference_no'] ?? null,
            'remarks' => $validated['remarks'] ?? null,
            'proof' => $proofPath,
        ]);

        return redirect()->route('admin.transactions.index')->with('success', 'Transaction recorded successfully.');
    }
}
