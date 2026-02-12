<?php

namespace Sale\Handlers\PaySystem;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Request;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\PaySystem;
use Bitrix\Sale\Payment;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\PaySystem\IRefund;
use Bitrix\Main\Diag\Debug;
use Bitrix\Sale\Order;



class paritet1_paymentHandler extends PaySystem\ServiceHandler implements IRefund {
     var $MODULE_ID = 'paritet1.payment';


    public function initiatePay(Payment $payment, Request $request = null) {
        $params = array();

        Loader::includeModule($this->MODULE_ID);


        $is_paid_sum = 0;


        $params['token'] = $this->getToken();
        $params['salePlaceId'] = \COption::GetOptionString($this->MODULE_ID, 'OPTION_STORE_ID');
        $params['salePointId'] = \COption::GetOptionString($this->MODULE_ID, 'OPTION_SALE_POINT_ID');
        $params['prodUrl'] = \COption::GetOptionString($this->MODULE_ID, 'OPTION_PROD_URL');
;


        $Order = Order::load($payment->getOrderId());
        $propertyCollection = $Order->getPropertyCollection();

        $phoneProp = $propertyCollection->getPhone(); 
        if (!$phoneProp) {
            foreach ($propertyCollection as $prop) {
                if ($prop->getField('CODE') === 'PHONE') {
                    $phoneProp = $prop;
                    break;
                }
            }

        }

        $paymentCollection = $Order->getPaymentCollection();
        foreach ($paymentCollection as $payment) {
            $psName = $payment->getPaymentSystemName(); 
            $sum = $payment->getSum(); 
            $isPaid = $payment->isPaid(); 
            $isReturned = $payment->isReturn(); 
            $ps = $payment->getPaySystem(); 
            $psID = $payment->getPaymentSystemId(); 
            $isInnerPs = $payment->isInner(); 

            if ($isPaid) {
                $is_paid_sum += $sum;
            }
            $total_sum += $sum;
            $arr['k_oplate'] = $KOPLATE;
        }


        $params['orderId'] = $payment->getOrderId();

        $Basket = $Order->getBasket();
        $basketItems = $Basket->getBasketItems();

        $positions = [];
        $lastIndex = 0;
        foreach ($basketItems as $key => $basketItem) {
            $params['products'][$key]['productId'] = $key;
            $params['products'][$key]['productId'] = "'" . $basketItem->getProductId() . "'";
            $params['products'][$key]['vendorCode'] = 'PRODUCT_' . $basketItem->getProductId();
            $params['products'][$key]['name'] = $basketItem->getField('NAME');
            $params['products'][$key]['price'] = $basketItem->getPrice();
            $params['products'][$key]['quantity'] = $basketItem->getQuantity();

        }

        $params['sum'] = $total_sum;
        $params['ownSum'] = $is_paid_sum;
        $params['phoneNumber'] = '+' . preg_replace('/\D+/', '', $phoneProp->getValue());
        $params['skipClaimVerification'] = true;


       

        $this->setExtraParams($params);
        return $this->showTemplate($payment, "payment");

    }
    public function getToken() {

        $post = array('username' => \COption::GetOptionString($this->MODULE_ID, 'OPTION_LOGIN'), 'password' => \COption::GetOptionString($this->MODULE_ID, 'OPTION_PASSWORD'));
        $ch = curl_init(\COption::GetOptionString($this->MODULE_ID, 'OPTION_PROD_URL').'OAuth/token');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $response = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($response, true);
        $token = $response['access_token'] ;


        return $token;

    }
   
    public function getCurrencyList() {

        return ['BYN'];
    }
    public function refund(Payment $payment, $refundableSum) {
        $result = new PaySystem\ServiceResult();

        $response = $this->sendRefundRequest($payment, $refundableSum);

        if ($response['status'] === 1) {
            $result->setOperationType(PaySystem\ServiceResult::MONEY_LEAVING);
        }

        return $result;
    }
    public function getPaymentIdFromRequest(Request $request) {
    $orderId = explode("_", $request->get('orderId') );
	$orderId = array_pop($orderId);
    $order = Order::load($orderId);
    if ($order) {
    $paymentCollection = $order->getPaymentCollection();
    
    foreach ($paymentCollection as $payment) {
       
        
        if ($request->get('PAY_SYSTEM_ID') == $payment->getPaymentSystemId())
            {
return $payment->getId();
            }
      
    }
}
        
    }
    public static function getIndicativeFields()

{


    return array('PAY_SYSTEM');

}
static function isMyResponseExtended(Request $request, $paySystemId)

{
    if ($request->get('PAY_SYSTEM')!=='PB') return false;
   
    return true;
  ;

}

    public function processRequest(Payment $payment, Request $request) {
        global $APPLICATION;
        $result = new PaySystem\ServiceResult();
        $option_status_pay  = $this->getBusinessValue($payment, 'PB_STATUS_PAY');
       
        $bank_status_pay = $request->get('statusId');
        
        
        if ($option_status_pay==$bank_status_pay) {
              $orderId = explode("_", $request->get('orderId') );
	$orderId = array_pop($orderId);
    $order = Order::load($orderId);
    if ($order) {
    $paymentCollection = $order->getPaymentCollection();
    
    foreach ($paymentCollection as $payment) {
       
      
        if ($request->get('PAY_SYSTEM_ID') == $payment->getPaymentSystemId())
            { 
                
     
$payment->setPaid("Y");
 $order->save();
            }
       
    }
}
        } 
        else 
            {

        }
        



        return $result;
    }
   public function getStatusPay(){

     
   $arr_status = unserialize(\COption::GetOptionString($this->MODULE_ID,  'OPTION_STATUSES'));
    return $arr_status;
    }

    private function log($data) {
        return file_put_contents(
            __DIR__ . '/PP-' . date('d-m-Y-H') . '-log.json',
            json_encode($data, JSON_PRETTY_PRINT, JSON_UNESCAPED_UNICODE),
            FILE_APPEND
        );
    }
}

?>
<?php

if (!empty($paySystemService)) {
    $arPaySysAction = $paySystemService->getFieldsValues();
    if ($paySystemService->getField('NEW_WINDOW') === 'N' || $paySystemService->getField('ID') == PaySystem\Manager::getInnerPaySystemId()) {
        $initResult = $paySystemService->initiatePay($payment, null, PaySystem\BaseServiceHandler::STRING);
        if ($initResult->isSuccess()) {
            $arPaySysAction['BUFFERED_OUTPUT'] = $initResult->getTemplate();
        }
       
        else {
            $arPaySysAction["ERROR"] = $initResult->getErrorMessages();
        }

    }
}



?>