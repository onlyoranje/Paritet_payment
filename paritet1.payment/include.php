<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Loader;

/**
 * Обработчик статусов заявок Паритетбанка.
 *
 * Регистрируется как агент Битрикс при установке модуля (install/index.php).
 * Ищет неоплаченные заказы данной платёжной системы и опрашивает API банка
 * методом GET /SalePoints/{salePointId}/Claims/by-order/{orderId}.
 * Если заявка получила статус, выбранный в настройках как "оплачено",
 * заказ помечается оплаченным (CSaleOrder::ПayOrder).
 *
 * @return string Строка вызова агента (для повторного запуска) либо false.
 */
function paritetCheckOrderStatuses()
{
    $moduleId = 'paritet1.payment';

    $prodUrl     = COption::GetOptionString($moduleId, 'OPTION_PROD_URL');
    $login       = COption::GetOptionString($moduleId, 'OPTION_LOGIN');
    $password    = COption::GetOptionString($moduleId, 'OPTION_PASSWORD');
    $salePointId = COption::GetOptionString($moduleId, 'OPTION_SALE_POINT_ID');
    $paidStatus  = COption::GetOptionString($moduleId, 'OPTION_PAID_STATUS_ID');
    $orderPrefix = COption::GetOptionString($moduleId, 'OPTION_ORDER_PREFIX');

    // Модуль ещё не настроен — возвращаем строку функции, чтобы агент остался в расписании.
    if (empty($prodUrl) || empty($login) || empty($password) || empty($salePointId) || empty($paidStatus)) {
        return 'paritetCheckOrderStatuses();';
    }

    if (!Loader::includeModule('sale')) {
        return 'paritetCheckOrderStatuses();';
    }

    $token = paritetGetOAuthToken($prodUrl, $login, $password);
    if (!$token) {
        return false;
    }

    $psId = paritetPaymentSystemId();
    if (!$psId) {
        return false;
    }

    $rows = array();
    $oOrder = CSaleOrder::GetList(
        array('ID' => 'ASC'),
        array('PAYED' => 'N', 'PAY_SYSTEM_ID' => $psId),
        false,
        false,
        array('ID', 'ACCOUNT_NUMBER', 'PAY_SYSTEM_ID')
    );
    while ($order = $oOrder->Fetch()) {
        $rows[] = $order;
    }

    foreach ($rows as $order) {
        $bankOrderId = ($orderPrefix ? $orderPrefix . '_' : '') . $order['ACCOUNT_NUMBER'];

        $statusId = paritetGetClaimStatus($prodUrl, $token, $salePointId, $bankOrderId);
        if ($statusId !== false && (string)$statusId === (string)$paidStatus) {
            \CSaleOrder::PayOrder($order['ID'], 'Y');
        }
    }

    return 'paritetCheckOrderStatuses();';
}

/**
 * Получение access-токена по логину/паролю.
 *
 * @param string $prodUrl Базовый URL API (с завершающим слэшем).
 * @param string $login   Логин магазина.
 * @param string $password Пароль.
 * @return string|false
 */
function paritetGetOAuthToken($prodUrl, $login, $password)
{
    $handle = curl_init($prodUrl . 'OAuth/token');
    curl_setopt($handle, CURLOPT_POST, true);
    curl_setopt($handle, CURLOPT_POSTFIELDS, array(
        'username' => $login,
        'password' => $password,
    ));
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($handle);
    curl_close($handle);

    $response = is_string($response) ? json_decode($response, true) : array();
    return (is_array($response) && !empty($response['access_token'])) ? $response['access_token'] : false;
}

/**
 * Получает id статуса заявки банковской заявки по заказу клиента.
 * GET /AccountesPoints/{phone}/Accountes Points/Claims/by-order/{orderId}
 *
 * @return string|false Ид статуса или false при ошибке.
 */
function paritetGetClaimStatus($prodUrl, $token, $salePointId, $orderId)
{
    $url = $prodUrl . 'SalePoints/' . $salePointId . '/Claims/by-order/' . rawurlencode($orderId);

    $handle = curl_init($url);
    curl_setopt($handle, CURLOPT_HTTPHEADER, array(
        'Accept: application/json; charset=utf-8',
        'Authorization: Bearer ' . $token,
    ));
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($handle);
    curl_close($handle);

    $response = is_string($response) ? json_decode($response, true) : array();
    if (!is_array($response) || empty($response['result']['statusId'])) {
        return false;
    }
    return $response['result']['statusId'];
}

/**
 * Определяет ID платёжной системы Паритетбанка в базе Битрикса.
 * NOTE: если используется несколько ПС модуля — код ниже требует уточнения.
 *
 * @return int|false
 */
function paritetPaymentSystemId()
{
    if (!Loader::isModuleLoaded('sale')) {
        Loader::includeModule('sale');
    }
    $rs = \CSalePaySystem::GetList(
        array('SORT' => 'ASC'),
        array('ACTIVE' => 'Y', 'NAME' => 'Кредит/рассрочка от Паритета')
    );
    if ($row = $rs->Fetch()) {
        return (int)$row['ID'];
    }
    return false;
}

/** Debug-пункт меню «Сборка заказов» удалён — он не относится к платёжному модулю. */