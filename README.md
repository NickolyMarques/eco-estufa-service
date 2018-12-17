#eco-estufas-service - first version to service, from Slim PHP
#eco-estufas-service - first version to service, from Slim PHP


A pasta V1 precisa ficar ao mesmo nivel que as pastas de galeria e eventos (colocadas no public do react)

No index.php (invocação dos serviços)
	//root para galeria //
	$diretorioroot = "../galeria/";

Para o banner de acesso temos preparado uma pasta de eventos, o serviço mostrabanner recupera a imagem que este na pasta ativo,
se tiver mais de uma recupera alfatebeticamente.
@Todo fazer o html para mostrar o banner.
	//root para banner //
	$directoriobanner = "../eventos/active/";
