<?php
$BODY['content'] = renderView('pages/acesso-negado','');
if($MODAL__){
	echo $BODY['content'];
}else{
	echo renderView('body',$BODY);
}
