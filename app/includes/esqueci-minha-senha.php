<?php

$Usuarios = new Usuarios;
if(isset($_POST['email'],$_POST['token']) and !check_post($_POST['token'])){
	receive_post($_POST['token']);
	$user = $Usuarios->check_mail_user($_POST['email']);
	if($user){
		if($Usuarios->esqueci_minha_senha($user['hash'])){
			mensagem_de_sucesso($LANG['MAIN']['messages']['esqueciMinhaSenha']);
		}else{
			mensagem_de_erro();
		}
	}else{
		mensagem_de_sucesso($LANG['MAIN']['messages']['esqueciMinhaSenha']);
	}
}
$BODY['content'] = renderView('pages/esqueci-minha-senha',md5(time()));
echo renderView('body',$BODY)
?>
