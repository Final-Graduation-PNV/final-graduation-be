<?php

namespace App\Http\Controllers\API\ShopOwner;

use App\Http\Controllers\Controller;
use App\Mail\RenewalShopOwnerAccount;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Process;

class ShopOwnerController extends Controller
{
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
        $vnp_TmnCode = "VPUPIB82";// Terminal ID
        $vnp_HashSecret = "WTLWKPUMSRUSENTTMVAJQNJDELXFQJOR"; // Secret Key

        $vnp_TxnRef = date('YmdHis') ."-". $id; // Code orders. In fact, the Merchant needs to insert the order into the DB and send this code to VNPAY
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
            "vnp_ReturnUrl" => route('return'),
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

    public function vnpayPayment()
    {
        $vnp_HashSecret = "WTLWKPUMSRUSENTTMVAJQNJDELXFQJOR";

        $inputData = array();
        $returnData = array();

        foreach ($_GET as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }

        $vnp_SecureHash = $inputData['vnp_SecureHash'];
        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
        $vnpTranId = $inputData['vnp_TransactionNo']; // Transaction code at VNPAY
        $vnp_BankCode = $inputData['vnp_BankCode']; // Bank payment
        $vnp_Amount = $inputData['vnp_Amount'] / 100; // Payment amount VNPAY feedback

        $Status = 0; // Is the payment status of the transaction that does not have an IPN stored in the merchant's system in the direction of the payment URL origination.
        $orderId = $inputData['vnp_TxnRef']; // form userId-orderId
         $userId = explode("-", $orderId)[1];
        $user = User::find($userId);

        try {
            if ($secureHash == $vnp_SecureHash) {
                $order = NULL;
                if ($order != NULL) {
                    if ($order["Amount"] == $vnp_Amount) //Kiểm tra số tiền thanh toán của giao dịch: giả sử số tiền kiểm tra là đúng. //$order["Amount"] == $vnp_Amount
                    {
                        if ($order["Status"] != NULL && $order["Status"] == 0) {
                            if ($inputData['vnp_ResponseCode'] == '00' || $inputData['vnp_TransactionStatus'] == '00') {
                                $Status = 1;
                            } else {
                                $Status = 2;
                            }
                            if ($Status == 1) {
                                if (isset($orderDB->user) && $orderDB->user->email !== null) {
                                    Mail::to($user->email)->send(new RenewalShopOwnerAccount($user));
                                }
                            } else if ($Status == 2) {
                                return response()->json("Account renewal failed!", 402);
                            }
                            $returnData['RspCode'] = '00';
                            $returnData['Message'] = 'Confirm Success';
                        } else {
                            $returnData['RspCode'] = '02';
                            $returnData['Message'] = 'Order already confirmed';
                        }
                    } else {
                        $returnData['RspCode'] = '04';
                        $returnData['Message'] = 'invalid amount';
                    }
                } else {
                    $returnData['RspCode'] = '01';
                    $returnData['Message'] = 'Order not found';
                }
            } else {
                $returnData['RspCode'] = '97';
                $returnData['Message'] = 'Invalid signature';
            }
        } catch (Exception $e) {
            $returnData['RspCode'] = '99';
            $returnData['Message'] = 'Unknow error';
        }
        return json_encode($returnData);
    }

    public function vnpayReturn(Request $request)
    {
        $vnp_HashSecret = "WTLWKPUMSRUSENTTMVAJQNJDELXFQJOR";
        $vnp_SecureHash = $_GET['vnp_SecureHash'];
        $inputData = array();
        foreach ($_GET as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }

        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
        if ($secureHash == $vnp_SecureHash) {
            if ($_GET['vnp_ResponseCode'] == '00') {
                $id = $request->user()->id;
                $user = User::find($id);

                $user->renewal = true;
                $user->save();
                return response()->json("Account renewal successful!", 200);
            } else {
                return response()->json("Account renewal failed!", 402);
            }
        } else {
            return response()->json("Invalid signature!", 422);
        }
    }
}
