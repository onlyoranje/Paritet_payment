<?php
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
IncludeModuleLangFile(__FILE__);

Class paritet1_payment extends CModule {

    var $MODULE_ID = 'paritet1.payment';
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_PATH;

    var $PAYMENT_HANDLER_PATH;

    function __construct() {
    	$path = str_replace("\\", "/", __FILE__);
    	$path = substr($path, 0, strlen($path) - strlen("/install/index.php"));

    	include($path."/install/version.php");
    	include($path."/config.php");

        $this->MODULE_PATH = $path;
        $this->MODULE_NAME =  $this->GetEncodeMessage('PB_MODULE_NAME') . " " . $P_CONFIG['BANK_NAME'];
        $this->MODULE_DESCRIPTION = $this->GetEncodeMessage('PB_MODULE_DESCRIPTION');
        $this->PARTNER_NAME = $this->GetEncodeMessage('PB_PARTNER_NAME');
        $this->PARTNER_URI = $this->GetEncodeMessage('PB_PARTNER_URI');

        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

        $this->PAYMENT_HANDLER_PATH = $_SERVER["DOCUMENT_ROOT"] . COption::GetOptionString('sale', 'path2user_ps_files') . str_replace(".", "_", $this->MODULE_ID) . "/";



    }

    function GetEncodeMessage($text) {
    	$siteEncode = SITE_CHARSET;
    	$message = Loc::getMessage($text);
        if(mb_detect_encoding($message,mb_list_encodings()) == 'UTF-8') {
            $old_enc = 'UTF-8';
        } else {
            $old_enc = 'windows-1251';
        }
        if($siteEncode == $old_enc) {
            return $message;
        }
    	return mb_convert_encoding( $message, $siteEncode, $old_enc);
    }

    function reEncode($folder, $enc) {
        $files = scandir($folder);
        foreach( $files as $file ) {
            if( $file == "." || $file == ".." ) { continue; }

            $path = $folder . DIRECTORY_SEPARATOR . $file;
            $content = file_get_contents($path);

            if( is_dir($path) ) {
                $this->reEncode( $path, $enc );
            }
            else {

                if(mb_detect_encoding($content,mb_list_encodings()) == 'UTF-8') {
                    $old_enc = 'UTF-8';
                } else {
                    $old_enc = 'windows-1251';
                }
                if($enc == $old_enc) {
                    continue;
                }
                $content = mb_convert_encoding( $content, $enc, $old_enc );
                if( is_writable($path) ) {
                	unlink($path);
                    $ff = fopen($path,'w');
                    fputs($ff,$content);
                    fclose($ff);
                }
            }
        }
    }

    function changeFiles($files) {

        foreach ($files as $file) {
            if ($file->isDot() === false) {
                $path_to_file = $file->getPathname();
                $file_contents = file_get_contents($path_to_file);
                $file_contents = str_replace("{module_path}", $this->MODULE_ID, $file_contents);
                file_put_contents($path_to_file, $file_contents);
            }
        }
    }
    function InstallFiles($arParams = array()) {

        CopyDirFiles($this->MODULE_PATH . "/install/setup/handler_include", $this->PAYMENT_HANDLER_PATH, true, true);
     
        $this->reEncode($this->MODULE_PATH . "/lang/", SITE_CHARSET);
        $this->changeFiles(new DirectoryIterator($this->PAYMENT_HANDLER_PATH));
        $this->changeFiles(new DirectoryIterator($this->PAYMENT_HANDLER_PATH . 'template/'));

    }

    function UnInstallFiles() {
        DeleteDirFilesEx(COption::GetOptionString('sale', 'path2user_ps_files') . str_replace(".", "_", $this->MODULE_ID));
    }

    function DoInstall() {
        $this->InstallFiles();
        COption::RemoveOption($this->MODULE_ID, "iso");
        COption::RemoveOption($this->MODULE_ID, "result_order_status");
        RegisterModule($this->MODULE_ID);
        COption::SetOptionInt($this->MODULE_ID, "delete", false);

    }

    function DoUninstall() {
        COption::SetOptionInt($this->MODULE_ID, "delete", true);
        DeleteDirFilesEx(COption::GetOptionString('sale', 'path2user_ps_files') . str_replace(".", "_", $this->MODULE_ID));
        DeleteDirFilesEx($this->MODULE_ID);
        UnRegisterModule($this->MODULE_ID);
        return true;
    }
    function log($data){
         file_put_contents(
            __DIR__ . '/PIP-' . date('d-m-Y-H') . '-log.json',
            json_encode($data, JSON_PRETTY_PRINT, JSON_UNESCAPED_UNICODE),
            FILE_APPEND  );
    }
}

?>