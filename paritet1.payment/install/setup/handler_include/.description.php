<?php
/**
 * Загрузка описания обработчика «Кредит/рассрочка от Паритетбанка».
 * Ищем папку модуля и в /bitrix/modules, и в /local/modules,
 * чтобы обработчик работал независимо от места установки.
 */
$__pb_module_dir = $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/{module_path}";
if (!is_dir($__pb_module_dir)) {
    $__pb_module_dir = $_SERVER["DOCUMENT_ROOT"] . "/local/modules/{module_path}";
}
$data = require $__pb_module_dir . "/handler/.description.php";

return $data;