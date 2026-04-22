<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShopifySetting;
use Illuminate\Http\Request;

class UpdateShopifySettingsController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:shopify_settings,id',
            'auto_sync' => 'required|boolean',
            'fulfillment_option' => 'required|in:Manual,Automatic',
        ]);

        // find settings by id
        $settings = ShopifySetting::find($request->id);

        if (! $settings) {
            return response()->json([
                'status' => false,
                'message' => 'Settings not found',
            ]);
        }

        // update values
        $settings->update([
            'auto_sync' => $request->auto_sync,
            'fulfillment_option' => $request->fulfillment_option,
        ]);

        // reload from DB
        $settings->refresh();

        return response()->json([
            'status' => true,
            'message' => 'Shopify settings updated successfully',
            'data' => [
                'id' => $settings->id,
                'auto_sync' => $settings->auto_sync,
                'fulfillment_option' => $settings->fulfillment_option,
            ],
        ]);
    }
}
