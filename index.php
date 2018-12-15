<?php

    require 'Slim/Slim.php';
    require 'Db.php';

    \Slim\Slim::registerAutoloader();
    $app = new \Slim\Slim();
	$db = new Db;
	//root para galeria //
	$diretorioroot = "..\\galeria\\";

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
	
	/**SECAO DE GALERIA */
	$app->post("/listagaleria", function() use($app,$db,$diretorioroot){
		$response = array();
		$galeria = array();
		 $ue = $db->query(sprintf("SELECT * FROM galeria WHERE ativo='S'"));
			if($ue->num_rows > 0)
			{ 
				while($dados = $ue->fetch_assoc())
				{

					$galeria['erro'] = false;
					$galeria['galeria'] = $dados["galeria"];
					$galeria['descricao'] = $dados["descricao"];
					$galeria['diretorio'] = $dados["diretorio"];
					/** retornamos a primeira imagem do diretorio**/
					$fileSystemIterator = new FilesystemIterator($diretorioroot.$dados["diretorio"]);
					$entries = array();
					foreach ($fileSystemIterator as $fileInfo){	
						list($width, $height)=getimagesize($diretorioroot.$dados["diretorio"]."\\".$fileInfo->getFilename());
						$galeria['imagemPrincipal'] = $fileInfo->getFilename();
						$galeria['width'] = $width;
						$galeria['height'] = $height; 
						break;
					}
					
					array_push ( $response,$galeria);
				}
			}else{
				$response['erro'] = true;
				$response['galeria'] = "Não existem fotos para Galeria";
			}
        response(200, $response,"listaGaleria");
		 
		 
	});
	$app->post("/imagemdirectorio", function() use($app,$diretorioroot){
		$response = array();
		$galeria = array();
		$diretorio  = $app->request->post("diretorio");
		$diretorio = isset($diretorio)?$diretorio:"diretoriovacio";
		//$diretorio = isset($app->request->post("diretorio"))?$app->request->post("diretorio"):"diretoriovacio";
					
		/** retornamos a primeira imagem do diretorio**/
		try{
			$fileSystemIterator = new FilesystemIterator($diretorioroot.$diretorio);
			$entries = array();
			foreach ($fileSystemIterator as $fileInfo){
				list($width, $height)=getimagesize($diretorioroot.$diretorio."\\".$fileInfo->getFilename());
				$galeria['erro'] = false;
				$galeria['imagem'] = $fileInfo->getFilename();
				$galeria['width'] = $width;
				$galeria['height'] = $height; 
				array_push ( $response,$galeria);
			}
		}catch(UnexpectedValueException $e){
			$galeria['erro'] = true;
			$galeria['imagem'] = '';
			array_push ( $response,$galeria);
		}

        response(200, $response,"listaImagems");
		 
		 
	});



	/**FIM DE SECAO DE GALERIA */
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
	
	/**ENVIO DE EMAIL**/
		$app->post("/contato", function() use($app){
			$response = array();
			$retorno = createEmail($app);
			// se a mensagem foi enviada com sucesso
			if ($retorno){
				$response['erro']=false;
			}else{
				$response['erro']=true;
			}
			response(200, $response,null);

	    });
	
	/**FIM DE ENVIO DE EMAIL**/
	
	
	
    function response($status_code, $response,$arrayName){
        $app = \Slim\Slim::getInstance();
        $app->status($status_code);
        $app->contentType("application/json");
        $formatResponse = isset($arrayName)?json_encode(array($arrayName => $response)):json_encode($response);
		echo $formatResponse;
    }

	function createEmail($app){
		$retorno = false;
		//$emailsender='contato@ecoestufas.com.br';
		$emailsender = "juanpablomonterohidalgo@gmail.com";
		//$emailremetente    = "contato@ecoestufas.com.br";
		$emailremetente    = "juanpablomonterohidalgo@gmail.com";
		$mensagemHTML = "";
		/* Verifica qual e o sistema operacional do servidor para ajustar o cabecalho de forma correta.  */
		if(PATH_SEPARATOR == ";") $quebra_linha = "\r\n"; //Se for Windows
		else $quebra_linha = "\n"; //Se "nao for Windows"

		$formularioContato = $app->request->post("metodo");
		
		if($formularioContato === "contato"){
			// contato
			
			$nomeremetente = $app->request->post("nome");
			$telefone = $app->request->post("telefone");
			$email = $app->request->post("email");
			$mensagem = $app->request->post("comentarios");
			$assunto = "Contato ecoestufas";
			
			/* Montando a mensagem a ser enviada no corpo do e-mail. */
			$mensagemHTML = '<P>Nome: '.$nomeremetente.'.<br></P>
			<P>Email para resposta: '.$email.'.<br></P>
			<P>Telefone para contato: '.$telefone.'.<br></P>
			<p>Mensagem: <b><i>'.$mensagem.'</i></b></p>
			Atenciosamente<br />'.
			$nomeremetente.
			'<hr>';

		}else{
			//orcamento
			$nomeremetente=$app->request->post("nome");
			$telefone = $app->request->post("telefone");
			$email = $app->request->post("email");
			$assunto = $app->request->post("assunto");
			$empresa = $app->request->post("empresa");
			$cidade = $app->request->post("cidade");
			$finalidade = $app->request->post("finalidade");
			$largura = $app->request->post("largura");
			$comprimento = $app->request->post("comprimento");
			$alturalateral = $app->request->post("alturaLateral");

			/* Montando a mensagem a ser enviada no corpo do e-mail. */
			$mensagemHTML = '<P>Nome: '.$nomeremetente.'.<br></P>
			<P>Email para resposta: '.$email.'.<br></P>
			<P>Telefone para contato: '.$telefone.'.<br></P>
			<p>Assunto: <b><i>'.$assunto.'</i></b></p>
			<P>Empresa: '.$empresa.'.<br></P>
			<P>Cidade: '.$cidade.'.<br></P>
			<P>Finalidade: '.$finalidade.'.<br></P>
			<P>Largura: '.$largura.'.<br></P>
			<P>Comprimento: '.$comprimento.'.<br></P>
			<P>Altura Lateral: '.$alturaLateral.'.<br></P>

			Atenciosamente<br />'.
			$nomeremetente.
			'<hr>';

		}

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
		
		return $retorno;
	}

    $app->run();