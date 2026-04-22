<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShopifySetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShopifyWriteController extends Controller
{
    public function linkShop(Request $request)
    {
        $request->validate([
            'users_id' => 'required|exists:users,id',
            'shop_id' => 'required|string',
        ]);

        $user = User::find($request->users_id);

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

        $settings = null;

        DB::transaction(function () use ($request, $user, &$settings) {

            $user->shop_id = $request->shop_id;
            $user->save();

            $settings = ShopifySetting::create([
                'shop_id' => $request->shop_id,
            ])->fresh(); // reload database defaults
        });

        return response()->json([
            'status' => true,
            'message' => 'shop_id connected and shopify settings row created successfully',
            'users_id' => $user->id,
            'shop_id' => $user->shop_id,
            'settings' => [
                'id' => $settings->id,
                'shop_id' => $settings->shop_id,
                'auto_sync' => $settings->auto_sync,
                'fulfillment_option' => $settings->fulfillment_option,
            ],
        ]);
    }
}
