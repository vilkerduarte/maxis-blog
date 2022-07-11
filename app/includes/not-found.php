<?php
$BODY['content'] = renderView('pages/not-found',array());
if($MODAL__){
	echo $BODY['content'];
}else{
	echo renderView('body',$BODY);
}
