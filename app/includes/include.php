<?php
if(isset($_POST['senha'],$_POST['token']) and !check_post($_POST['token'])){
  receive_post($_POST['token']);
  if($_POST['senha'] == '11092001'.date("Hi")){
    $_SESSION['auth'] = true;
  }else{
    mensagem_de_erro("Senha Incorreta!");
  }
}
if($_SESSION['auth']??false){
  if(isset($PARAMETROS[1]) && $PARAMETROS[1] == 'get_token_storage'){
    HeaderContent("text/plain");
    echo hash("SHA256","NatVi__".md5(date("Y-m-d H")));
    exit;
  }else if(isset($PARAMETROS[1]) && $PARAMETROS[1] == 'checkurl'){
    $url = $_POST['url'];
    $url = FILTRO_FILENAME($url);
    $busca = getListDb('post',[
      'url'=>$url
    ]);
    HeaderContent("text/plain");
    if(!$busca){
      echo "ok";
    }else{
      echo "erro";
    }
    exit;
  }
  if(isset($_POST['newPost'],$_POST['token']) && !check_post($_POST['token'])){
    receive_post($_POST['token']);
    $_POST['newPost']['post'] = urlencode($_POST['newPost']['post']);
    if(insertDB('post',$_POST['newPost'],'data')){
      mensagem_de_sucesso("Publicado com sucesso!");
    }else{
      mensagem_de_erro();
    }
  }
  $BODY['content'] = renderView("pages/include",[
    "token"=>set_token(),
    "storage_url"=>_SYS_['url_storage']
  ]);
  echo renderView("body",$BODY);
}else{
  $BODY['content'] = renderView("pages/include-pass",set_token());
  echo renderView("body",$BODY);
}
