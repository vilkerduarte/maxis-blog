<?php
if(isset($_POST['cad'],$_POST['token']) and !check_post($_POST['token'])){
	receive_post($_POST['token']);
	if($_POST['cad']['senha'] == $_POST['cad']['csenha'] && strlen($_POST['cad']['senha']) >= 6){
		if(is_array($_POST['cad'])){
			$user = new Usuarios;
			if(!$user->check_mail_user($_POST['cad']['email'])){
				if($user->novo_usuario($_POST['cad'])){
					$user->entrar($_POST['cad']['email'],$_POST['cad']['senha']);
					HeaderLocation('/');
				}else{
					mensagem_de_erro();
				}
			}else{
				mensagem_de_erro($LANG['cadastro']['messages']['conflito']);
			}
		}else{
			mensagem_de_erro();
		}
	}else{
		mensagem_de_erro($LANG['cadastro']['messages']['errorPass']);
	}
}

$html['titulo'] = isset($_SESSION['tipo']) && $_SESSION['tipo'] == 'admin' ? $LANG['cadastro']['pageSubtitle'] :$LANG['cadastro']['pageTitle'];

$cad['class-termos-de-uso'] = isset($_SESSION['tipo']) && $_SESSION['tipo'] == 'admin' ? 'force-hide' : '';
$cad['token'] = set_token();

$html['content'] = renderView('pages/cadastro',$cad);

$BODY['content'] = renderView('pages/generic',$html);

echo renderView('body',$BODY);
?>
