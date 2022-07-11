<?php
function send_email_notif_html(string $IDassunto,string $ID_Message,$from=null,$vars){
	$lang = getLanguage('emails');
	$sys = _SYS_;
	$assunto = $lang['assuntos'][$IDassunto];
	$mensagem = $lang['mensagens'][$ID_Message].'<br><br>'.($vars['msg']??'');

	$vars['color'] = $sys['colors']['color_2'];
	$vars['logo'] = $sys['logo_email'];
	$vars['title'] = '';
	$vars['site'] = $sys['site'];
	$vars['text-color'] = '#333333';
	$email['header'] = renderView('header-email',$vars);
	$vars['text-color'] = '#ffffff';
	$email['footer'] = renderView('footer-email',$vars);
	$email['texto'] = $mensagem;
	$email['titulo'] = $assunto;

	$mensagem = renderView('email',$email);
	$mensagem = str_var($mensagem,$vars);

	$destino = $from <> null ? $from : $sys['email_system'];

	$origem = $sys['email_system'];

	if($sys['external_SMTP']){
		return smtpmailer($destino, $origem, $sys['title'],$assunto, $mensagem,true);
	}else{
		$email_headers = implode ( "\n",array ( 'From: '.$sys['title'].'<'.$origem.'>',"Content-Type: text/html; charset=UTF-8" ) );
		return mail ($destino, $assunto, $mensagem, $email_headers)? true : false;
	}
}
