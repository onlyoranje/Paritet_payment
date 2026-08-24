<?php
/**
 * Логика оплаты «Кредит/рассрочка от Паритетбанка».
 * Ищем папку модуля и в /bitrix/modules, и в /local/modules.
 */
$__pb_module_dir = $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/{module_path}";
if (!is_dir($__pb_module_dir)) {
    $__pb_module_dir = $_SERVER["DOCUMENT_ROOT"] . "/local/modules/{module_path}";
}
require $__pb_module_dir . "/handler/handler.php";