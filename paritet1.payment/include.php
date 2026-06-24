<?
use Bitrix\Main\EventManager;

$eventManager = EventManager::getInstance();
\Bitrix\Main\EventManager::getInstance()->addEventHandler('main', 'OnAdminContextMenuShow', 'OrderDetailAdminContextMenuShow');
function OrderDetailAdminContextMenuShow(&$items){
        $arReports[] = array(
                  "TEXT" => "123",
                   "LINK"=>"button.php"

               );
   if ($_SERVER['REQUEST_METHOD']=='GET' && $GLOBALS['APPLICATION']->GetCurPage()=='/bitrix/admin/sale_order_edit.php' && $_REQUEST['ID']>0)
        {
                $items[] = array(
                        "TEXT"=>"Сборка заказов",
                        "LINK"=>"button.php",
                        "TITLE"=>"Сборка товаров",
                        "ICON"=>"btn_new",
                  "MENU" => $arReports
                  );
        }
        if ($_SERVER['REQUEST_METHOD']=='GET' && $GLOBALS['APPLICATION']->GetCurPage()=='/bitrix/admin/sale_order_view.php' && $_REQUEST['ID']>0)
        {
                $items[] = array(
                        "TEXT"=>"Сборка заказов",
                        "LINK"=>"button.php",
                        "TITLE"=>"Сборка товаров",
                        "ICON"=>"btn_new",
                  "MENU" => $arReports
                  );
        }
}
?>