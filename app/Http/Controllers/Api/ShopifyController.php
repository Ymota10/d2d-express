<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShopifySetting;
use App\Models\User;
use Illuminate\Http\Request;

class ShopifyController extends Controller
{
    public function findShop(Request $request)
    {
        $request->validate([
            'shop_id' => 'required|string',
        ]);

        // 1. find user using shop_id
        $user = User::where('shop_id', $request->shop_id)->first();

        if (! $user) {
            return response()->json([
                'status' => false,
                'token' => null,
                'message' => 'Shop not found',
            ]);
        }

        // 2. create token
        $token = $user->createToken('shopify')->plainTextToken;

        // 3. get shopify settings
        $settings = ShopifySetting::where('shop_id', $request->shop_id)->first();

        return response()->json([
            'status' => true,
            'token' => $token,
            'user' => $user,

            // ✅ include shopify settings (if exists)
            'settings' => $settings ? [
                'id' => $settings->id,
                'shop_id' => $settings->shop_id,
                'auto_sync' => $settings->auto_sync,
                'fulfillment_option' => $settings->fulfillment_option,
            ] : null,
        ]);
    }
}
