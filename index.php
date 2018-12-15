<?php

    require 'Slim/Slim.php';
    require 'Db.php';

    \Slim\Slim::registerAutoloader();
    $app = new \Slim\Slim();
    $db = new Db;

	// login
    $app->post("/login", function() use($app, $db){
        $response = array();
        
        
        $email = $app->request->post("email");
        $pass = $app->request->post("pass");
       
        $ue = $db->query(sprintf("SELECT token FROM usuario WHERE email = '%s' AND pass = '%s'", $email, $pass));
        
        if($ue->num_rows > 0){
            
            $dados = $ue->fetch_assoc();
            
            $response['erro'] = false;
            $response['token'] = $dados["token"];
            
        } else {
            $response['erro'] = true;
            $response['mensagem'] = "Usuário/Senha incorreto(s)";
        }
        
        response(200, $response);
        
    });
	
	$app->post("/listagaleria", function() use($app,$db){
		$response = array();
		$galeria = array();
		//$ue = $db->query(sprintf("SELECT COUNT(*) as numelem FROM produto WHERE ativo ='S'"));
		 $ue = $db->query(sprintf("SELECT * FROM galeria WHERE ativo='S'"));
			if($ue->num_rows > 0)
			{ 
				while($dados = $ue->fetch_assoc())
				{

					$galeria['erro'] = false;
					$galeria['galeria'] = $dados["galeria"];
					$galeria['descricao'] = $dados["descricao"];
					$galeria['diretorio'] = $dados["diretorio"];
					array_push ( $response,$galeria);
				}
			}else{
				$response['erro'] = true;
				$response['galeria'] = "Não existem fotos para Galeria";
			}
        response(200, $response,"listaGaleria");
		 
		 
	});
	/****SECAO DE PRODUTOS***/
	//lista de 5 em 5 os produtos
    $app->post("/listarproduto", function() use($app, $db){
        $response = array();
        $produto = array();
        $pagina = $app->request->post("pagina");
        $pagina = (isset($pagina) && !empty($pagina) && intval($pagina)>0)?$pagina:"1";
		$offset = intval($pagina -1)*5;
       
        $ue = $db->query(sprintf("SELECT * FROM produto WHERE ativo='S'  LIMIT 5 OFFSET %d",$offset));
      
        while($dados = $ue->fetch_assoc()) {

            $produto['erro'] = false;
            $produto['produto'] = $dados["produto"];
            $produto['imagem'] = $dados["imagem"];
            $produto['descricao'] = $dados["descricao"];
            array_push ( $response,$produto);
        }
        if($ue->num_rows == 0){
        
            $response['erro'] = true;
            $response['produto'] = "Não existem productos";

			}
        response(200, $response,"produtos");
        
    });
	//retorna o numero de paginas dos produtos
    $app->post("/paginasprodutos", function() use($app, $db){

        $response = array();
        $ue = $db->query(sprintf("SELECT COUNT(*) as numelem FROM produto WHERE ativo ='S'"));
        if($ue->num_rows > 0)
        {
            $dados = $ue->fetch_assoc();
            $response['numpaginas'] = ceil($dados['numelem']/5);
        }else{
            $response['numpaginas'] = $dados['0'];
        }
            response(200, $response,null);

    });
	//retorna o numero de elementos dos produtos
	$app->post("/contarprodutos", function() use($app, $db){

        $response = array();
        $ue = $db->query(sprintf("SELECT COUNT(*) as numelem FROM produto WHERE ativo ='S'"));
        if($ue->num_rows > 0)
        {
            $dados = $ue->fetch_assoc();
            $response['numelementos'] = $dados['numelem'];
        }else{
            $response['numelementos'] = $dados['0'];
        }
            response(200, $response,null);

    });
	/***FIM DE SECAO DE PRODUTOS***/
	
	/**SECAO DE CONTATO**/
		
		$app->post("/contato", function() use($app, $db){
		
		
		$response = array();
		$form = array();
		
		
		//$emailsender='contato@ecoestufas.com.br';
		$emailsender = "juanpablomonterohidalgo@gmail.com";
		/* Verifica qual e o sistema operacional do servidor para ajustar o cabecalho de forma correta.  */
		if(PATH_SEPARATOR == ";") $quebra_linha = "\r\n"; //Se for Windows
		else $quebra_linha = "\n"; //Se "nao for Windows"
		$nomeremetente = $app->request->post("nome");
		$telefone = $app->request->post("telefone");
		$email = $app->request->post("email");
		$mensagem = $app->request->post("comentarios");
		$assunto = "Contato ecoestufas";
        //$emailremetente    = "contato@ecoestufas.com.br";
		$emailremetente    = "juanpablomonterohidalgo@gmail.com";
		/* Montando a mensagem a ser enviada no corpo do e-mail. */
		$mensagemHTML = '<P>Nome: '.$nomeremetente.'.<br></P>
		<P>Email para resposta: '.$email.'.<br></P>
		<P>Telefone para contato: '.$telefone.'.<br></P>
		<p>Mensagem: <b><i>'.$mensagem.'</i></b></p>
		Atenciosamente<br />'.
		$nomeremetente.
		'<hr>';
		
		/* Montando o cabecalho da mensagem */
		$headers = "MIME-Version: 1.1" .$quebra_linha;
		$headers .= "Content-type: text/html; charset=iso-8859-1" .$quebra_linha;
		// Perceba que a linha acima contém "text/html", sem essa linha, a mensagem nao chegara formatada.
		$headers .= "From: " . $emailsender.$quebra_linha;
		$headers .= "Reply-To: " . $email . $quebra_linha;
		// Note que o e-mail do remetente sera usado no campo Reply-To (Responder Para)
		 
		/* Enviando a mensagem */

		//e obrigatorio o uso do parametro -r (concatenacao do "From na linha de envio"), aqui na Locaweb:

		/*if(!mail($emailremetente, $assunto, $mensagemHTML, $headers ,"-r".$emailsender)){ // Se for Postfix
			$headers .= "Return-Path: " . $emailsender . $quebra_linha; // Se "nao for Postfix"
			$retorno = mail($emailremetente, $assunto, $mensagemHTML, $headers );
		}*/
		$retorno = true;
		// se a mensagem foi enviada com sucesso
		if ($retorno){
			
			$response['nome']=$nomeremetente;
			$response['telefone']=$telefone;
			$response['email']=$email;
			$response['comentarios']=$mensagem;
			$response['erro']=false;
			
			
		}else{
			$response['erro']=true;
		}
      
        response(200, $response,null);

    });
	
	/**FIM DE SECAO DE CONTATO**/
	
	
	
    function response($status_code, $response,$arrayName){
        $app = \Slim\Slim::getInstance();
        $app->status($status_code);
        $app->contentType("application/json");
        $formatResponse = isset($arrayName)?json_encode(array($arrayName => $response)):json_encode($response);
		echo $formatResponse;
    }


    $app->run();