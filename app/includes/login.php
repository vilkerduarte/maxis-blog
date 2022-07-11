<?php
HeaderLocation("/acesso-negado");
if(isset($_POST['email_login'],$_POST['senha_login'])){
	reset_post_session();
	$user = new Usuarios;
	if($user->entrar($_POST['email_login'],$_POST['senha_login'])){
		$tee = isset($_SESSION['tipo']) && $_SESSION['tipo'] == 'admin' ? '' : '';
		recarregar_pagina('/'.$tee);
	}else{
		mensagem_de_erro($LANG['login']['messages']['error']);
	}
}
$BODY['content'] = renderView('pages/login',array("token"=>md5(time())));
echo renderView('body',$BODY);
?>
