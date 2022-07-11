<?php
if(isset($_SESSION['ID'],$_SESSION['nome'],$_SESSION['email'],$_SESSION['key'],$_SESSION['tipo'],$_SESSION['token'])){
	@session_start();
	$ID_USER = $_SESSION['ID'];
	$NOME_USER = $_SESSION['nome'];
	$EMAIL_USER = $_SESSION['email'];
	$KEY_USER = $_SESSION['key'];
	$TIPO_USER = $_SESSION['tipo'];
	$TOKEN_USER = $_SESSION['token'];
}else{
	if(isset($MODAL__)){
		$_REQUEST['page'] = "login";
		$DIR = "login";
	}else{
		@header("LOCATION: /login");
	}
}
?>