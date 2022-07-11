<?php
$html = [
  'card-post'=>''
];
$BODY['top'] = renderView('widgets/cabecalho',[
  "logo"=>$sys['url_storage'].($sys['layout']['header']['logo'] == 'white' ? $sys['logo_white'] : $sys['logo'])
]);
$busca = getListDb('post',false,'=','ID DESC',true,50,$PAGE);

if($busca){
  foreach ($busca["page"] as $key => $item) {
    $item['link-img'] = _SYS_['url_storage'].'/files/'.$item['url'].'.png';
    $html['card-post'] .= renderView("cards/post",$item);
  }
}

$BODY['content'] = renderView('pages/index',$html);

echo renderView('body',$BODY);
