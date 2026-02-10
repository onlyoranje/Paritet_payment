<?
include dirname(__FILE__) . "/install/version.php";
$moduleId = 'paritet1.payment';
$PB_CONFIG = array(
    'MODULE_ID' => 'paritet1.payment',
    'BANK_NAME' => 'Паритетбанк',

   'PROD_URL' => 'https://partner-loans.paritetbank.by/vendor-api/',
     



    'ISO' => array(
        'USD' => 840,
        'EUR' => 978,
        'RUB' => 810,
        'RUR' => 810,
        'BYN' => 933
    ),
    'MODULE_VERSION' => $arModuleVersion['VERSION'],
    
);


?>