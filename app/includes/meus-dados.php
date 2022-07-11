<?php


$usuarios = new Usuarios;
$status = false;
if(isset($_POST['email'],$_POST['senha'],$_POST['csenha'])){
	if($_POST['senha'] == $_POST['csenha'] && strlen($_POST['senha']) >= 6){
		if($usuarios->alterar_senha($_SESSION['key'],$_POST['senha'])){
			$status = true;
		}
	}
	if(!empty($_POST['email'])){
		if($usuarios->alterar_email($_SESSION['key'],$_POST['email'])){
			$status = true;
		}
	}
	if($status){
		mensagem_de_sucesso($LANG['meus-dados']['messages']['dadosAlterados']);
	}else{
		mensagem_de_erro();
	}
}
$dados = $usuarios->dados_usuario($_SESSION['key']);

$BODY['content'] = renderView('pages/meus-dados',$dados);
echo renderView('body',$BODY);
?>
