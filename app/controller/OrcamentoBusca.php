<?php 

/**
 * <b>OrcamentoBusca</b>:
 * Classe extendida de OrcamentoController
 * Responsável por executar ações de busca com filtragem, listagem e exclusão. 
 * Obtem e monta a view dinamicamnte.
 * @author Weydans Campos de Barros, 20/04/2019.
 */
class OrcamentoBusca extends OrcamentoController 
{
	private $dadosList = array();
	private $dadosTabela;
	private $dadosBusca;
	private $paginator;
	private $listAll;

	/**
	 * <b>buscar</b>
	 * Verifica, gerencia e valida os dados da busca, exibe a view.
	 */
	public function buscar()
	{		
		$this->msg = '';
		$pageBusca = array();

		$this->listAll = filter_input(INPUT_GET, 'all', FILTER_VALIDATE_BOOLEAN);
		$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
		$delete = filter_input(INPUT_GET, 'delete', FILTER_DEFAULT);
		$dadosForm = filter_input_array(INPUT_GET, FILTER_DEFAULT);

		if (isset($dadosForm['page'])){
			unset($dadosForm['page']);
		}

		if (isset($dadosForm['delete'])){
			unset($dadosForm['delete']);
		}

		// LISTA TODOS OS ORÇAMENTOS CASO ( $this->listAll === true )
		if ($this->listAll === true){
			$this->clearFormBusca();

			$pageBusca = $this->exeBusca($page, $delete, $this->dadosForm);
			$totalOrcamentos = $this->paginator->getTotalResults();

			if (!empty($delete)){
				$this->msg =  Msg::setMsg("Orçamento do(a) cliente <b>{$delete}</b> removido com sucesso.", ACCEPT) . 
				'<br/>' .
				Msg::setMsg("O total de orçamentos cadastrados é de <b>{$totalOrcamentos}</b>.", INFOR);

			} else{
				$this->msg =  Msg::setMsg("O total de orcamentos cadastrados é de <b>{$totalOrcamentos}</b>.", INFOR);
			}

		} elseif (!$page && empty($dadosForm)){
			$this->clearFormBusca();
			$pageBusca['conteudoPrincipal'] = Render::show('./app/view/form-busca.html', $this->dadosForm);

		} elseif (!empty($page) || !empty($dadosForm)){

			if (empty($dadosForm['orcamento_busca'])){
				$this->msg = Msg::setMsg('Preencha o campo de de pesquisa.', ERROR);
				$pageBusca['conteudoPrincipal'] = Render::show('./app/view/form-busca.html', $dadosForm);

			// REALIZA BUSCA PERSONALIZADA POR ORÇAMENTOS
			} elseif (!empty($dadosForm['orcamento_busca'])){ 
				if($this->validarDadosFormBusca($dadosForm)){				
					$pageBusca = $this->exeBusca($page, $delete, $dadosForm, $pageBusca);

				} else {
					$pageBusca['conteudoPrincipal'] = Render::show('./app/view/form-busca.html', $dadosForm);
				}
			}

		}

		$pageBusca['<div class="msg"></div>'] = $this->msg;
		$pageBusca['home'] = self::$linkHome;

		$this->setHeader('./app/view/header.html', array('home' => self::$linkHome));
		$this->setContent('./app/view/tela-principal.html', $pageBusca);
		$this->setFooter('./app/view/footer.html');

		$this->show();
	}


	/**
	 * <b>exeBusca</b>
	 * Executa a busca no banco de dados dinamicamente conforme 
	 * dados recebidos no formulário de busca.
	 * @param string $page Número da página na paginação.
	 * @param string $delete Nome do cliente caso tensido realizada uma exclusão.
	 * @param array $dadosForm contendo os dados informados no formulário.
	 * @return string 
	 */
	private function exeBusca($page, $delete, $dadosForm)
	{
		$dados = array();

		foreach ($dadosForm as $key => $value) {
			if (!empty($value) && $value !== $dadosForm['orcamento_busca']){
				$dados[$key] = $value;
			}
		}

		if (!empty($dados['filtro_data_inicial'])){
			$dataCorrigidaInicio = Change::brlToTimestamp($dados['filtro_data_inicial']);
			$dados['filtro_data_inicial'] = date('Y-m-d H:i:s', strtotime($dataCorrigidaInicio));				
		}

		if (!empty($dados['filtro_data_final'])){
			$dataCorrigidaFim = Change::brlToTimestamp($dados['filtro_data_final']);
			$dados['filtro_data_final'] = date('Y-m-d H:i:s', strtotime($dataCorrigidaFim));				
		}

		$orcamento = new Orcamento;
		$paramBusca = $this->prepareParamBusca($_SERVER['QUERY_STRING']);

		$condicao = $this->getCondicaoBusca($dadosForm);
		$ordenacao = "ORDER BY orcamento_id";

		if ($this->listAll === true){
			// LISTA TODOS OS ORÇAMENTOS E REALIZA PAGINAÇÃO
			$this->paginator = new Paginator(HOME . '/busca?page=', $page, $orcamento, 3, $paramBusca);
			$buscaResults = $this->paginator->exePaginator(array(), '', array(), $ordenacao);
			$paginador = $this->paginator->getPaginator('Primeira', 'Última', 5);


		} else {			
			// REALIZA BUSCA E PAGINAÇÃO
			$this->paginator = new Paginator(HOME . '/busca?page=', $page, $orcamento, 3, $paramBusca);
			$buscaResults = $this->paginator->exeBusca(array(), $condicao, $dados, $ordenacao); 
			$paginador = $this->paginator->getPaginator('Primeira', 'Última', 5);
		}

		$item = '';
		$i = 0;
		if (is_array($buscaResults) && !empty($buscaResults)){
			foreach ($buscaResults as $key => $value) {
				$i % 2 == 0 ? $bgLine = 'par' : $bgLine = 'impar';

				// SUBSTITUI LINKS DA VIEW
				$value['bg-line'] 			  = $bgLine;
				$value['home'] 				  = self::$linkHome;
				$value['uri'] 				  = $_SERVER['REDIRECT_URL']; 
				$value['page']				  = $page;
				$value['all'] 				  = $this->listAll;
				$value['busca'] 			  = '&' . http_build_query($dadosForm);
				$value['filtro_data_inicial'] = $dadosForm['filtro_data_inicial'];
				$value['filtro_data_final']   = $dadosForm['filtro_data_final'];
				$value['orcamento_data_hora'] = date('d/m/Y H:i:s', strtotime($value['orcamento_data_hora']));
				$value['orcamento_valor']     = 'R$ ' .  number_format( ($value['orcamento_valor'] / 100) , 2, ',', '.');

				// CARREGA RESULTADOS NA TABELA
				$item .= Render::show('./app/view/resultado-busca-orcamento.html', $value); 	

				$i++;
			}

			$this->dadosList['resultadoBuscaOrcamento'] = $item;
			$this->dadosList['paginador'] = $paginador; 

			$pageBusca['conteudoPrincipal'] = Render::show('./app/view/form-busca.html', $dadosForm);
			$tabela = Render::show('./app/view/tabela-orcamentos.html', $this->dadosList);
			$pageBusca['conteudoPrincipal'] .= $tabela;
			$totalResults = $this->paginator->getTotalResults();

			if (empty($delete)){
				$this->msg = Msg::setMsg("Sua busca retornou <b>{$totalResults}</b> resultado(s)", ACCEPT);

			} else {
				$this->msg = Msg::setMsg("Orcamento do(a) cliente <b>{$delete}</b> removido com sucesso.", ACCEPT);
			}

			// RETORNA VIEW PRINCIPAL DE EXIBIÇÃO
			return $pageBusca;

		} elseif (is_array($buscaResults) && empty($buscaResults)){
			$this->msg = Msg::setMsg('Desculpe! Não existem resultados para esta busca.', ERROR);
			$this->dadosList['resultadoBuscaOrcamento'] = '';

			$pageBusca['conteudoPrincipal'] = Render::show('./app/view/form-busca.html', $dadosForm);			
			return $pageBusca; 

		} else {
			$this->msg = Msg::setMsg($buscaResults, ERROR);
		}
	}


	/**
	 * <b>getDadosPesquisa</b>:
	 * Obtem os dados informados no formulário de busca.
	 * @param $paramBusca recebe a querystring da pagina.
	 * @return array Retorna os dados informados no formulário.
	 */
	private function getDadosPesquisa($paramBusca) : array
	{
		if (!empty($paramBusca)){
			$paramBusca =  explode('&', $paramBusca);
			$arrParamBusca = array();

			foreach ($paramBusca as $key => $value) {
				if (strstr($value, 'page=')){
					unset($paramBusca[$key]);
					continue;
				}	

				$arrays = explode('=', $value); 
				$arrParamBusca[$arrays[0]] = $arrays[1];		
			}
			return $arrParamBusca;

		} else {
			return array();
		}
	}


	/**
	 * <b>prepareParamBusca</b>:
	 * Configura os parametros de busca para os links de paginação.
	 * @param mixed $param Querystring contendo os dados informados no formulário.
	 * @return string Retorna uma Querystring sem o parametro page;
	 */
	private function prepareParamBusca($paramBusca) : string
	{ 
		$paramBusca =  explode('&', $paramBusca);

		foreach ($paramBusca as $key => $value) {
			if (strstr($value, 'page=')){
				unset($paramBusca[$key]);
			}
		}

		$paramBusca = '&' . implode('&', $paramBusca);

		return $paramBusca;
	}


	/**
	 * <b>validarDadosFormBusca</b>
	 * Verifica se todos os dados infomados pelo usuário são válidos.
	 * @param array $dadosForm Array contendo os dados a serem validados.
	 * @return bool Retorna true caso dados sejam válidos.
	 */
	private function validarDadosFormBusca($dadosForm) : bool
	{	
		if (!empty($dadosForm['filtro_data_inicial'])){
			$res = $this->validaDataHora($dadosForm['filtro_data_inicial']);
			if ( $res === false ) return $res; 
		}

		if (!empty($dadosForm['filtro_data_final'])){
			$res = $this->validaDataHora($dadosForm['filtro_data_final']);
			if ( $res === false ) return $res; 
		}

		if (!empty($dadosForm['orcamento_cliente'])){
			$res = $this->validaCliente($dadosForm['orcamento_cliente']);
			if ( $res === false ) return $res; 
		}

		if (!empty($dadosForm['orcamento_vendedor'])){
			$res = $this->validavendedor($dadosForm['orcamento_vendedor']);
			if ( $res === false ) return $res; 
		}

		return true;
	}


	/**
	 * <b>getCondicaoBusca</b>:
	 * Obtem dinamicamente a condição de busca.
	 * @param array $dadosForm Array com os dados para obter a condição.
	 * @return string Retorna uma string com a condição obtida dinamicamente.
	 */
	private function getCondicaoBusca($dadosForm) : string
	{		
		$dados = array();

		$condicao = 'WHERE ';
		$preCondicao = " LIKE '%{$dadosForm['orcamento_busca']}%'";

		foreach ($dadosForm as $key => $value) {
			if (!empty($value) && $value !== $dadosForm['orcamento_busca']){
				$dados[$key] = $value;

				switch ($key){
					case 'filtro_data_inicial':
					$preCondicao .= " AND orcamento_data_hora >= :{$key}";
					break;

					case 'filtro_data_final':
					$preCondicao .= " AND orcamento_data_hora <= :{$key}";
					break;

					case 'orcamento_cliente':
					$preCondicao .= " AND orcamento_cliente = :{$key}";
					break;

					case 'orcamento_vendedor':
					$preCondicao .= " AND orcamento_vendedor = :{$key}";
					break;
				}
			}
		}

		$colunas = Orcamento::getColumns();
		unset($colunas[0]);
		array_pop($colunas);

		$arrPreCondicao = array();

		for ($i=1; $i <= count($colunas); $i++) { 
			$arrPreCondicao[$i-1] = $colunas[$i] . $preCondicao;
		}

		$condicao .= implode(' OR ', $arrPreCondicao);

		return $condicao;
	}

	/**
	 * <b>clearFormBusca</b>
	 * Limpa todos os campos do formulário.
	 */
	private function clearFormBusca()
	{
		$this->dadosForm = [
			'orcamento_busca' => '',
			'filtro_data_inicial' => '',
			'filtro_data_final' => '',
			'orcamento_cliente' => '',
			'orcamento_vendedor' => ''
		];
	}

}
