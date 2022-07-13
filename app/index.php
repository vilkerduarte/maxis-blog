<?php
/*
** -------------------------
** Author: Vilker Duarte
** Framework: AJS v2.2
** Updated: 2022-01-11
** -------------------------
*/





@session_start();
include_once(__DIR__."/set/config.php");
if(_SYS_['DB'][0]['user'] == 'maxis_blog'){
	$CSP = "Content-Security-Policy: ";
	$CSP .= "default-src 'self' 'unsafe-inline' *.gstatic.com *.googleapis.com googleapis.com *.7exp.us; ";
	header($CSP);
	// header("Content-Security-Policy: default-src 'self'; img-src storage.7exp.us; media-src youtube.com;");
}

/* -----------------------------
========= Fingerprint ==========
------------------------------*/

if(function_exists('get_connect')){
	if(isset($_POST['fingerprint'],$_POST['metadata']) && ($_POST['fingerprint'] == ($_SESSION['fingerprint'] ?? 'ERRRRRROOO'))){
	    Fingerprint::setMetadata($_POST['fingerprint'],$_POST['metadata']);
	    exit;
	}
}
if(isset($_POST['fingerprint']) && !isset($_SESSION['fingerprint'])){
	$_SESSION['fingerprint'] = $_POST['fingerprint'];
	if(function_exists('get_connect')){
		$buscaMeta = Fingerprint::checkMeta($_POST['fingerprint']);
    header("Content-Type: text/plain");
    if(!$buscaMeta){
        echo 'meta requested';
    }else{
        echo 'ok';
    }
	}
	exit;
}

/* -----------------------------
====== Fingerprint's End =======
------------------------------*/



$URL_ = strstr($_SERVER['REQUEST_URI'],'?') ? strstr($_SERVER['REQUEST_URI'],'?',true) : $_SERVER['REQUEST_URI'];
if($URL_ <> '/'){
	$URI = $_SERVER['REQUEST_URI'];
	$GET_STRING = strstr($URI,'?');
	$GET = _GET($GET_STRING);
	$PAGE = isset($GET['page']) ? intval($GET['page']) : 1;
	if($GET_STRING){
		$DIR = strstr($URI,'?',true);
	}else{
		$DIR = $URI;
	}
	$DIR = trim($DIR,'/');
	$cont = substr_count($DIR, '/');
	define('DIR_COUNT', $cont);
	$arr_tst = explode('/',$DIR);
	$sys['page'] = ucwords(str_replace('-',' ',$arr_tst[$cont]));
}else{
	$GET_STRING = strstr($_SERVER['REQUEST_URI'],'?');
	$GET = _GET($GET_STRING);
	$PAGE = isset($GET['page']) ? intval($GET['page']) : 1;
	$DIR = 'index';
	$sys['page'] = '';
}

$MODAL__ = false;

if(function_exists('check_session_pages')){
	check_session_pages($DIR);
}


function getDefaultMenu(){
	$Menu = (json(file_get_contents(__DIR__."/set/lang/"._SYS_['LOCALE']."/menu.json"))) ?? array();
	$Def = $Menu['default'];
	$Menu = array_merge($Def,($Menu[$_SESSION['tipo'] ?? 'externo']));
	$result = '';
	// $result = renderView('menu-item',array("link"=>"/","texto"=>"Início"));
	if($Menu){
		foreach($Menu as $texto=>$link){
			$item = array(
				"texto"=>$texto,
				"link"=>$link
			);
			$result .= renderView('menu-item',$item);
		}
	}
	return $result;
}
function getHtmlHeader($Array=false){
	$Vars = array(
		"extra"=>"",
		"class"=>"bg_color_2 shadow px-5 header-new",
		"class-logo"=>"",
		"locales"=>"",
		"logo-link"=>"/img/logo-white.svg",
		"class-menu"=>"text-right",
		"extra-content"=>'',
		"menu"=>getDefaultMenu()
	);
	$locs = _SYS_['LOCALE_CONFIG']['disponiveis'] ?? array();
	foreach($locs as $x){
		$Vars['locales'] .= renderView('locale-menu-item',$x);
	}
	if($Array && is_array($Array)){
		foreach($Array as $x=>$y){
			if(isset($Vars[$x])){
				$Vars[$x] = $y;
			}
		}
	}
	return renderView('cabecalho',$Vars);
}

$BODY['global'] = $globalConfig;
$BODY['title-page'] = (!empty($sys['page']) ? $sys['page'].' | ' : '').$BODY['global']['sys']['title'];
$BODY['extra-head'] = '';
$BODY['top'] = getHtmlHeader(false);
$BODY['content'] = '';
$BODY['bottom'] = '';
$BODY['bottom-script'] = '';

$PARAMETROS = explode('/',$DIR);
$DIR = $PARAMETROS[0];


$i = array_search($DIR,$sys['CONTROL_LANG']['DIR']);
if(is_string($i)){
	$DIR = $i;
}
if(isset($_GET['setLocale'])){
	$l = urldecode($_GET['setLocale']);
	if(array_search($l,$sys['LOCALE_CONFIG']['disponiveis']) !== false){
		$_SESSION['LOCALE'] = $l;
	}
	HeaderLocation("/".$DIR);
}
$LANG['MAIN'] = getLanguage('main');
$LANG[$DIR] = getLanguage($DIR);
if(isset($LANG[$DIR]['pageTitle'])){
	$BODY['title-page'] = $LANG[$DIR]['pageTitle'].' | '.$BODY['global']['sys']['title'];
}

if(!isset($_SESSION['fingerprint'])){
	$BODY['bottom'] .= "\n".'<script src="/js/requestFingerprint.js" type="text/javascript"></script>'."\n";
}
if(function_exists('get_connect') && !get_connect() && !in_array($DIR,['instalar','css','js'])){
	HeaderLocation(getLanguage('instalar')['myLink']);
}
if(file_exists('includes/'.$DIR.'.php')){
	include_once('includes/'.$DIR.'.php');
}else{
	include_once('includes/not-found.php');
}
@close_connect();
