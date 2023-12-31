<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialController extends Controller
{
    public function googleLoginUrl()

    {
        return Socialite::driver('google')->redirect();
    }

    public function loginCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            $user = User::where('email', $googleUser->email)->first();
            $googleId = User::where('password', $googleUser->id)->first();
            if ($googleId) {
                Auth::login($user);
                return response()->json([
                    'status' => __('Google sign in successful'),
                    'data' => $googleId,
                ], Response::HTTP_OK);
            }
            if ($user) {
                throw new \Exception(__('Google sign in email existed'));
            }
            $user = User::create(
                [
                    'email' => $googleUser->email,
                    'name' => $googleUser->name,
                    'google_id' => $googleUser->id,
                    'password' => $googleUser->id,
                ]
            );
            Auth::login($user);
            return response()->json([
                'status' => __('Google sign in successful'),
                'data' => $user,
            ], Response::HTTP_CREATED);
        } catch (\Exception $exception) {
            return response()->json([
                'status' => __('Google sign in failed'),
                'error' => $exception,
                'message' => $exception->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
