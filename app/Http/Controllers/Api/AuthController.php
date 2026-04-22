<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShopifySetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Incorrect email or password.',
            ], 401);
        }

        // create token
        $token = $user->createToken('mobile')->plainTextToken;

        // get shopify settings if shop_id exists
        $settings = null;

        if ($user->shop_id) {
            $settings = ShopifySetting::where('shop_id', $user->shop_id)->first();
        }

        return response()->json([
            'status' => true,
            'message' => 'Login successful.',
            'token' => $token,
            'user' => $user,

            // ✅ add settings
            'settings' => $settings ? [
                'id' => $settings->id,
                'shop_id' => $settings->shop_id,
                'auto_sync' => $settings->auto_sync,
                'fulfillment_option' => $settings->fulfillment_option,
            ] : null,
        ]);
    }
}
