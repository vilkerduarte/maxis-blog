<?php
$defaultConfigFile = __DIR__."/config.json";
$LocalConfigFile = __DIR__."/local.config.json";
$ConfigFile = file_exists($LocalConfigFile) ? $LocalConfigFile : $defaultConfigFile;
$ConfigFile = file_get_contents($ConfigFile);

$sys = json_decode($ConfigFile,true) ?? array();

$sys['protocolo'] = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';
$sys['default_locale'] = $sys['default_locale'] ?? 'pt-BR';
$sys['LOCALE'] = $_SESSION['LOCALE'] ?? $sys['default_locale'];
$sys['CONTROL_LANG'] = file_get_contents(__DIR__."/lang/".$sys['LOCALE']."/control.json");
$sys['CONTROL_LANG'] = json_decode($sys['CONTROL_LANG'],true);
$sys['LOCALE_CONFIG'] = json_decode(file_get_contents(__DIR__."/lang/config.json"),true) ?? array();


define('_SYS_',$sys);
if(isset($_SERVER['HTTP_HOST'])){
  define('ENDERECO_SITE',$sys['protocolo'].'://'.$_SERVER['HTTP_HOST']);
}else{
  define('ENDERECO_SITE','');
}

define('DIR_CSS',ENDERECO_SITE.'/css/');
define('DIR_JS',ENDERECO_SITE.'/js/');
define('DIR_IMG',ENDERECO_SITE.'/img/');
define('HOME_FILES',ENDERECO_SITE.'/home_assets/');

$sLoc = str_replace('-','_',$sys['LOCALE']);
setlocale(LC_TIME, $sLoc, $sLoc.'.iso-8859-1', $sLoc.'.utf-8');
date_default_timezone_set('America/Sao_Paulo'); //FUSO HORARIO

#SMTP_CONFIG
define('GUSER', $sys['SMTP']['user']);
define('GPWD', $sys['SMTP']['pass']);
define('SMTP_HOST', $sys['SMTP']['host']);
define('SMTP_PORT', $sys['SMTP']['port']);

if (version_compare(phpversion(), '7.1', '>=')) {
    ini_set( 'serialize_precision', -1 );
}

$color__1 = $sys['colors']['color_1'];
$color__2 = $sys['colors']['color_2'];
$color__3 = $sys['colors']['color_3'];
$color__4 = $sys['colors']['color_4'];
$color__5 = $sys['colors']['color_5'];

$globalConfig['sys'] = $sys;
if(isset($globalConfig['sys']['DB'])){
  unset($globalConfig['sys']['DB']);
}
unset($globalConfig['sys']['SMTP'],$globalConfig['sys']['modules']);
$globalConfig['endereco-site'] = ENDERECO_SITE;
$globalConfig['cor1'] = $color__1;
$globalConfig['cor2'] = $color__2;
$globalConfig['cor3'] = $color__3;
$globalConfig['cor4'] = $color__4;
$globalConfig['cor5'] = $color__5;

require_once(__DIR__."/phpmailer/class.phpmailer.php");
include_once(__DIR__."/Du-Art_functions.php");
//#FIM#
?>
