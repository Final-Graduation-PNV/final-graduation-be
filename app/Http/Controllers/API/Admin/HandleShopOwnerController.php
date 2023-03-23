<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use function Symfony\Component\Translation\t;

class HandleShopOwnerController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function notificationShopOwnerAccount()
    {
        $shops = Shop::all();

        $validArray = [];
        $expireArray = [];

        foreach ($shops as $shop) {
            if ($shop && $shop->end_time) {
                $date = Carbon::createFromFormat('Y-m-d H:i:s', $shop->end_time)->format('Y-m-d');
                $expires = Carbon::now()->format('Y-m-d');

                if (!($date === $expires)) {
                    $validArray[] = [
                        'shop_id' => $shop->id,
                        'name' => $shop->name,
                        'date_used' => $date,
                        'date_expires' => $expires
                    ];
                } else {
                    $expireArray[] = [
                        'shop_id' => $shop->id,
                        'name' => $shop->name,
                        'date_used' => $date,
                        'date_expires' => $expires
                    ];
                }
            } else {
                return response()->json([
                    'message' => 'Something went wrong!',
                ], 500);
            }
        }
        $response = [
            'valid_accounts' => $validArray,
            'expired_accounts' => $expireArray
        ];

        return response()->json($response);
    }
}
