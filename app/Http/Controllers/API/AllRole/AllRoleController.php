<?php

namespace App\Http\Controllers\API\AllRole;

use App\Http\Controllers\Controller;
use App\Mail\RenewalShopOwnerAccount;
use App\Models\Category;
use App\Models\Shop;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Mail;

class AllRoleController extends Controller
{
    /**
     * Get all categories.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCategories()
    {
        $categories = Category::all();

        return response()->json([
            'categories' => $categories
        ], 200);
    }

    public function vnpayPayment()
    {
//        Log::error('An error occurred while processing the request.');

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
        $orderId = $inputData['vnp_TxnRef']; // form orderId-userId
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
                                $user->renewal = true;
                                $user->save();
                                Mail::to($user->email)->send(new RenewalShopOwnerAccount($user));
                            }
                            else {
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
        return response()->json($returnData);
    }

    public function vnpayReturn()
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
                return response()->json([
                    'message' => "Account renewal successful!",
                    'data' => $secureHash
                ], 200);
            } else {
                return response()->json([
                    'message' => "Account renewal failed!",
                ], 402);
            }
        } else {
            return response()->json([
                'message' => "Invalid signature!",
            ], 422);
        }
    }

    /**
     * Get all shop owners.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllShopOwner()
    {
        try {
            $shopOwners = Shop::all();

            if ($shopOwners->isEmpty()) {
                throw new \Exception('No shop owners found.');
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }

        return response()->json(['shop_owners' => $shopOwners], 200);
    }

}
