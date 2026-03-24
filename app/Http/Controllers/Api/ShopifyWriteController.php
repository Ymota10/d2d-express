<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ShopifyWriteController extends Controller
{
    public function linkShop(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'shop_id' => 'required|string',
        ]);

        $user = User::find($request->user_id);

        if ($user->shop_id !== null) {
            return response()->json([
                'status' => false,
                'message' => 'User already connected to shop',
            ]);
        }

        if (User::where('shop_id', $request->shop_id)->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'This shop_id is already linked to another user',
            ]);
        }

        $user->shop_id = $request->shop_id;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'shop_id connected to user successfully',
            'user_id' => $user->id,
            'shop_id' => $user->shop_id,
        ]);
    }
}
