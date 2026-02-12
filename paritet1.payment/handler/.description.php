<?
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Sale\PaySystem;
use Bitrix\Main\Loader;

Loc::loadMessages(__FILE__);
require dirname(dirname(__FILE__)) . '/config.php';
$moduleID = $PB_CONFIG['MODULE_ID'];
Loader::includeModule($moduleID);


$current_settings = array(
    'BANK_NAME' => $PB_CONFIG['BANK_NAME'],
    'MODULE_VERSION' => $PB_CONFIG['MODULE_VERSION'],

    'OPTION_LOGIN'              => COption::GetOptionString($moduleID, 'OPTION_LOGIN'),
    'OPTION_PASSWORD'           => COption::GetOptionString($moduleID, 'OPTION_PASSWORD'),
    'OPTION_STORE_ID'           => COption::GetOptionString($moduleID, 'OPTION_STORE_ID'),
    'OPTION_SALE_POINT_ID'      => COption::GetOptionString($moduleID, 'OPTION_SALE_POINT_ID'),
    'OPTION_TOKEN'              => COption::GetOptionString($moduleID, 'OPTION_TOKEN'),
    'OPTION_BANK_PRODUCT_ID'    => COption::GetOptionString($moduleID, 'OPTION_BANK_PRODUCT_ID'),
    'OPTION_STATUSES'           => COption::GetOptionString($moduleID, 'OPTION_STATUSES'),
);

if ($current_settings['OPTION_SALE_POINT_ID']) {
  $arr_BP = unserialize($current_settings['OPTION_BANK_PRODUCT_ID']);

}

$arr_status = unserialize($current_settings['OPTION_STATUSES']);

$data = [
    'NAME' => 'Кредит/рассрочка от Паритетбанка',
    'SORT' => 100,
    'CODES' => [
        
        "BANK_PRODUCT" => [
        "NAME" => Loc::getMessage("PB_BANK_PRODUCT_NAME"),
        "DESCRIPTION" =>Loc::getMessage("PB_BANK_PRODUCT_DESC"),
        'SORT' => 100,
        'GROUP' => Loc::getMessage("PB_GROUP_ORDER"),
        'TYPE' => 'SELECT',
        'INPUT' => [
            'TYPE' => 'ENUM',
            'OPTIONS' => $arr_BP
        ],

        ],

        "PB_ORDER_NUMBER" => [
            "NAME" => Loc::getMessage("PB_ORDER_NUMBER_NAME"),
            "DESCRIPTION" => Loc::getMessage("PB_ORDER_NUMBER_DESC"),
            'SORT' => 130,
            'GROUP' => Loc::getMessage("PB_GROUP_ORDER"),
            'DEFAULT' => [
                'PROVIDER_KEY' => 'ORDER',
                'PROVIDER_VALUE' => 'ACCOUNT_NUMBER'
            ]
        ],
        "PB_PREFIX" => [
            "NAME" => Loc::getMessage("PB_PREFIX_NAME"),
            "DESCRIPTION" => Loc::getMessage("PB_PREFIX_DESC"),
            'SORT' => 120,
            'GROUP' => Loc::getMessage("PB_GROUP_ORDER"),
        ],
        "PB_PHONE_NUMBER" => [
            "NAME" => Loc::getMessage("PB_PHONE_NUMBER_NAME"),
            "DESCRIPTION" => Loc::getMessage("PB_PHONE_NUMBER_DESC"),
            'SORT' => 120,
            'GROUP' => Loc::getMessage("PB_GROUP_ORDER"),
            'DEFAULT' => [
                'PROVIDER_KEY' => 'USER',
                'PROVIDER_VALUE' => 'PERSONAL_MOBILE'
             ]
        ],
        "PB_BELARUS_PRODUCT" => [
            "NAME" => Loc::getMessage("PB_BELARUS_PRODUCT_NAME"),
            "DESCRIPTION" => Loc::getMessage("PB_BELARUS_PRODUCT_DESC"),
            'SORT' => 140,
            'GROUP' => Loc::getMessage("PB_GROUP_ORDER"),
            "INPUT" => [
                'TYPE' => 'Y/N'
            ],

        ],
        "PB_CLIENT_REDIRECT" => [
            "NAME" => Loc::getMessage("PB_CLIENT_REDIRECT_NAME"),
            "DESCRIPTION" => Loc::getMessage("PB_CLIENT_REDIRECT_DESC"),
            'SORT' => 150,
            'GROUP' => Loc::getMessage("PB_GROUP_ORDER"),
        ],
        "PB_STATUS_REDIRECT" => [
            "NAME" => Loc::getMessage("PB_STATUS_REDIRECT_NAME"),
            "DESCRIPTION" => Loc::getMessage("PB_STATUS_REDIRECT_DESC"),
            'SORT' => 160,
            'GROUP' => Loc::getMessage("PB_GROUP_ORDER"),
        ],
        "PB_ALTERNATIVE_CLAIM" => [
            "NAME" => Loc::getMessage("PB_ALTERNATIVE_CLAIM_NAME"),
            "DESCRIPTION" => Loc::getMessage("PB_ALTERNATIVE_CLAIM_DESC"),
            'SORT' => 170,
            'GROUP' => Loc::getMessage("PB_GROUP_ORDER"),
            "INPUT" => [
                'TYPE' => 'Y/N'
            ],


        ],
        "PB_STATUS_PAY" => [
            "NAME" => Loc::getMessage("PB_STATUS_PAY_NAME"),
            "DESCRIPTION" => Loc::getMessage("PB_STATUS_PAY_DESC"),
            'SORT' => 180,
            'GROUP' => Loc::getMessage("PB_GROUP_ORDER"),
            'TYPE' => 'SELECT',
            'INPUT' => [
            'TYPE' => 'ENUM',
            'OPTIONS' => $arr_status,
        ],


        ],
        "PB_SMS" => [
            "NAME" => Loc::getMessage("PB_SMS_NAME"),
            "DESCRIPTION" => Loc::getMessage("PB_SMS_DESC"),
            'SORT' => 180,
            'GROUP' => Loc::getMessage("PB_GROUP_ORDER"),
            "INPUT" => [
                'TYPE' => 'Y/N'
            ],
            ],
    ]
];

