<?php
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;

IncludeModuleLangFile(__FILE__);

/**
 * Обработчик платежа Паритетбанка.
 * Готовит тело запроса строго по контракту Vendor API v1 (CreateOrderRequest),
 * выполняет запрос к API и сохраняет результат в $params['arr'].
 * Весь вывод HTML вынесен в template/payment.php — здесь только логика.
 */

// Базовый URL сайта (со страницей оплаты). Битрикс может передать его с
// завершающим слэшем — нормализуем, чтобы не получить "//" при склейке.
$baseUrl = rtrim((string)$params['url'], '/');

$post = array(
    // MANDATORY поля по контракту CreateOrderRequest
    'orderId'        => $params['PB_PREFIX'] . '_' . $params['orderId'],
    'bankProductId'  => $params['BANK_PRODUCT'],
    // создание заявок по связанным банковским продуктам
    'createClaimsByRelatedBankProducts' => true,
    // редирект клиента после оформления заявки
    'clientRedirectUrl' => $baseUrl . str_replace('#ORDER_ID#', $params['orderId'], $params['PB_CLIENT_REDIRECT']),
    // URL-callback для получения уведомлений об изменении статуса заявки
    'claimStatusChangedCallbackUrl' => $baseUrl . str_replace('#PAY_SYSTEM_ID#', $params['PAYMENT_ID'], $params['PB_STATUS_REDIRECT']),
);

// Опциональные поля — только если указаны (в контракте поле 'sum' отсутствует,
// сумма заказа считается по товарам и первоначальному взносу)
if (!empty($params['ownSum'])) {
    $post['ownSum'] = $params['ownSum'];
}
if (isset($params['phoneNumber']) && (string)$params['phoneNumber'] !== '') {
    $post['phoneNumber'] = (string)$params['phoneNumber'];
}
if (isset($params['skipClaimVerification'])) {
    $post['skipClaimVerification'] = (bool)$params['skipClaimVerification'];
}

// Продукты заказа — только если переданы корректным массивом
if (isset($params['products']) && is_array($params['products'])) {
    $post['products'] = $params['products'];
    if ($params['PB_BELARUS_PRODUCT'] == 'Y') {
        foreach ($post['products'] as $key => $product) {
            // В контракте поле называется madeInBelarus (camelCase)
            $post['products'][$key]['madeInBelarus'] = true;
        }
    }
}

// Альтернативные заявки
if ($params['PB_ALTERNATIVE_CLAIM'] == 'Y') {
    $post['showAlternativeClaimsToClients'] = true;
} elseif ($params['PB_ALTERNATIVE_CLAIM'] == 'N') {
    $post['showAlternativeClaimsToClients'] = false;
}

$postJson = json_encode($post, JSON_UNESCAPED_UNICODE);

$process = curl_init($params['prodUrl'] . 'SalePoints/' . $params['salePointId'] . '/Orders/CreateOrder');
curl_setopt($process, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Accept: text/plain',
    'Authorization: Bearer ' . $params['token']
));
curl_setopt($process, CURLOPT_RETURNTRANSFER, true);
curl_setopt($process, CURLOPT_POSTFIELDS, $postJson);
$result0 = curl_exec($process);
curl_close($process);

$arr = is_string($result0) ? json_decode($result0, true) : array();
if (!is_array($arr)) {
    $arr = array();
}
$params['order'] = $arr;
$params['post']  = $post;

// СМС клиенту — только когда банк вернул qrId (по контракту SendQrUrlToClientRequest:
// phoneNumber + qrId)
if ($params['PB_SMS'] == 'Y'
    && !empty($arr['result']['qr']['qrId'])
    && !empty($post['phoneNumber'])) {

    $smsBody = json_encode(array(
        'phoneNumber' => $post['phoneNumber'],
        'qrId'        => $arr['result']['qr']['qrId'],
    ), JSON_UNESCAPED_UNICODE);

    $process = curl_init($params['prodUrl'] . 'SalePoints/' . $params['salePointId'] . '/Notification/SendQrUrlToClient');
    curl_setopt($process, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Accept: text/plain',
        'Authorization: Bearer ' . $params['token']
    ));
    curl_setopt($process, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($process, CURLOPT_POSTFIELDS, $smsBody);
    $result1 = curl_exec($process);
    curl_close($process);
    $params['sms'] = is_string($result1) ? json_decode($result1, true) : array();
}
?>
