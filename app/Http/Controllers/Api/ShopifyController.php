<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ShopifyController extends Controller
{
    public function findShop(Request $request)
    {
        $request->validate([
            'shop_id' => 'required|string',
        ]);

        // find user using shop_id
        $user = User::where('shop_id', $request->shop_id)->first();

        if (! $user) {
            return response()->json([
                'status' => false,
                'token' => null,
                'message' => 'Shop not found',
            ]);
        }

        // create token
        $token = $user->createToken('shopify')->plainTextToken;

        return response()->json([
            'status' => true,
            'token' => $token,
            'users_id' => $user->id,
        ]);
    }
}
