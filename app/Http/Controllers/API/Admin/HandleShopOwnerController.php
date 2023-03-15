<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use function Symfony\Component\Translation\t;

class HandleShopOwnerController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function notificationShopOwnerAccount()
    {
        $shops = User::join('role_user', 'role_user.user_id', '=', 'users.id')
            ->where('role_user.role_id', 2)
            ->get(['users.*']);

        $validArray = [];
        $expireArray = [];

        foreach ($shops as $shop) {
            if ($shop && $shop->end_time) {
                $date = Carbon::createFromFormat('Y-m-d H:i:s', $shop->end_time)->format('Y-m-d');
                $expires = Carbon::now()->format('Y-m-d');

                if (!($date === $expires)) {
                    $validArray[] = [
                        'name' => $shop->name,
                        'date_used' => $date,
                        'date_expires' => $expires
                    ];
                } else {
                    DB::table('role_user')->where('user_id', $shop->id)->delete();
                    $expireArray[] = [
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
