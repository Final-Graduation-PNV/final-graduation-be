<?php

namespace App\Http\Controllers\API\ShopOwner;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ShopOwnerController extends Controller
{
    public $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
    public $vnp_Returnurl = "https://localhost/vnpay_php/vnpay_return.php";
    public $vnp_TmnCode = "VPUPIB82";//Mã website tại VNPAYreturnU
    public $vnp_HashSecret = "WTLWKPUMSRUSENTTMVAJQNJDELXFQJOR"; //Chuỗi bí mật

    public function checkoutAccount(Request $request)
    {
        $id = $request->user()->id;

        $shop = User::join('role_user', 'role_user.user_id', '=', 'users.id')
            ->where('role_user.user_id', $id)
            ->first('users.*');

        if ($shop && $shop->end_time) {
            $date = Carbon::createFromFormat('Y-m-d H:i:s', $shop->end_time)->format('Y-m-d');
            $expires = Carbon::now()->format('Y-m-d');

            if ($date === $expires) {
                $delete = DB::table('role_user')
                    ->where('role_id', 2)
                    ->where('user_id', $id)
                    ->delete();
                $expireArray = [
                    'message' => 'Your account has expired. You must pay to continue using!',
                    'name' => $shop->name,
                    'date_used' => $date,
                    'date_expires' => $expires
                ];
                if ($delete) {
                    return response()->json([
                        'valid_account' => [$expireArray]
                    ], 402);
                } else {
                    return response()->json([
                        'valid_account' => [$expireArray]
                    ], 402);
                }
            } else {
                $validArray = [
                    'message' => 'Your account has not expired!',
                    'name' => $shop->name,
                    'date_used' => $date,
                    'date_expires' => $expires
                ];

                return response()->json([
                    'valid_account' => [$validArray]
                ], 200);
            }
        } else {
            return response()->json([
                'message' => 'Something went wrong!',
            ], 500);
        }
    }

    public function checkoutPayMent(Request $request)
    {
        $id = $request->user()->id;

        $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
        $vnp_Returnurl = "https://localhost/vnpay_php/vnpay_return.php";
        $vnp_TmnCode = "VPUPIB82";//Mã website tại VNPAYreturnU
        $vnp_HashSecret = "WTLWKPUMSRUSENTTMVAJQNJDELXFQJOR"; //Chuỗi bí mật

        $vnp_TxnRef = date('YmdHis') . $id; //Mã đơn hàng. Trong thực tế Merchant cần insert đơn hàng vào DB và gửi mã này sang VNPAY
        $vnp_OrderInfo = "Payment continues using Shop Owner account";
        $vnp_OrderType = 250000;
        $vnp_Amount = 200000 * 100;
        $vnp_Locale = 'vn';
        $vnp_BankCode = 'NCB';
        $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];

        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef
        );

        if (isset($vnp_BankCode) && $vnp_BankCode != "") {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }

        // return var_dump($inputData);
        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);//
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }
        $returnData = array('code' => '00'
        , 'message' => 'success'
        , 'data' => $vnp_Url);
        if (isset($_POST['redirect'])) {
            header('Location: ' . $vnp_Url);
            die();
        } else {
            return response()->json($returnData, 200);
        }
    }
}
