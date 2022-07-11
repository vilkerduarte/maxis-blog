<?php
HeaderContent('text/plain');
$MODAL__ = true;
if(isset($_REQUEST['page'])){
	$GET = isset($_REQUEST['GET']) ? _GET($_REQUEST['GET']) : '';
	$P = $_REQUEST['page'];
	$i = array_search($P,$sys['CONTROL_LANG']['DIR']);
	if(is_string($i)){
		$P = $i;
	}
  if(class_exists('Usuarios')){
	   check_session_pages_modal($P);
  }
	if(file_exists(__DIR__.'/'.$P.'.php')){
		include_once(__DIR__.'/'.$P.'.php');
	}else{
		include_once(__DIR__."/not-found.php");
	}
}
