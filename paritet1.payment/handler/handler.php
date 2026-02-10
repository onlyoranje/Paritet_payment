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
//use Sale\Handlers\PaySystem\COption;


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

        $phoneProp = $propertyCollection->getPhone(); // Специальный метод для системных свойств
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
            $psName = $payment->getPaymentSystemName(); // название платежной системы
            $sum = $payment->getSum(); // сумма к оплате
            $isPaid = $payment->isPaid(); // true, если оплачена
            $isReturned = $payment->isReturn(); // true, если возвращена
            $ps = $payment->getPaySystem(); // платежная система (объект Sale\PaySystem\Service)
            $psID = $payment->getPaymentSystemId(); // ID платежной системы
            $isInnerPs = $payment->isInner(); // true, если это оплата с внутреннего счета

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


        /*$r = $params;
        $r = json_encode($r, JSON_UNESCAPED_UNICODE);*/

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
    // Список валют, поддерживаемых обработчиком
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

        return $request->get('orderNumber');
    }
    public function processRequest(Payment $payment, Request $request) {
        $result = new PaySystem\ServiceResult();
        $action = $request->get('action');
         echo 111;
        if ($action === 'demandPayment') {
            return $this->processDemandPaymentAction($payment, $request);
        } else if ($action === 'consumerStatus') {
            return $this->processConsumerStatusAction($payment, $request);
        } else if ($action === 'consumerReady') {
            return $this->processConsumerReadyAction($payment, $request);
        } else if ($action === 'paymentStatusAwait') {
            return $this->processPaymentStatusAwaitAction($payment, $request);
        } else if ($action === 'paymentStatus') {
            return $this->processPaymentStatusAction($payment, $request);
        } else if ($action === 'consumerStatusAwait') {
            return $this->processConsumerStatusAwaitAction($payment, $request);
        }

        return 11;
        return $result;
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
        // получаем форму оплаты из обработчика
        else {
            $arPaySysAction["ERROR"] = $initResult->getErrorMessages();
        }

    }
}



?>