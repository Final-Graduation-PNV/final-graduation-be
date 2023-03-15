<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use function Symfony\Component\Translation\t;

class HandleShopOwnerController extends Controller
{
    public function notificationAccountRenewal(Request $request)
    {
        $shops = User::join('role_user as role', 'role.user_id', '=', 'users.id')
            ->where('users.id', 2)
            ->first('users.*');

        if ($shops && $shops->end_time) {
            $date = Carbon::createFromFormat('Y-m-d H:i:s', $shops->end_time)->format('Y-m-d');
        } else {
            return 'Something went wrong!';
        }

        $expires = Carbon::now()->format('Y-m-d');

        $merge = [
            'date_carbon' => $date,
            'date_account' =>$expires
        ];

        if (!($date === $expires)) {
            return response()->json([
                'message' => 'Your account is still valid!',
                'dates' => $merge
            ],200);
        }

        return response()->json([
            'message' => 'Your 2-month free account has expired!',
            'date' => $merge
        ],402);
    }
}
