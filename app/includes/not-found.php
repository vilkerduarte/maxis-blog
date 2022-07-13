<?php
$BODY['top'] = renderView('widgets/cabecalho',[
  "logo"=>$sys['url_storage'].($sys['layout']['header']['logo'] == 'white' ? $sys['logo_white'] : $sys['logo'])
]);
$BODY['content'] = renderView('pages/not-found',array());
if($MODAL__){
	echo $BODY['content'];
}else{
	http_response_code(404);
	echo renderView('body',$BODY);
}
