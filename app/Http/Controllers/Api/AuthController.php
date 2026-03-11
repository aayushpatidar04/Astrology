<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Astrologer;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{

    // -------------------------------------------------------------Astrologers APIs-------------------------------------------------------------
    /**
     * Register a new astrologer
     *
     * This endpoint creates a user with the role "Astrologer", saves their profile details,
     * uploads a profile image, and returns an authentication token.
     *
     * @group Astrologer
     *
     * @bodyParam name string required The astrologer's full name.
     * @bodyParam email string required The astrologer's email address. Must be unique.
     * @bodyParam phone string required The astrologer's phone number (10 digits). Must be unique.
     * @bodyParam password string required The password. Must be confirmed with password_confirmation.
     * @bodyParam password_confirmation string required The password confirmation.
     * @bodyParam expertise array required List of expertise areas. Example: ["Vedic","Tarot"]
     * @bodyParam experience_years integer required Years of experience. Example: 5
     * @bodyParam profile_image file required The astrologer's profile image (jpeg, png, jpg, webp).
     *
     * @response 200 {
     *   "status": "success",
     *   "user": {
     *     "id": 1,
     *     "name": "Aayush",
     *     "email": "aayush@example.com",
     *     "phone": "9876543210"
     *   },
     *   "astrologer": {
     *     "id": 1,
     *     "user_id": 1,
     *     "expertise": ["Vedic"],
     *     "experience_years": 5,
     *     "profile_image": "9876543210_1709720000.jpg"
     *   },
     *   "token": "1|abcdef123456..."
     * }
     */
    public function registerAstrologer(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required',
                'email' => 'required|unique:users,email',
                'phone' => 'required|unique:users|numeric|digits:10',
                'password' => 'required|confirmed',
                'expertise' => 'required|array',
                'experience_years' => 'required|integer',
                'profile_image' => 'required|image',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
                'message' => 'Validation failed',
            ], 200);
        }

        DB::beginTransaction();

        try {

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => Hash::make($data['password'])
            ]);

            $user->assignRole('Astrologer');

            $image = null;
            if ($request->hasFile('profile_image')) {
                $file = $request->profile_image;
                $ext = $file->getClientOriginalExtension();
                $image = '_' . $data['phone'] . '_.' . $ext;
                $file->move(public_path('uploads/astrologer_profile/'), $image);
            }

            $astrologer = Astrologer::create([
                'user_id' => $user->id,
                'expertise' => $data['expertise'],
                'experience_years' => $data['experience_years'],
                'profile_image' => $image,
            ]);

            $token = $user->createToken('myastrosathi')->plainTextToken;

            DB::commit();

            return response()->json([
                'status' => 'success',
                'user' => $user,
                'astrologer' => $astrologer,
                'token' => $token,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Registration failed',
                'error' => $e->getMessage(),
            ], 200);
        }
    }

    public function loginAstrologer(Request $request) {}

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully',
        ], 200);
    }

    // -------------------------------------------------------------Astrologers APIs-------------------------------------------------------------
    public function registerUser(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required',
                'email' => 'required|unique:users,email',
                'phone' => 'required|unique:users|numeric|digits:10',
                'password' => 'required|confirmed',
                'dob' => 'nullable|date',
                'birth_time' => 'nullable',
                'gender' => 'nullable|in:male,female,other',
                'location' => 'nullable',
                'preferred_language' => 'nullable',
                'profile_image' => 'nullable|image',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
                'message' => 'Validation failed',
            ], 200);
        }

        DB::beginTransaction();

        try {

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => Hash::make($data['password'])
            ]);

            $user->assignRole('User');

            $image = null;
            if ($request->hasFile('profile_image')) {
                $file = $request->profile_image;
                $ext = $file->getClientOriginalExtension();
                $image = '_' . $data['phone'] . '_.' . $ext;
                $file->move(public_path('uploads/user_profile/'), $image);
            }

            $details = UserDetail::create([
                'user_id' => $user->id,
                'profile_image' => 'uploads/user_profile/' . $image,
                'dob' => $data['dob'],
                'birth_time' => $data['birth_time'],
                'gender' => $data['gender'],
                'location' => $data['location'],
                'preferred_language' => $data['preferred_language'],
            ]);

            $token = $user->createToken('myastrosathi')->plainTextToken;

            DB::commit();

            return response()->json([
                'status' => 'success',
                'user' => $user,
                'details' => $details,
                'token' => $token,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Registration failed',
                'error' => $e->getMessage(),
            ], 200);
        }
    }

    
}
