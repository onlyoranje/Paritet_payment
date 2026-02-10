<?php

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale;

require __DIR__ . '/config.php';

$moduleID = $PB_CONFIG['MODULE_ID'];

Loader::includeModule('sale');
Loader::includeModule('currency');
Loader::includeModule($moduleID);

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/options.php");
IncludeModuleLangFile(__FILE__);

if ($REQUEST_METHOD == 'POST' && strlen($Update . $Apply) > 0 && check_bitrix_sessid()) {

    COption::SetOptionString($moduleID, "OPTION_LOGIN", $_POST['OPTION_LOGIN']);
    COption::SetOptionString($moduleID, "OPTION_PASSWORD", $_POST['OPTION_PASSWORD']);
    COption::SetOptionString($moduleID, "OPTION_STORE_ID", $_POST['OPTION_STORE_ID']);
    COption::SetOptionString($moduleID, "OPTION_SALE_POINT_ID", $_POST['OPTION_SALE_POINT_ID']);
    COption::SetOptionString($moduleID, "OPTION_TOKEN", $_POST['OPTION_TOKEN']);
    COption::SetOptionString($moduleID, "OPTION_PROD_URL", $_POST['OPTION_PROD_URL']);
    COption::SetOptionString($moduleID, "OPTION_BANK_PRODUCT_ID", serialize($_POST['OPTION_BANK_PRODUCT_ID']));
}
$current_settings = array(
    'BANK_NAME' => $PB_CONFIG['BANK_NAME'],
    'MODULE_VERSION' => $PB_CONFIG['MODULE_VERSION'],

    'OPTION_LOGIN' => COption::GetOptionString($moduleID, 'OPTION_LOGIN'),
    'OPTION_PASSWORD' => COption::GetOptionString($moduleID, 'OPTION_PASSWORD'),
    'OPTION_STORE_ID' => COption::GetOptionString($moduleID, 'OPTION_STORE_ID'),
    'OPTION_SALE_POINT_ID' => COption::GetOptionString($moduleID, 'OPTION_SALE_POINT_ID'),
    'OPTION_TOKEN' => COption::GetOptionString($moduleID, 'OPTION_TOKEN'),
    'OPTION_PROD_URL' => COption::GetOptionString($moduleID, 'OPTION_PROD_URL'),
    'OPTION_BANK_PRODUCT_ID' => unserialize(COption::GetOptionString($moduleID, 'OPTION_BANK_PRODUCT_ID')),
);
$tabControl = new CAdminTabControl("tabControl", array(
    array("DIV" => "edit1", "TAB" => Loc::getMessage('PB_TAB_NAME'), "ICON" => "blog_settings", "TITLE" => Loc::getMessage('PB_TAB_TITLE')),
));
$tabControl->Begin();?>
    <form method="POST" action="<?php echo $APPLICATION->GetCurPage(); ?>?mid=<?=htmlspecialcharsbx($mid);?>&lang=<?=LANGUAGE_ID;?>">
        <?=bitrix_sessid_post();?>
            <?php $tabControl->BeginNextTab();?>
            <?php

if ($current_settings['OPTION_LOGIN'] and $current_settings['OPTION_PASSWORD']) {
    $post = array('username' => $current_settings['OPTION_LOGIN'], 'password' => $current_settings['OPTION_PASSWORD']);
    $ch = curl_init($PB_CONFIG['PROD_URL'] . 'OAuth/token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    $response = curl_exec($ch);
    curl_close($ch);
    $response = json_decode($response, true);
    $token = $response['access_token'];
}
?>
                <tr class="heading">
                    <td colspan="2">
                        <?=Loc::getMessage('PB_STRING_MODULE_INFO');?>:</td>
                </tr>
                <tr>
                    <td width="50%">
                        <?=Loc::getMessage('PB_STRING_BANK');?>
                    </td>
                    <td width="50%"><span><?=$current_settings['BANK_NAME'];?></span></td>
                </tr>
                <tr>
                    <td width="50%">
                        <?=Loc::getMessage('PB_STRING_MODULE_VERSION');?>:</td>
                    <td width="50%"><span><?=$current_settings['MODULE_VERSION'];?></span></td>
                </tr>
                <tr class="heading">
                    <td colspan="2">
                        <?=Loc::getMessage('PB_TAB_TITLE');?>:</td>
                </tr>
                <tr class="extra-settings active">
                    <td width="50%" class="adm-detail-content-cell-l">
                        <?=Loc::getMessage('PB_PROD_URL');?>
                    </td>
                    <td width="50%" class="sberbank-input-top adm-detail-content-cell-r">
                        <input type="text" name="OPTION_PROD_URL" value="<?=$current_settings['OPTION_PROD_URL'];?>">
                    </td>
                </tr>
                <tr class="extra-settings active">
                    <td width="50%" class="adm-detail-content-cell-l">
                        <?=Loc::getMessage('PB_LOGIN');?>
                    </td>
                    <td width="50%" class="sberbank-input-top adm-detail-content-cell-r">
                        <input type="text" name="OPTION_LOGIN" value="<?=$current_settings['OPTION_LOGIN'];?>">
                    </td>
                </tr>
                <tr class="extra-settings active">
                    <td width="50%" class="adm-detail-content-cell-l">
                        <?=Loc::getMessage('PB_PASSWORD');?>
                    </td>
                    <td width="50%" class="sberbank-input-top adm-detail-content-cell-r">
                        <input type="text" name="OPTION_PASSWORD" value="<?=$current_settings['OPTION_PASSWORD'];?>">
                    </td>
                </tr>
                <?php if ($token) {?>
                <?php if (!$current_settings['OPTION_STORE_ID']) {
    $process = curl_init($PB_CONFIG['PROD_URL'] . 'SalePlaces/CheckSalePlaceStatus');
    curl_setopt($process, CURLOPT_HTTPHEADER, array(
        'Accept: application/json; charset=utf-8',
        'Authorization: Bearer ' . $token . ''
    ));
    curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($process);
    curl_close($process);
    $arr = json_decode($result, true);

    $current_settings['OPTION_STORE_ID'] = $arr['result']['salePlaceId'];
}?>
                <tr class="extra-settings active">
                    <td width="50%" class="adm-detail-content-cell-l">
                        <?=Loc::getMessage('PB_STORE_ID');?>
                    </td>
                    <td width="50%" class="sberbank-input-top adm-detail-content-cell-r">
                        <input type="text" name="OPTION_STORE_ID" value="<?=$current_settings['OPTION_STORE_ID'];?>">
                    </td>
                </tr>
                <?php

    $process = curl_init($PB_CONFIG['PROD_URL'] . 'SalePlaces/GetAvailableSalePoints');
    curl_setopt($process, CURLOPT_HTTPHEADER, array(
        'Accept: application/json; charset=utf-8',
        'Authorization: Bearer ' . $token . ''
    ));
    curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($process);
    curl_close($process);
    $arr = json_decode($result, true);

    foreach ($arr['result'] as $point) {
        ?>
                    <tr>
                        <td width="50%" class="adm-detail-content-cell-l">
                            <input id="к" name="OPTION_SALE_POINT_ID" type="radio" value="<?=$point['salePointId'];?>" <?=($point['salePointId'] == $current_settings['OPTION_SALE_POINT_ID']) ? 'checked' : '';?>>
                        </td>
                        <td width="50%" class="sberbank-input-top adm-detail-content-cell-r">
                            <?=$point['salePointName'];?>
                        </td>
                    </tr>
                    <?php }?>
                    <tr class="heading">
                        <td colspan="2">
                            <?=Loc::getMessage('PB_BANK_PRODUCT');?>
                        </td>
                    </tr>
                    <?php if ($current_settings['OPTION_SALE_POINT_ID']) {
        $process = curl_init($PB_CONFIG['PROD_URL'] . 'SalePoints/' . $current_settings['OPTION_SALE_POINT_ID'] . '/BankProducts/GetBankProducts');
        curl_setopt($process, CURLOPT_HTTPHEADER, array(
            'Accept: application/json; charset=utf-8',
            'Authorization: Bearer ' . $token . ''
        ));
        curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($process);
        curl_close($process);
        $arr = json_decode($result, true);
        asort($arr['result']);
        foreach ($arr['result'] as $key => $BankProduct) {
            ?>
                    <tr>
                        <td width="50%" class="adm-detail-content-cell-l">
                            <input id="BankProduct_<?=$key;?>" name="OPTION_BANK_PRODUCT_ID[<?=$BankProduct['id'];?>]" type="checkbox" value="<?=$BankProduct['name'];?>" <?=(in_array($BankProduct['id'], array_keys($current_settings['OPTION_BANK_PRODUCT_ID']))) ? 'checked' : '';?>>
                        </td>
                        <td width="50%" class="sberbank-input-top adm-detail-content-cell-r">
                            <?=$BankProduct['name'];?> (
                                <?=$BankProduct['interestRate'];?>% /
                                    <?=$BankProduct['termInMonth'];?> мес
                                        <?=($BankProduct['useForBelarusProducts'] == 1) ? ' / &#127463;&#127486;' : '';?>)
                        </td>
                    </tr>
                    <?php }?>
                    <?php } else {?>
                    <tr>
                        <td width="100%" class="sberbank-input-top adm-detail-content-cell-r">
                            <?=Loc::getMessage('PB_NO_SALE_POINT_ID');?>
                        </td>
                    </tr>
                    <?php }?>
                    <?php }?>

                    <!--<tr>
                        <pre style="width: 320px">        <?php print_r($current_settings);?>
                        </pre>
                        </tr>-->
                    <?php $tabControl->BeginNextTab();?>
                    <?php $tabControl->Buttons();?>
                    <input type="submit" name="Update" value="<?=GetMessage("MAIN_SAVE");?>" title="<?=GetMessage("MAIN_OPT_SAVE_TITLE");?>" class="adm-btn-save">
                    <input type="submit" name="Apply" value="<?=GetMessage("MAIN_OPT_APPLY");?>" title="<?=GetMessage("MAIN_OPT_APPLY_TITLE");?>">
                    <?php if (strlen($_REQUEST["back_url_settings"]) > 0): ?>
                    <input type="button" name="Cancel" value="<?=GetMessage("MAIN_OPT_CANCEL");?>" title="<?=GetMessage("MAIN_OPT_CANCEL_TITLE");?>" onclick="window.location='<?php echo htmlspecialcharsbx(CUtil::addslashes($_REQUEST["back_url_settings"])); ?>'">
                    <input type="hidden" name="back_url_settings" value="<?=htmlspecialcharsbx($_REQUEST["back_url_settings"]);?>">
                    <?php endif;?>
                    <?php $tabControl->End();?>
    </form>