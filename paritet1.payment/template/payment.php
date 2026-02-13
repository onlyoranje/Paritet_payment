<?php
IncludeModuleLangFile(__FILE__);
use Bitrix\Main\Config\Option;
use Bitrix\Sale\Payment;
use Bitrix\Main\Localization\Loc;


$post['prodUrl'] = $params['prodUrl'];
$post['token'] = $params['token'];
$post['orderId'] = $params['PB_PREFIX'] . '_' . $params['orderId'];
$post['salePlaceId'] = $params['salePlaceId'];
$post['salePointId'] = $params['salePointId'];
$post['bankProductId'] = $params['BANK_PRODUCT'];
$post['sum'] = $params['sum'];
$post['ownSum'] = $params['ownSum'];
$post['phoneNumber'] = strval($params['phoneNumber']);
$post['skipClaimVerification'] = $params['skipClaimVerification'];
$post['createClaimsByRelatedBankProducts'] = true;
$post['clientRedirectUrl'] = $params['url'].str_replace('#ORDER_ID#', $params['orderId'], $params['PB_CLIENT_REDIRECT']);
$post['claimStatusChangedCallbackUrl'] = $params['url'].str_replace('#PAY_SYSTEM_ID#', $params['PAYMENT_ID'], $params['PB_STATUS_REDIRECT']);
$post['products'] = $params['products'];

foreach ($post['products'] as $key => $products) {
    if ($params['PB_BELARUS_PRODUCT'] == 'Y') {
        $post['products'][$key]['MadeInBelarus'] = true;
    }
}

if ($params['PB_ALTERNATIVE_CLAIM'] == 'Y') {
    $post['showAlternativeClaimsToClients'] = true;
}

if ($params['PB_ALTERNATIVE_CLAIM'] == 'N') {
    $post['showAlternativeClaimsToClients'] = false;
}

$r = $post;
$r = json_encode($r, JSON_UNESCAPED_UNICODE);

$process = curl_init($post['prodUrl'] . 'SalePoints/' . $post['salePointId'] . '/Orders/CreateOrder');
curl_setopt($process, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Accept: text/plain',
    'Authorization: Bearer ' . $post['token'] . ''
));
curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($process, CURLOPT_RETURNTRANSFER, true);
curl_setopt($process, CURLOPT_POSTFIELDS, $r);
$result0 = curl_exec($process);
curl_close($process);
$arr = json_decode($result0, true);
$params['arr'] = $arr;

if ($params['PB_SMS'] == 'Y') {
$r1['sum'] = $params['sum'];
$r1['phoneNumber'] = $post['phoneNumber'];

$r1['qrId'] = $arr['result']['qrId'];

$r1 = json_encode($r1, JSON_UNESCAPED_UNICODE);
$process = curl_init($post['prodUrl'] . 'SalePoints/' . $post['salePointId'] . '/Notification/SendQrUrlToClient');
curl_setopt($process, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Accept: text/plain',
    'Authorization: Bearer ' . $post['token'] . ''
));
curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($process, CURLOPT_RETURNTRANSFER, true);
curl_setopt($process, CURLOPT_POSTFIELDS, $r1);
$result1 = curl_exec($process);
curl_close($process);
$arr1 = json_decode($result1, true);
   }
?>

<div class="paritet" style="text-align: left">
<?php if ($params['arr']['result']['urlToCreateClaim']) {?>
<a href="<?=$params['arr']['result']['urlToCreateClaim'];?>" target="_blank">
    <input name="" class='btn btn-default' type="button" value="<?=Loc::getMessage('PB_ORDER_BUTTON_NAME');?>">
    </a>
<?php }?>
<?php if ($params['arr']['responseException']['exceptionMessage']) {
    echo '<input name="" class="btn btn-default"  type="button" value="' . Loc::getMessage('PB_ERROR_MESSAGE_UNDEFIND') . '">';
}?>
<p><ol>
    <li><?= Loc::getMessage('PB_STEP1') ?></li>
    <li><?= Loc::getMessage('PB_STEP2') ?></li>
    <li><?= Loc::getMessage('PB_STEP3') ?></li>
    <li><?= Loc::getMessage('PB_STEP4') ?></li>
    <li><?= Loc::getMessage('PB_STEP5') ?></li>
</ol></p>
  <!--    <pre style="white-space: pre-wrap;">
            <?php  print_r($post);;?>
            <hr>
            <?php  print_r($_SERVER['HTTP_ORIGIN']);;?>
        </pre>-->
</div>
