<?php
if($_SESSION['tipo'] <> 'admin'){
	include_once(__DIR__."/acesso-negado.php");
	exit;
}
$user = verItemDb('users',($GET['ID'] ?? 'Erro'));
if(!$user){
	include_once(__DIR__."/not-found.php");
	exit;
}
$user['info'] = '';
$BODY['content'] = renderView('page-ver-usuario',$user);
if($MODAL__){
	echo $BODY['content'];
}else{
	echo renderView('body',$BODY);
}
