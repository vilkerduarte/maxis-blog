<?php
$BODY['content'] = renderView('pages/not-found',array());
if($MODAL__){
	echo $BODY['content'];
}else{
	http_response_code(404);
	echo renderView('body',$BODY);
}
