<?php
if(!isset($PARAMETROS[1])){
  HeaderLocation("/");
  exit;
}
$url = urldecode($PARAMETROS[1]);
$busca = getListDb('post',['url'=>$url]);
if(!$busca){
  HeaderLocation("/");
  exit;
}
$BODY['top'] = renderView('widgets/cabecalho',[
  "logo"=>$sys['url_storage'].($sys['layout']['header']['logo'] == 'white' ? $sys['logo_white'] : $sys['logo'])
]);
$BODY['content'] = renderView('pages/post',$busca[0]);
$BODY['title-page'] = $busca[0]['title'];
$BODY['global']['sys']['description'] = $busca[0]['description'];
$BODY['extra-head'] .= "\n\n";

$BODY['extra-head'] .= "\n".'<meta property="og:locale" content="pt_BR"/>';
$BODY['extra-head'] .= "\n".'<meta property="og:type" content="article"/>';
$BODY['extra-head'] .= "\n".'<meta property="og:title" content="'.$busca[0]['title'].'"/>';
$BODY['extra-head'] .= "\n".'<meta property="og:description" content="'.$busca[0]['description'].'"/>';
$BODY['extra-head'] .= "\n".'<meta property="og:url" content="'.ENDERECO_SITE.'/post/'.$busca[0]['url'].'" />';
$BODY['extra-head'] .= "\n".'<meta property="og:site_name" content="'._SYS_['title'].'" />';
$BODY['extra-head'] .= "\n".'<meta property="article:published_time" content="'.date('Y-m-d\TH:i:s').'-03:00" />';
$BODY['extra-head'] .= "\n".'<meta property="og:image" content="'._SYS_['url_storage'].'/files/'.$busca[0]['url'].'.png" />';
$BODY['extra-head'] .= "\n".'<meta property="og:image:type" content="image/png" />'."\n";



// HeaderContent("text/plain");
// var_dump($BODY);
echo renderView('body',$BODY);
