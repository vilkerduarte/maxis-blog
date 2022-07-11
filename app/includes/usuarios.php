<?php

if(isset($_SESSION['tipo']) && $_SESSION['tipo'] == 'admin'){
  $Usuarios = new Usuarios;
  if(isset($_POST['action'],$_POST['hash'],$_POST['token']) and !check_post($_POST['token'])){
    receive_post($_POST['token']);
    $ACTION = $_POST['action'];
    $HASH = $_POST['hash'];

    if($ACTION == 'bloquear'){
      $user = verItemDb('users',$HASH);
      if($user){
        if($Usuarios->bloquear_usuario($HASH,($user['status'] == 'Ativo' ? false : true))){
          echo 'ok';
        }else{
          echo 'erro';
        }
      }else{
        http_response_code(404);
        exit;
      }
    }else if($ACTION == 'excluir'){
      if($Usuarios->excluir_usuario($HASH)){
        echo 'ok';
      }else{
        echo 'erro';
      }
    }else if($ACTION == 'alterar-privilegios'){
      if($Usuarios->tornar_admin($HASH)) {
        echo 'ok';
      }else{
        echo 'erro';
      }
    }else{
      http_response_code(401);
      exit;
    }
    exit;
  }
}
$users = new Usuarios;



$html = array();
$html['token'] = set_token();
$html['regs'] = '';
$busca = $users->listar_usuarios(false,true,35,$PAGE);
if($busca){
	foreach($busca['page'] as $item){
		$item['admin-label'] = $item['tipo'] == 'admin' ? '<div class="admin-label">'.$LANG['usuarios']['lbAdmin'].'</div>' : FILTRO_nome($item['tipo']);

		$html['regs'] .= renderView('cards/reg-user',$item);

	}
	$html['regs'] .= check_pagination($busca['full'],$PAGE,$GET_STRING,35);

}else{
	$html['regs'] .= '<h2 class="col-12 font-18 text-center mb-5">Nenhum Registro Encontrado</h2>';
}
$BODY['content'] = renderView('pages/usuarios',$html);
echo renderView('body',$BODY);
?>
