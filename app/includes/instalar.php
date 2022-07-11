<?php

if(isset($_POST['DBhost'],$_POST['DBuser'],$_POST['DBpass'],$_POST['DBname'],$_POST['token']) && !check_post($_POST['token'])){
	receive_post($_POST['token']);
	//$con = mysqli_connect($_POST['DBhost'],$_POST['DBuser'],$_POST['DBpass'],$_POST['DBname']);
	$con = mysqli_connect($_POST['DBhost'],$_POST['DBuser'],$_POST['DBpass']);
	if($con){
		mysqli_query($con,"DROP DATABASE IF EXISTS `".$_POST['DBname']."`");
		mysqli_query($con,"CREATE DATABASE `".$_POST['DBname']."` CHARACTER SET 'utf8' COLLATE 'utf8_general_ci'");
		$con = mysqli_connect($_POST['DBhost'],$_POST['DBuser'],$_POST['DBpass'],$_POST['DBname']);
	}
	if($con){
		Config::setLocalConfig('DB',array(array(
			"host"=>$_POST['DBhost'],
			"user"=>$_POST['DBuser'],
			"pass"=>$_POST['DBpass'],
			"db"=>$_POST['DBname']
		)));
		if(file_exists(__DIR__.'/../../DB.sql')){
			$SQL = file_get_contents(__DIR__.'/../../DB.sql');
			$SQL = decrypt($SQL);
			if(strstr($SQL,'CREATE')){
				$SQL = explode(';',$SQL);
				unset($SQL[count($SQL)-1]);
				foreach ($SQL as $query) {
					mysqli_query($con,$query);
				}
			}
		}
		mysqli_close($con);
		HeaderLocation('/');
	}else{
		mensagem_de_erro($LANG['instalar']['messages']['DbNotFound']);
		HeaderLocation('/');
	}
}


$BODY['top'] = '';
$BODY['content'] = renderView('pages/instalar',md5(time()));
echo renderView('body',$BODY);
