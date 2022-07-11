<?php

$Usuarios = new Usuarios;
if(isset($GET['key'])){
	$key = $GET['key'];
	$cod = $Usuarios->check_cod_pass($key);
	if($cod){

	}
}else{
	recarregar_pagina();
	return;
}


if(isset($_POST['senha'],$_POST['token']) and !check_post($_POST['token'])){
	receive_post($_POST['token']);
	if($Usuarios->redefinir_senha($_POST['senha'],$key)){
		mensagem_com_location('Sua senha foi redefinida com sucesso!','/login');
	}else{
		mensagem_de_erro();
	}
}

$htm = array(
"token"=>set_token()
);

$BODY['content'] = renderView('pages/redefinir-senha',$htm);
echo renderView('body',$BODY);
