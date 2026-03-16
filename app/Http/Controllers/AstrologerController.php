<?php

namespace App\Http\Controllers;

use App\Events\AstrologerStatusChanged;
use App\Events\MessageSent;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Chat;
use App\Models\ChatMessage;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AstrologerController extends Controller
{
    protected array $middleware = [
        'auth',
        'role:Astrologer',
    ];

    public function dashboard()
    {
        return Inertia::render('Dashboard/Astrologer/Dashboard', [
            'user' => Auth::user()->load('astrologer'),
        ]);
    }

    public function status(Request $request)
    {
        $request->validate([
            'online' => 'required|boolean'
        ]);
        try {
            $astrologer = Auth::user()->astrologer;

            if (! $astrologer) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Astrologer profile not found',
                ], 404);
            }

            $astrologer->update([
                'online' => $request->online,
            ]);

            broadcast(new AstrologerStatusChanged($astrologer->user_id, $request->online))->toOthers();

            return response()->json([
                'status' => 'success',
                'online' => $astrologer->online,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function chats($id = null)
    {
        $user = Auth::user();

        $chats = $user->chats()
            ->whereHas('messages') // only chats that have at least one message
            ->with([
                'participants.user',
                'messages' => function ($q) {
                    $q->latest()->limit(1);
                }
            ])
            ->withMax('messages', 'created_at')
            ->orderByDesc('messages_max_created_at')
            ->get();

        $chat = null;
        $messages = [];

        if ($id) {
            $chat = Chat::with(['participants.user', 'messages.user'])->findOrFail($id);
            $messages = $chat->messages;
        }

        return Inertia::render('Dashboard/Astrologer/Chats', [
            'user'  => $user->load('astrologer'),
            'chats' => $chats,
            'chat'     => $chat,
            'messages' => $messages,
        ]);
    }

    public function storeMessage(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $chat = Chat::findOrFail($id);

        // Create the new message
        $message = ChatMessage::create([
            'chat_id'   => $chat->id,
            'user_id' => Auth::id(),
            'message'   => $request->message,
        ]);

        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }




    public function edit(Request $request): Response
    {
        return Inertia::render('Dashboard/Astrologer/Profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'user' => Auth::user()->load('astrologer'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($request->user()->id)],
            'phone' => ['required', 'numeric', 'digits:10', Rule::unique('users')->ignore($request->user()->id)],
        ]);

        $request->user()->fill($validated);

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('astrologer.profile.edit');
    }

    public function updateAstrologer(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'bio'             => ['required', 'string'],
            'expertise'       => ['required', 'array'],
            'experience_years' => ['required', 'numeric'],
            'profile_image' => ['required'],
            'documents'        => ['nullable', 'array'],
            'documents.*.name' => ['required_with:documents', 'string'],
            'documents.*.file'          => ['nullable', 'file', 'mimes:pdf,jpg,png', 'max:2048'],
            'documents.*.document_path' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('profile_image')) {
            $file = $request->profile_image;
            $ext = $file->getClientOriginalExtension();
            $filename = '_' . $request->user()->phone . '_.' . $ext;
            $file->move(public_path('uploads/astrologer_profile/'), $filename);
            $validated['profile_image'] = 'uploads/astrologer_profile/' . $filename;
        }

        $docs = [];
        if ($request->has('documents')) {
            foreach ($request->documents as $doc) {
                $path = $doc['document_path'] ?? null;

                if (!empty($doc['file'])) {
                    $ext      = $doc['file']->getClientOriginalExtension();
                    $slugName = Str::slug($doc['name']);
                    $filename = '_' . $request->user()->phone . '_' . $slugName . '_.' . $ext;
                    $doc['file']->move(public_path('uploads/documents/'), $filename);
                    $path = 'uploads/documents/' . $filename;
                }

                $docs[] = [
                    'name'          => $doc['name'],
                    'document_path' => $path,
                ];
            }
        }

        $validated['documents'] = $docs;

        $astrologer = $request->user()->astrologer;
        $oldDocs = $astrologer->documents ?? [];
        $oldImage = $astrologer->profile_image;
        $astrologer->fill($validated);
        $astrologer->save();

        if ($request->hasFile('profile_image') && $oldImage && file_exists(public_path($oldImage))) {
            @unlink(public_path($oldImage));
        }

        $oldPaths = collect($oldDocs)->pluck('document_path')->filter();
        $newPaths = collect($docs)->pluck('document_path')->filter();
        $removedPaths = $oldPaths->diff($newPaths);

        // Unlink removed files
        foreach ($removedPaths as $removed) {
            if ($removed && file_exists(public_path($removed))) {
                @unlink(public_path($removed));
            }
        }

        return Redirect::route('astrologer.profile.edit');
    }


    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
