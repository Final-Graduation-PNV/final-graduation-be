<?php

namespace App\Http\Controllers\API\ShopOwner;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class ShopOwnerController extends Controller
{
    public function checkoutAccount(Request $request)
    {
        try {
            $id = $request->user()->id;

            $shop = Shop::where('user_id', $id)->first();

            if (!$shop->end_time) {
                return response()->json([
                    'message' => 'Your account has expired. You must pay to continue using!',
                ],402);
            }

            if ($shop) {
                $expires = $shop->end_time;
                $date = Carbon::now()->format('Y-m-d');

                if ($date > $expires) {
                    $shop->renewal = false;
                    $shop->save();
                    $expireArray = [
                        'message' => 'Your account has expired. You must pay to continue using!',
                        'name' => $shop->name,
                        'date_used' => $date,
                        'date_expires' => $expires
                    ];
                    return response()->json([
                        'valid_account' => [$expireArray]
                    ], 402);
                } else {
                    $validArray = [
                        'message' => 'Your account has not expired!',
                        'name' => $shop->name,
                        'date_used' => $date,
                        'date_expires' => $expires
                    ];

                    return response()->json([
                        'valid_account' => [$validArray]
                    ], 202);
                }
            } else {
                return response()->json([
                    'message' => 'Something went wrong!',
                ], 500);
            }
        } catch (\Exception $e) {
            // Log the error for debugging purposes
            Log::error($e);

            // Return an error response to the client
            return response()->json([
                'message' => 'An error occurred while processing your request.',
            ], 400);
        }
    }

    public function profile(Request $request)
    {
        try {
            $shop = Shop::join('users', 'users.id', '=', 'shops.user_id')
                ->first(['shops.*', 'users.email']);

            return response()->json([
                'shop' => $shop
            ], 202);
        } catch (Exception $e) {
            return response()->json(['message' => 'An error occurred while fetching user data'], 500);
        }
    }

    public function editProfile(Request $request)
    {
        try {
            $id = $request->user()->id;

            $shop = Shop::where('user_id', $id)->first();

            // If user not found, return error
            if (!$shop) {
                return response()->json(['message' => 'Shop not found'], 404);
            }

            $data = $request->only(['name', 'avatar', 'phone', 'birth', 'gender', 'address', 'city']);

            // If no data was submitted, return success message
            if (empty(array_filter($data))) {
                return response()->json(['message' => 'Information has not changed!'], 200);
            }

            // Update shop's profile
            $shop->fill($data);
            $shop->save();

            return response()->json(['message' => 'Profile updated successfully!'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'An error occurred while updating user profile'], 500);
        }
    }

    public function checkoutPayment(Request $request)
    {
        $id = $request->user()->id;

        $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
        $vnp_Returnurl = "https://decoplantsflowers-f381a.web.app/";
        $vnp_TmnCode = "VPUPIB82";// Terminal ID
        $vnp_HashSecret = "WTLWKPUMSRUSENTTMVAJQNJDELXFQJOR"; // Secret Key

        $vnp_TxnRef = date('YmdHis') . "-" . $id; // Code orders. In fact, the Merchant needs to insert the order into the DB and send this code to VNPAY
        $vnp_OrderInfo = "Payment continues using Shop Owner account";
        $vnp_OrderType = 250000;
        $vnp_Amount = 200000 * 100;
        $vnp_Locale = 'en';
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
