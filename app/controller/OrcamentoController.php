<?php 

/**
 * <b>OrcamentController</b>:
 * Classe extendida de controlller,
 * Responsável por executar ações genéricas relacionadas aos orçamentos e 
 * fornecer métodos para montagem da view dinamicamnte.
 * @author Weydans Campos de Barros, 18/04/2019.
 */
class OrcamentoController extends Controller 
{
	protected static $linkHome = HOME;

	protected $conteudoPrincipal = array();
	protected $formAction;
	protected $action;

	/**
	 * <b>excluir</b>:
	 * Realiza exclusões de orçamentos do sistema e 
	 * redireciona para página de listagem de orçamentos.
	 * @param string $id Recebe o id do registro a ser Excluído.
	 */
	public function excluir(string $id)
	{
		$page = filter_input(INPUT_GET, 'page', FILTER_DEFAULT);
		$uri = filter_input(INPUT_GET, 'uri', FILTER_DEFAULT);
		$id = filter_var($id, FILTER_DEFAULT);

		$paramBusca = filter_input_array(INPUT_GET, FILTER_DEFAULT); 

		if (isset($paramBusca['page'])){
			unset($paramBusca['page']);
		}

		if (isset($paramBusca['uri'])){
			unset($paramBusca['uri']);
		}

		$paramBusca = '&' . http_build_query($paramBusca);

		// IDENTIFICA SE REGISTRO EXISTE NO BANCO
		if (is_numeric($id)){
			$orcamento = new Orcamento;
			$arrOrcamento = $orcamento->find($id);

			// EXECUTA EXCLUSÃO
			if (is_array($arrOrcamento)){
				$resDelete = $orcamento->delete($id);

				if ($resDelete === true){
					header("Location: " . self::$linkHome . $uri . "?page={$page}&delete={$arrOrcamento['orcamento_cliente']}{$paramBusca}");

				} else {
					header('Location: ' . self::$linkHome . $uri . "?page={$page}&failDelete={$resDelete}{$paramBusca}");
				}

			} else {
				header('Location: ' . self::$linkHome . $uri . "?page={$page}&failDelete=Informe um orçamento cadastrado no sistema{$paramBusca}");
			}

		} else {
			header('Location: ' . self::$linkHome . $uri . "?page={$page}&failDelete=Informe um valor válido para exclusão{$paramBusca}");
		}
	}


	/**
	 * <b>setConteudoPrincipal</b>:
	 * Substitui os links de uma determinada view informada dinamicamnete.
	 * @param string $caminhoArquivo Caminho do arquivo de view.
	 * @param array $data Dados que vão substituir os links da view; 
	 */
	protected function setConteudoPrincipal(string $caminhoArquivo, array $data)
	{
		$conteudoPrincipal = $this->getPreparedView($caminhoArquivo, $data); 
		$this->conteudoPrincipal = array('conteudoPrincipal' => $conteudoPrincipal);

		$this->conteudoPrincipal['<div class="msg"></div>'] = $this->msg;
		$this->conteudoPrincipal['action'] = $this->action;		
		$this->conteudoPrincipal['formAction'] = $this->formAction;		
	}

	/**
	 * <b>show</b>
	 * Exibe a view completa para o usuário.
	 */
	protected function show()
	{
		echo $this->getFinalView();
	}


	/**
	 * <b>validaCliente</b>
	 * Valida se dados informados pelo usúario são válidos.
	 * @param string $cliente Recebe o valor informado no campo cliente.
	 * @return bool Retorna true caso dados sejam válidos.
	 */
	protected function validaCliente(string $cliente) : bool
	{
		if (strlen($cliente) > 45 || strlen($cliente) < 5){
			$this->msg = Msg::setMsg('O campo <b>cliente</b> deve ter entre 5 e 45 caracteres.', ERROR);
			return false;			
		} else {
			return true;
		}
	}

	/**
	 * <b>validaVendedor</b>
	 * Valida se dados informados pelo usúario são válidos.
	 * @param string $vendedor Recebe o valor informado no campo vendedor.
	 * @return bool Retorna true caso dados sejam válidos.
	 */
	protected function validaVendedor(string $vendedor)
	{
		if (strlen($vendedor) > 45 || strlen($vendedor) < 5){
			$this->msg = Msg::setMsg('O campo <b>vendedor</b> deve ter entre 5 e 45 caracteres.', ERROR);
			return false;			
		} else {
			return true;
		}
	}

	/**
	 * <b>validaDescricao</b>
	 * Valida se dados informados pelo usúario são válidos.
	 * @param string $descricao Recebe o valor informado no campo descricao.
	 * @return bool Retorna true caso dados sejam válidos.
	 */
	protected function validaDescricao(string $descricao) : bool
	{
		if (strlen($descricao) > 255 || strlen($descricao) < 10){
			$this->msg = Msg::setMsg('O campo <b>descricao</b> deve ter entre 10 e 255 caracteres.', ERROR);
			return false;			
		} else {
			return true;
		}
	}

	/**
	 * <b>validaDataHora</b>:
	 * Verifiva se data e hora são válidos.
	 * @param string $validaDataHora recebe a data e hora informada pelo usuário.
	 * @return bool Retorna true caso data e hora sejão válidos.
	 */
	protected function validaDataHora(string $dataHora) : bool
	{	
		$exprecao1 = "/^[0-3]{1}[0-9]{1}\/[0-1]{1}[0-9]{1}\/[1-2]{1}[0-9]{3} [0-2]{1}[0-9]{1}\:[0-5]{1}[0-9]{1}\:[0-5]{1}[0-9]{1}$/";

		$res = preg_match($exprecao1, $dataHora);		

		if ($res === 1){
			$arrDataHora = explode(' ', $dataHora);
			$data = explode('/', $arrDataHora[0]);
			$hora = explode(':', $arrDataHora[1]);

			$validacaoData = $this->validaData($data);
			$validacaoHora = $this->validaHora($hora);

			if (!$validacaoData || !$validacaoHora){
				return false;
			} else {
				return true;
			}
		}

		$this->msg = Msg::setMsg('Vefifique se data e hora estão corretos e nos padrões especificados.', ERROR);

		return false;		
	}

	/**
	 * <b>validaData</b>:
	 * Verifica se a data é uma data válida, verifica se ano é bisexto para validar o mes de fevereiro,
	 * Verifica se os demais meses podem ou não ter mais de 30 ou 31 dias.
	 * @param array $data Recebe data a ser verificada.
	 * @return bool Retorna true caso data válida.
	 */
	protected function validaData(array $data) : bool
	{
		$anoBisexto = null;

		if ((int) $data[2] % 4 === 0){
			$anoBisexto = true;
		}

		if ($data[2] < 1000 || $data[2] > 2999){
			$this->msg = Msg::setMsg('Verifique a Data, informe o ano atual.', ERROR);
			return false;
		}

		switch ($data[1]){

			case '02':

			if ($anoBisexto && (int) $data[0] > 29){
				$this->msg = Msg::setMsg('Verifique a Data, o mês de Fevereiro não pode ter mais de 29 dias.', ERROR);
				return false;

			} elseif ((int) $data[0] > 28){
				$this->msg = Msg::setMsg('Verifique a Data, o mês de Fevereiro não pode ter mais de 28 dias.', ERROR);
				return false;
			}

			break;

			case '04':
			case '06':
			case '09':
			case '11':

			if ($data[0] > 31){
				$this->msg = Msg::setMsg('Verifique a Data, o mês informado não pode ter mais de 30 dias.', ERROR);
				return false;
			}

			break;

		}

		if ($data[0] > 31){
			$this->msg = Msg::setMsg('Verifique a Data, o mês não pode ter mais de 31 dias.', ERROR);
			return false;
		}

		if ($data[1] > 12){
			$this->msg = Msg::setMsg('Verifique a Data, informe um mês válido.', ERROR);
			return false;
		}

		return true;
	}

	/**
	 * <b>validaHora</b>:
	 * Verifica se a hora é válida.
	 * @param array $hora Recebe hora a ser verificada.
	 * @return bool Retorna true caso data válida.
	 */
	protected function validaHora(array $hora) : bool
	{
		if ($hora[0] > 23){
			$this->msg = Msg::setMsg('Verifique a Hora, seu valor não pode ser maior que 24.', ERROR);
			return false;
		}

		return true;
	}

	/**
	 * <b>validaValor</b>:
	 * Verifica se o campo valoer do formulário foi corretamente preenchido.
	 * @param string $valor Recebe o valor a ser validado.
	 * @return bool Retorna true caso dados sejam válidos.
	 */
	protected function validaValor(string $valor) : bool
	{
		$verify = false;

		$valor = str_replace(array('R', '$', '.', ',', ' '), '', $valor);

		if ($valor === ''){
			$this->msg = Msg::setMsg('O campo valordeve ser preenchido', ERROR);

		} elseif (!is_numeric($valor)){
			$this->msg = Msg::setMsg('O campo valor so aceita números, e os seguintes caracteres: "<b>R</b>", "<b>$</b>", "<b>.</b>" e "<b>,</b>"', ERROR);

		} elseif (strlen($valor) > 8){
			$this->msg = Msg::setMsg('O valor máximo para o campo campo valor é de R$ 999.999,99', ERROR);

		} elseif ((float) $valor < 1) {
			$this->msg = Msg::setMsg('Não é permitido informar valores negativos.', ERROR);
		} else {
			return true;
		}

		return $verify;
	}

}
