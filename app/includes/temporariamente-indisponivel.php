<?php
$BODY['content'] = renderView('pages/temp',array());
if($MODAL__){
	echo $BODY['content'];
}else{
	echo renderView('body',$BODY);
}
