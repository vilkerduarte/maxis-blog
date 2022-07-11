<?php
class Usuarios{
	public static function novo_usuario($array,$foto=false){
		if(is_array($array)){
			foreach($array as $i=>$d){
				$dados[$i] = FILTRO($d);
			}
			$DB['nome'] = FILTRO_nome($dados['nome']);
			$DB['senha'] = FILTRO_senha($dados['senha']);
			$DB['email'] = strtolower($dados['email']);
			$DB['tipo'] = 'normal';
			$DB['data'] = date_time_now();
			$DB['status'] = isset($_SESSION['tipo']) && $_SESSION['tipo'] == 'admin' ? 'Ativo' : 'Ativo';
			$hash = insertDB('users',$DB,'hash');
			if($hash){
				Log::novo("Usuário Cadastrado! $DB[nome] <$DB[email]> hash: $hash");

				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}


	function entrar($email,$senha){
		$email = FILTRO($email);
		$senha = FILTRO_senha($senha);
		$busca = get_data_single("SELECT * FROM `"._SYS_['prefix_db']."users` WHERE `email` = '$email' AND `senha` = '$senha' AND `status` = 'Ativo'");
		if($busca){
			@session_start();
			$key_user = $busca['hash'];
			$_SESSION['ID'] = $busca['ID'];
			$_SESSION['nome'] = $busca['nome'];
			$_SESSION['email'] = $busca['email'];
			$_SESSION['tipo'] = $busca['tipo'];
			$_SESSION['key'] = $busca['hash'];
			$_SESSION['dados'] = $busca;
			$_SESSION['token'] = set_token();
			if(isset($_SESSION['token-app-id'])){
				$this->inserir_id($_SESSION['token-app-id'],$busca['hash']);
			}
			if(isset($_SESSION['fingerprint'])){
			    Fingerprint::regFinger($_SESSION['fingerprint'],$busca['hash']);
			}
			return true;
		}else{
			return false;
		}
	}


	function entrar_hash($key){
		$key = FILTRO($key);
		$busca = get_data_single("SELECT * FROM `"._SYS_['prefix_db']."users` WHERE `hash` = '$key' AND `status` = 'Ativo'");
		if($busca){
			@session_start();
			$key_user = $busca['hash'];
			$_SESSION['ID'] = $busca['ID'];
			$_SESSION['nome'] = $busca['nome'];
			$_SESSION['email'] = $busca['email'];
			$_SESSION['tipo'] = $busca['tipo'];
			$_SESSION['key'] = $busca['hash'];
			$_SESSION['dados'] = $busca;
			$_SESSION['token'] = set_token();
			if(isset($_SESSION['token-app-id'])){
				$this->inserir_id($_SESSION['token-app-id'],$busca['hash']);
			}
			if(isset($_SESSION['fingerprint'])){
			    Fingerprint::regFinger($_SESSION['fingerprint'],$busca['hash']);
			}
			return true;
		}else{
			return false;
		}
	}


	function id_machine($COD){
		if(strstr($COD,'---')){
	        $COD = explode('---',$COD)[0];
	    }
		$COD = FILTRO_senha($COD);
		$busca = get_data_single("SELECT * FROM `"._SYS_['prefix_db']."app-token` WHERE `code` = '$COD'");
		if($busca){
			$in = intval(strtotime("now"));
			$ip = get_ip();
			if($this->entrar_hash($busca['user'])){
				query("UPDATE `"._SYS_['prefix_db']."app-token` SET `last_used` = $in, `ip` = '$ip' WHERE `code` = '$COD'");
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}


	function inserir_id($COD,$UserID){
		if(strstr($COD,'---')){
	        $C = explode('---',$COD);
	        $DB['firebase-token'] = $C[1];
	        $DB['code'] = FILTRO_senha($C[0]);
	    }else{
	        $DB['code'] = FILTRO_senha($COD);
	    }
		$UserID = FILTRO($UserID);
		$in = intval(strtotime("now"));
		$DB['user'] = $UserID;
		$DB['last_used'] = $in;
		insertDB('app-token',$DB,'ip');
	}


	function sair(){
		$LOCA = $_SESSION['LOCALE'];
		@session_start();
		if(isset($_SESSION['token-app-id'])){
	        $token = FILTRO_senha($_SESSION['token-app-id']);
	        query("DELETE FROM `"._SYS_['prefix_db']."app-token` WHERE `code` = '$token'");
	    }
		session_destroy();
		@session_start();
		$_SESSION['LOCALE'] = $LOCA;
	}

	function aprovar_usuario($key){
		$key = FILTRO($key);
		$user = $this->dados_usuario($key);
		if($user){
			$DB['status'] = 'Ativo';
			if(updateDB('users',$DB,$user['hash'])){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}


	function listar_usuarios($filtrosArray=false,bool $pagination=false,int $itens_p_pagina=35,int $pagina_atual=1){
		if($filtrosArray){
			$str = 'WHERE ';
			foreach($filtrosArray as $a=>$b){
				$str .= '`'.$a.'` LIKE \'%'.$b.'%\'***@@***';
			}
			$str = trim($str,'***@@***');
			$str = str_replace('***@@***',' AND ',$str);
		}else{
			$str = '';
		}
		$busca = get_data("SELECT * FROM `"._SYS_['prefix_db']."users` $str ORDER BY `nome` ASC",$pagination,$itens_p_pagina,$pagina_atual);
		return $busca;
	}


	function bloquear_usuario(string $ID_Usuario, bool $desbloquear=false){
		$busca = $this->dados_usuario($ID_Usuario);
		if($busca){
			if($desbloquear){
				$DB['status'] = 'Ativo';
			}else{
				$DB['status'] = 'Bloqueado';
			}
			return updateDB('users',$DB,$busca['hash']);
		}else{
			return false;
		}
	}


	function excluir_usuario($ID_Usuario){
		$busca = $this->dados_usuario($ID_Usuario);
		if($busca){
			$DB['status'] = 'Excluído';
			return updateDB('users',$DB,$busca['hash']);
		}else{
			return false;
		}
	}


	function dados_usuario($ID_Usuario){
		$str = is_hash($ID_Usuario) ? ' `hash` = '."'$ID_Usuario'" : ' `ID` = '."'$ID_Usuario'";
		$SQL = "SELECT * FROM `"._SYS_['prefix_db']."users` WHERE $str";
		$busca = get_data_single($SQL);
		return $busca?$busca:false;
	}


	function tornar_admin($ID){
		$busca = $this->dados_usuario($ID);
		if($busca && $_SESSION['tipo'] == 'admin'){
			$DB['tipo'] = ($busca['tipo'] == 'normal' ? 'admin' : 'normal');
			return updateDB('users',$DB,$busca['hash']);
		}else{
			return false;
		}
	}


	function check_mail_user($email){
		$email = FILTRO($email);
		$busca = get_data_single("SELECT * FROM `"._SYS_['prefix_db']."users` WHERE `email` = '$email'");
		if($busca){
			return $busca;
		}else{
			return false;
		}
	}


	function check_user($cpf,$email){
		$cpf = FILTRO($cpf);
		$email = FILTRO($email);
		$busca = get_data_single("SELECT * FROM `"._SYS_['prefix_db']."users` WHERE `cpf` = '$cpf' OR `email` = '$email'");
		if($busca){
			return true;
		}else{
			return false;
		}
	}


	function alterar_senha($key_user,$senha){
		$key_user = FILTRO($key_user);
		$senha = FILTRO_senha($senha);
		$u = $this->dados_usuario($key_user);
		$DB['senha'] = $senha;

		if(updateDB('users',$DB,$u['hash'])){
			return true;
		}else{
			return false;
		}
	}


	function alterar_email($key_user,$email){
		$key_user = FILTRO($key_user);
		$busca = $this->dados_usuario($key_user);
		$email = FILTRO($email);
		if($busca){
			$DB['email'] = $email;
			return updateDB('users',$DB,$busca['hash']);
		}else{
			return false;
		}
	}


	function consultar_cpf($CPF){
		$CPF = FILTRO($CPF);
		$busca = get_data_single("SELECT * FROM `"._SYS_['prefix_db']."users` WHERE `cpf` = '$CPF'");
		if($busca){
			return $busca;
		}else{
			return false;
		}
	}


	function esqueci_minha_senha($user){
		$DB = array(
			"cod"=>time().rand(1001,9999),
			"hash"=>set_token(),
			"data_exp"=> date("Y-m-d H:i:s",strtotime("+2 hours")),
			"status"=>"Ativo",
			"user_key"=>$user
		);
		$busca = $this->dados_usuario($user);
		if($busca){
			query("UPDATE `"._SYS_['prefix_db']."code_pass` SET `status` = 'Expirado' WHERE `user_key` = '$user'");
			if(insertDB('code_pass',$DB,'data')){
				$DB['endereco'] = ENDERECO_SITE;
				if(send_email_notif_html('esqueciMinhaSenha','esqueciMinhaSenha',$busca['email'],$DB)){
					return true;
				}else{
					query("UPDATE `"._SYS_['prefix_db']."code_pass` SET `status` = 'Expirado' WHERE `user_key` = '$user'");
					return false;
				}
			}else{
				return false;
			}
		}else{
			return false;
		}
	}


	function check_cod_pass($KEY){
		$KEY = FILTRO($KEY);
		$busca = get_data_single("SELECT * FROM `"._SYS_['prefix_db']."code_pass` WHERE `hash` = '$KEY' AND `status` = 'Ativo'");
		if($busca){
			return $busca;
		}else{
			return false;
		}
	}


	function redefinir_senha($senha,$key){
		$key = FILTRO($key);
		$cod = $this->check_cod_pass($key);
		if($cod){
			if($this->alterar_senha($cod['user_key'],$senha)){
				$this->status_code_pass($key,'Utilizado');
				return true;
			}
		}else{
			return false;
		}
	}


	function status_code_pass($key,$status){
		$key = FILTRO($key);
		$status = FILTRO($status);
		$busca = get_data("SELECT * FROM `"._SYS_['prefix_db']."code_pass` WHERE `hash` = '$key'");
		if($busca){
			if(query("UPDATE `"._SYS_['prefix_db']."code_pass` SET `status` = '$status' WHERE `hash` = '$key'")){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
}
