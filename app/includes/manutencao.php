<?php
$BODY['content'] = renderView('pages/manutencao',$BODY);
if($MODAL__){
	echo $BODY['content'];
}else{
	echo renderView('body',$BODY);
}
