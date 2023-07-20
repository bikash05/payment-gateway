<?php

namespace App\Http\Controllers;

use App\MicroServices\IdGenerator\PrefixIdGenerator;
use App\Models\Payment\ApiMaster;
use App\Models\Payment\CardDetail;
use App\Models\Payment\PaymentRequest;
use App\Models\Payment\WebhookPaymentData;
use App\Models\Workflow\WfWorkflow;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Razorpay\Api\Api;
use Illuminate\Support\Str;
use Razorpay\Api\Errors\SignatureVerificationError;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * | ----------------------------------------------------------------------------------
 * | Payment Module 
 * |-----------------------------------------------------------------------------------
 * | Created On - 17-07-2023
 * | Created By - Mrinal
 * | Created For - payment Gateway related Transaction and Payment Related operations
 */

class PaymentGatewayController extends Controller
{
    private $_refRazorpayId;
    private $_refRazorpayKey;

    public function __construct()
    {
        $this->_refRazorpayId  = getenv("RAZORPAY_KEY");
        $this->_refRazorpayKey = getenv('RAZORPAY_ID');
    }
    /**
     * | Verify the payment status 
     * | Use to check the actual paymetn from the server 
        | Serial No : 01 
        | Testing
     */
    public function verifyPaymentStatus(Request $request)
    {
        $rules = [
            'razorpayOrderId'   => 'required',
            'razorpayPaymentId' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors(), 'data' => []], 422);
        }

        try {
            return responseMsgs(true, "Payment is pending pleas check it later!", [], "", "01", responseTime(), "POST", $request->deviceId);

            # This is a different code 
            # Test code 
            $attributes     = null;
            $success        = false;
            $refRazorpayId  = $this->_refRazorpayKey;
            $refRazorpayKey = $this->_refRazorpayId;

            $api = new Api($refRazorpayKey, $refRazorpayId);
            $paymentId = $_POST['razorpayPaymentId'];
            $payment = $api->payment->fetch($paymentId);
            if ($payment->status === 'captured') {
                return responseMsgs(true, "Payment status!", $payment, "", "04", responseTime(), "POST", $request->deviceId);
            } else {
                return responseMsgs(false, "Payment status!", $payment, "", "04", responseTime(), "POST", $request->deviceId);
            }


            # This Process is different 
            # Variable and model declaration 
            $attributes     = null;
            $success        = false;
            $refRazorpayId  = $this->_refRazorpayKey;
            $refRazorpayKey = $this->_refRazorpayId;

            # Verify the existence of the razerpay Id
            try {
                $api = new Api($refRazorpayId, $refRazorpayKey);
                $attributes = [
                    'razorpay_order_id'     => $request->razorpayOrderId,
                    'razorpay_payment_id'   => $request->razorpayPaymentId,
                    'razorpay_signature'    => $request->razorpaySignature
                ];
                $api->utility->verifyPaymentSignature($attributes);
                $success = true;
            } catch (SignatureVerificationError $exception) {
                $success = false;
                $messsage = $exception->getMessage();
            }
            if ($success === true) {
                # Check the webhook transaction data
                $messsage = "Payment Successfully done!";
                return responseMsgs(true, $messsage, [], "", "01", responseTime(), "POST", $request->deviceId);
            } else {
                # Update database with error data
                return responseMsgs(false, $messsage, [], "", "01", responseTime(), "POST", $request->deviceId);
            }
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "01", responseTime(), "POST", $request->deviceId);
        }
    }

    /**
     * | Razorpay Payment generating order id / Saving in database 
     * | Operation : generating the order id according to request data, using the razorpay API 
        | Serial No : 02
        | Department Id will be replaced by module Id 
        | Razorpay key will be collected from database
     */
    public function saveGenerateOrderid(Request $request)
    {
        $rules = [
            'id'            => 'required|integer',
            'amount'        => 'required|',
            'workflowId'    => 'required|',
            'ulbId'         => 'nullable',
            'auth'          => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors(), 'data' => []], 422);
        }

        try {
            $mWfWorkflow    = new WfWorkflow();
            $saveRequestObj = new PaymentRequest();
            $wfReq = new Request([
                'id' => $request->workflowId
            ]);

            $user               = (object)$request->auth;
            $userId             = $user->id ?? $request->ghostUserId;
            $workflowDetails    = $mWfWorkflow->listbyId($wfReq);
            $ulbId              = $workflowDetails->ulb_id ?? $request->ulbId;                      // ulbId
            $refRazorpayId      = $this->_refRazorpayKey;
            $refRazorpayKey     = $this->_refRazorpayId;
            $mReciptId          = Str::random(15);                                                  // Static recipt ID

            if (!$ulbId) {
                throw new Exception("Ulb details not found!");
            }
            $mApi = new Api($refRazorpayId, $refRazorpayKey);
            $mOrder = $mApi->order->create(array(
                'receipt'           => $mReciptId,
                'amount'            => $request->all()['amount'] * 100,
                'currency'          => 'INR',                                                       // Static
                'payment_capture'   => 1                                                            // Static
            ));

            $Returndata = [
                'orderId'       => $mOrder['id'],
                'amount'        => $request->all()['amount'],
                'currency'      => 'INR',                                                           // Static
                'userId'        => $userId,
                'ulbId'         => $ulbId,
                'workflowId'    => $request->workflowId,
                'applicationId' => $request->id,
                'departmentId'  => $request->departmentId,
                'propType'      => $request->propType
            ];
            $saveRequestObj->saveRazorpayRequest($userId, $ulbId, $Returndata['orderId'], $request);
            return responseMsgs(true, "OrderId Generated!", $Returndata, "", "04", responseTime(), "POST", $request->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "04", responseTime(), "POST", $request->deviceId);
        }
    }

    /**
     * | Razorpay Payment Gateway END
     * | Collecting the data provided by the webhook in database
     * | @param requet request from the frontend
     * | @param error collecting the operation error
        | Serial No : 03   
        | Working
     */
    public function gettingWebhookDetails(Request $request)
    {
        try {
            # creating json of webhook data
            $paymentId = $request->payload['payment']['entity']['id'];
            Storage::disk('public')->put($paymentId . '.json', json_encode($request->all()));

            if (!empty($paymentId)) {
                $mWebhookDetails = $this->collectWebhookDetails($request);
                return $mWebhookDetails;
            }
            return responseMsgs(false, "WEBHOOK DATA NOT ACCUIRED!", [], "", "04", responseTime(), "POST", $request->deviceId);
        } catch (Exception $error) {
            return responseMsgs(false, "OPERATIONAL ERROR!", $error->getMessage(), [], "", "04", responseTime(), "POST", $request->deviceId);
        }
    }

    /**
     * | Integration of the webhook 
     * | Operation : this function url is consumed by the webhook and the detail of the payment is collected in request 
     *               then the storage -> generating pdf -> generating json ->save -> hitting url for watsapp message.
     * | Rating : 5
        | Serial No : 03:01
        | Flag : department Id will be replaced 
        | Checking of the payment is success (keys:amount,orderid,departmentid,status) / razorpay verification 
     */
    public function collectWebhookDetails($request)
    {
        try {
            # Variable Defining Section
            $mApiMaster     = new ApiMaster();
            $mCardDetail    = new CardDetail();
            $webhookData    = new WebhookPaymentData();

            $webhookEntity  = $request->payload['payment']['entity'];
            $contains       = json_encode($request->contains);
            $notes          = json_encode($webhookEntity['notes']);
            $depatmentId    = $webhookEntity['notes']['departmentId'];  // ModuleId
            $status         = $webhookEntity['status'];
            $captured       = $webhookEntity['captured'];
            $aCard          = $webhookEntity['card_id'];
            $amount         = $webhookEntity['amount'];
            $arrayInAquirer = $webhookEntity['acquirer_data'];

            $actulaAmount           = $amount / 100;
            $firstKey               = array_key_first($arrayInAquirer);
            $actualTransactionNo    = $this->generatingTransactionId($webhookEntity['notes']['ulbId']);

            # Save card details 
            if (!is_null($aCard)) {
                $webhookCardDetails = $webhookEntity['card'];
                $mCardDetail->saveCardDetails($webhookCardDetails);
            }

            # Data to be stored in webhook table
            $refWebhookDetails = $webhookData->getWebhookRecord($request, $captured, $webhookEntity, $status)->first();
            if (is_null($refWebhookDetails)) {
                $webhookData = $webhookData->saveWebhookData($request, $captured, $actulaAmount, $status, $notes, $firstKey, $contains, $actualTransactionNo, $webhookEntity);
            }
            # data transfer to the respective module's database 
            $transfer = [
                'paymentMode'   => $webhookData->payment_method,
                'id'            => $webhookEntity['notes']['applicationId'],
                'amount'        => $actulaAmount,
                'workflowId'    => $webhookData->workflow_id,
                'transactionNo' => $actualTransactionNo,
                'userId'        => $webhookData->user_id,
                'ulbId'         => $webhookData->ulb_id,
                'departmentId'  => $webhookData->department_id,         //ModuleId
                'orderId'       => $webhookData->payment_order_id,
                'paymentId'     => $webhookData->payment_id,
                'tranDate'      => $request->created_at,
                'gatewayType'   => 1,                                   // Razorpay Id ??
            ];

            # conditionaly upadting the request data
            if ($status == 'captured' && $captured == 1) {
                PaymentRequest::where('razorpay_order_id', $webhookEntity['order_id'])
                    ->update(['payment_status' => 1]);

                # calling function for the modules                  
                switch ($depatmentId) {
                    case ('1'):                                                             //(Property)
                        $refpropertyType = $webhookEntity['notes']['workflowId'];
                        if ($refpropertyType == 0) {
                            // $objHoldingTaxController = new HoldingTaxController($this->_safRepo);
                            // $transfer = new ReqPayment($transfer);
                            // $objHoldingTaxController->paymentHolding($transfer);
                        } else {                                                            //(SAF Payment)
                            // $obj = new ActiveSafController($this->_safRepo);
                            // $transfer = new ReqPayment($transfer);
                            // $obj->paymentSaf($transfer);
                        }
                        break;
                    case ('2'):                                                             //(Water)
                        // $objWater = new WaterNewConnection();
                        // $objWater->razorPayResponse($transfer);
                        break;
                    case ('3'):                                                             //(Trade)
                        // $objTrade = new TradeCitizen();
                        // $objTrade->razorPayResponse($transfer);
                        break;
                    case ('5'):                                                             //(Advertisment) 
                        $adv = 76;                                                          // Static
                        $api = $mApiMaster->getApiEndpoint($adv);
                        Http::withHeaders([])
                            ->post("$api->end_point", $transfer);
                        break;
                    case ('9'):                                                             //(Pet Registration)
                        $pet = 461;                                                         // Static
                        $petApi = $mApiMaster->getApiEndpoint($pet);
                        Http::withHeaders([])
                            ->post("$petApi->end_point", $transfer);
                        break;
                }
            }
            return responseMsgs(true, "Webhook Data Collected!", $request->event, "", "04", responseTime(), "POST", $request);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "04", responseTime(), "POST", $request->deviceId);
        }
    }

    /**
     * | Generating Application ID 
     * | @param request
     * | Operation : this function generate a random and unique transactionID
        | Serial No : 03.01.01
     */
    public function generatingTransactionId($ulbId)
    {
        $tranParamId    = Config::get("workflow-constants.PARAM_IDS");
        $idGeneration   = new PrefixIdGenerator($tranParamId['TRN'], $ulbId);
        $transactionNo  = $idGeneration->generate();
        $transactionNo  = str_replace('/', '-', $transactionNo);
        return $transactionNo;
    }
}
