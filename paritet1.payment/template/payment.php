<?php
use Bitrix\Main\Localization\Loc;

IncludeModuleLangFile(__FILE__);

// Если обработчик ещё не выполнял запрос в этом флоу — запускаем логику,
// чтобы результат $params['order'] был доступен для отрисовки.
if (empty($params['order']) || !is_array($params['order'])) {
    require dirname(__DIR__) . '/handler/handler.php';
}

$order = (is_array($params['order'])) ? $params['order'] : array();
$result = $order['result'] ?? array();
$qr = $result['qr'] ?? array();
?>
<div class="paritet" style="text-align: left">
<?php if (!empty($qr['urlToCreateClaim'])): ?>
    <a href="<?= htmlspecialcharsbx($qr['urlToCreateClaim']); ?>" target="_blank">
        <input name="" class="btn btn-default" type="button"
               value="<?= htmlspecialcharsbx(Loc::getMessage('PB_ORDER_BUTTON_NAME')); ?>">
    </a>
<?php endif; ?>

<?php if (!empty($order['message'])): ?>
    <div class="adm-info-message">
        <?= htmlspecialcharsbx($order['message']); ?>
    </div>
    <input name="" class="btn btn-default" type="button"
           value="<?= htmlspecialcharsbx(Loc::getMessage('PB_ERROR_MESSAGE_UNDEFIND')); ?>">
<?php endif; ?>

<p><ol>
    <li><?= Loc::getMessage('PB_STEP1') ?></li>
    <li><?= Loc::getMessage('PB_STEP2') ?></li>
    <li><?= Loc::getMessage('PB_STEP3') ?></li>
    <li><?= Loc::getMessage('PB_STEP4') ?></li>
    <li><?= Loc::getMessage('PB_STEP5') ?></li>
</ol></p>
</div>
