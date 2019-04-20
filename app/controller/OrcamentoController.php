<?php 

/**
 * <b>OrcamentController</b>:
 * Classe extendida de controlller
 * Responsável por executar ações relacionadas aos orçãmentos e montar a view dinamicamnte.
 * @author Weydans Campos de Barros
 */
class OrcamentoController extends Controller 
{
	private static $linkHome = HOME;

	private $dadosList = array();
	private $dadosForm = array();
	private $dadosTabela;
	private $dadosBusca;

	private $conteudoPrincipal;
	private $formAction;
	private $action;

	/**
	 * <b>pageForm</b>:
	 * Exibe a página de cadastro com os campos do formulario vazios.
	 */
	public function pageForm()
	{
		$this->setHeader('./app/view/header.html', array('home' => self::$linkHome));

		$this->msg = '';
		$this->formAction = HOME . '/cadastro';
		$this->action = 'Cadastrar';

		$this->clearForm();

		$this->setConteudoPrincipal('./app/view/form-cad-orcamento.html', $this->dadosForm);
		$this->setContent('./app/view/tela-principal.html', $this->conteudoPrincipal);
		$this->setFooter('./app/view/footer.html');

		$this->show();
	}

	/**
	 * <b>cadastrar</b>:
	 * Realiza cadastro de orçamentos no sistema.
	 * Caso cadastro com sucesso redireciona para página de edição.
	 */
	public function cadastrar()
	{
		$this->setHeader('./app/view/header.html', array('home' => self::$linkHome));
		$this->formAction = HOME . '/cadastro';
		$this->action = 'Cadastrar';

		$this->dadosForm = filter_input_array(INPUT_POST, FILTER_DEFAULT);

		$resVerif = $this->validarDadosForm();

		if ($resVerif){
			$orcamento = new Orcamento;

			$resId = $orcamento->nextId();

			if (is_numeric($resId)){
				$dataCorrigida = Change::brlToTimestamp($this->dadosForm['orcamento_data_hora']);

				$this->dadosForm['orcamento_id'] = $resId;
				$this->dadosForm['orcamento_data_hora'] = date('Y-m-d H:i:s', strtotime($dataCorrigida));
				$this->dadosForm['orcamento_valor'] = str_replace( array('R', '$', '.', ',', ' '), '', $this->dadosForm['orcamento_valor']);

				$resCad = $orcamento->save($this->dadosForm);

				if ($resCad === true){
					$this->msg = Msg::setMsg("Orçamento do cliente <b>{$this->dadosForm['orcamento_cliente']}</b> cadastrado com sucesso.", ACCEPT);
					header('Location: ' . HOME . "/{$resId}/atualizacao?cadastro=true");

				} else {
					$this->msg = Msg::setMsg($resCad, ERROR);
				}

			} else {
				$this->msg = Msg::setMsg($resId, ERROR);
			}
		}

		$this->setConteudoPrincipal('./app/view/form-cad-orcamento.html', $this->dadosForm);
		$this->setContent('./app/view/tela-principal.html', $this->conteudoPrincipal);
		$this->setFooter('./app/view/footer.html');

		$this->show();

	}

	/**
	 * <b>editar</b>
	 * Exibe formulário de edição com os dados de um determinado orçamento carregados,
	 * atualiza dados dados do orçamento e emite mensagens ao usuário.
	 * @param string $id Id do orçamento a ser editado.
	 */
	public function editar(string $id = null)
	{
		$this->setHeader('./app/view/header.html', array('home' => self::$linkHome));

		$this->formAction = HOME . "/atualizacao";
		$this->action = 'Atualizar';

		$id = filter_var($id, FILTER_DEFAULT);

		$cadastro = filter_input(INPUT_GET, 'cadastro', FILTER_VALIDATE_BOOLEAN);
		$atualizacao = filter_input(INPUT_GET, 'atualizacao', FILTER_VALIDATE_BOOLEAN);

		$this->dadosForm = filter_input_array(INPUT_POST, FILTER_DEFAULT);

		if (is_numeric($this->dadosForm['orcamento_id'])){
			$resVerif = $this->validarDadosForm();

			if ($resVerif === true){
				$dataCorrigida = Change::brlToTimestamp($this->dadosForm['orcamento_data_hora']);
				$this->dadosForm['orcamento_data_hora'] = date('Y-m-d H:i:s', strtotime($dataCorrigida));
				$this->dadosForm['orcamento_valor'] = str_replace( array('R', '$', '.', ',', ' '), '', $this->dadosForm['orcamento_valor']);

				$orcamento = new Orcamento;
				$resUpdate = $orcamento->update($this->dadosForm);

				if ($resUpdate === true){
					header('Location: ' . HOME . "/{$this->dadosForm['orcamento_id']}/atualizacao?atualizacao=true");

				} else {
					$this->msg = Msg::setMsg($resUpdate, ERROR);
				}
			}

		} elseif (is_numeric($id)){
			$orcamento = new Orcamento;			
			$this->dadosForm = $orcamento->find((int)$id);

			if (is_array($this->dadosForm)) {
				if ($cadastro === true){
					$this->msg = Msg::setMsg("Orçamento do(a) cliente <b>{$this->dadosForm['orcamento_cliente']}</b> cadastrado com sucesso.", ACCEPT);

				} elseif ($atualizacao === true){
					$this->msg = Msg::setMsg("Orçamento do(a) cliente <b>{$this->dadosForm['orcamento_cliente']}</b> atualizado com sucesso.", ACCEPT);
				}

				$this->dadosForm['orcamento_data_hora'] =  date('d/m/Y H:i:s', strtotime($this->dadosForm['orcamento_data_hora']));
				$this->dadosForm['orcamento_valor'] = 'R$ ' .  number_format( ($this->dadosForm['orcamento_valor']/100) , 2, ',', '.');

			} else {
				$this->msg = Msg::setMsg('Informe um registro válido para atualizar.', ERROR);
				$this->clearForm();
			}

		} else {
			$this->msg = Msg::setMsg('Informe um id válido para atualizar.', ERROR);
			$this->clearForm();
		}

		$this->setConteudoPrincipal('./app/view/form-cad-orcamento.html', $this->dadosForm);
		$this->setContent('./app/view/tela-principal.html', $this->conteudoPrincipal);
		$this->setFooter('./app/view/footer.html');

		$this->show();
	}

	/**
	 * <b>listar</b>:
	 * Lista todos os orçamentos cadastrados e exibe em forma de tabela
	 * Realiza paginação dos registros existentes 	 *
	 */
	public function listar()
	{
		$this->setHeader('./app/view/header.html', array('home' => self::$linkHome));
		$this->msg = '';

		$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
		$delete = filter_input(INPUT_GET, 'delete', FILTER_DEFAULT);
		$failDelete = filter_input(INPUT_GET, 'failDelete', FILTER_DEFAULT);

		if (!empty($delete)){
			$this->msg = Msg::setMsg("Orcamento do(a) cliente <b>{$delete}</b> removido com sucesso.", ACCEPT);
		}

		if (!empty($failDelete)){
			$this->msg = Msg::setMsg($failDelete, ERROR);
		}

		if (!$page || $page < 0){
			header('Location: http://oficina2.0.com/lista?page=1');
		}

		$orcamento = new Orcamento;

		// OBTEM RESULTADOS DINAMICAMENTE E REALIZA PAGINAÇÃO
		$paginator = new Paginator('http://oficina2.0.com/lista?page=', $page, $orcamento, 8);
		$itensLista = $paginator->exePaginator();
		$paginador = $paginator->getPaginator('Primeira', 'Última', 5);

		$item = '';
		$i = 0;
		if (is_array($itensLista) && !empty($itensLista)){
			foreach ($itensLista as $key => $value) {
				$i % 2 == 0 ? $bgLine = 'par' : $bgLine = 'impar';

				$value['bg-line'] = $bgLine;
				$value['home'] = self::$linkHome;
				$value['orcamento_data_hora'] = date('d/m/Y H:i:s', strtotime($value['orcamento_data_hora']));
				$value['orcamento_valor'] = 'R$ ' .  number_format( ($value['orcamento_valor']/100) , 2, ',', '.');

				// RENDERIZA RESULTADOS
				$item .= Render::show('./app/view/resultado-busca-orcamento.html', $value); 	

				$i++;
			}

			$this->dadosList['resultadoBuscaOrcamento'] = $item;
			$this->dadosList['paginador'] = $paginador;

		} elseif (is_array($itensLista) && empty($itensLista)){
			$this->msg = Msg::setMsg('Desculpe! Ainda não existem orçamentos cadastrados.', ERROR);
			$this->dadosList['resultadoBuscaOrcamento'] = '';

		} else {
			$this->msg = Msg::setMsg($itensLista, ERROR);
		}

		$this->setConteudoPrincipal('./app/view/tabela-orcamentos.html', $this->dadosList);
		$this->setContent('./app/view/tela-principal.html', $this->conteudoPrincipal);
		$this->setFooter('./app/view/footer.html');

		$this->show();
	}

	/**
	 * <b>excluir</b>:
	 * Realiza exclusões de orçamentos do sistema e 
	 * redireciona para página de listagem de orçamentos.
	 * @param string $id Recebe o id do registro a ser Excluído.
	 */
	public function excluir(string $id)
	{
		$id = filter_var($id, FILTER_DEFAULT);

		if (is_numeric($id)){
			$orcamento = new Orcamento;
			$arrOrcamento = $orcamento->find($id);

			if (is_array($arrOrcamento)){
				$resDelete = $orcamento->delete($id);

				if ($resDelete === true){
					header('Location: ' . self::$linkHome . '/lista?delete=' . $arrOrcamento['orcamento_cliente']);

				} else {
					header('Location: ' . self::$linkHome . '/lista?failDelete=' . $resDelete);
				}

			} else {
				header('Location: ' . self::$linkHome . '/lista?failDelete=Informe um orçamento cadastrado no sistema.' );
			}

		} else {
			header('Location: ' . self::$linkHome . '/lista?failDelete=Informe um valor válido para exclusão.');
		}
	}

	/**
	 * <b>setConteudoPrincipal</b>:
	 * Substitui os links de uma determinada view informada dinamicamnete.
	 * @param string $caminhoArquivo Caminho do arquivo de view.
	 * @param array $data Dados que vão substituir os links da view; 
	 */
	private function setConteudoPrincipal(string $caminhoArquivo, array $data)
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
	private function show()
	{
		echo $this->getFinalView();
	}

	/**
	 * <b>clearForm</b>
	 * Limpa todos os campos do formulário.
	 */
	private function clearForm()
	{
		$this->dadosForm = [
			'orcamento_id' => '',
			'orcamento_cliente' => '',
			'orcamento_data_hora' => '',
			'orcamento_vendedor' => '',
			'orcamento_descricao' => '',
			'orcamento_valor' => ''
		];
	}

	/**
	 * <b>validarDadosForm</b>:
	 * Verifica se todos os dados infomados pelo usuário são válidos.
	 * @return bool Retorna true caso dados sejam válidos.
	 */
	private function validarDadosForm()
	{
		$verify = false;

		if (strlen($this->dadosForm['orcamento_cliente']) > 45 || strlen($this->dadosForm['orcamento_cliente']) < 5){
			$this->msg = Msg::setMsg('O campo <b>Cliente</b> deve ter entre 5 e 45 caracteres.', ERROR);
			
		} elseif (!$this->validaDataHora($this->dadosForm['orcamento_data_hora'])){
			$this->validaDataHora($this->dadosForm['orcamento_data_hora']);

		} elseif (strlen($this->dadosForm['orcamento_vendedor']) > 45 || strlen($this->dadosForm['orcamento_vendedor']) < 5){
			$this->msg = Msg::setMsg('O campo <b>Vendedor</b> deve ter entre 5 e 45 caracteres.', ERROR);

		} elseif (strlen($this->dadosForm['orcamento_descricao']) > 255 || strlen($this->dadosForm['orcamento_descricao']) < 10){
			$this->msg = Msg::setMsg('O campo <b>Descrição</b> deve ter entre 10 e 255 caracteres.', ERROR);

		} elseif (!$this->validaValor($this->dadosForm['orcamento_valor'])) {
			$this->validaValor($this->dadosForm['orcamento_valor']);
			
		} else {
			$verify = true;
		}

		return $verify;
	}

	/**
	 * <b>validaDataHora</b>:
	 * Verifiva se data e hora são válidos.
	 * @param string $validaDataHora recebe a data e hora informada pelo usuário.
	 * @return bool Retorna true caso data e hora sejão válidos.
	 */
	private function validaDataHora(string $dataHora)
	{	
		$exprecao1 = "/^[0-3]{1}[0-9]{1}\/[0-1]{1}[0-9]{1}\/[0-2]{1}[0-9]{3} [0-2]{1}[0-9]{1}\:[0-5]{1}[0-9]{1}\:[0-5]{1}[0-9]{1}$/";

		$res = preg_match($exprecao1, $dataHora);		

		if ($res === 1){
			$arrDataHora = explode(' ', $dataHora);
			$data = explode('/', $arrDataHora[0]);

			$validacaoData = $this->validaData($data);

			if ($validacaoData){
				return true;
			}

		}

		$this->msg = Msg::setMsg('Vefifique se a data e a hora estão nos padrões especificados.', ERROR);

		return false;		
	}

	/**
	 * <b>validaData</b>:
	 * Verifica se a data é uma data válida, verifica se ano é bisexto para validar o mes de fevereiro,
	 * Verifica se os demais meses podem ou não ter mais de 30 ou 31 dias.
	 * @param array $data Recebe data a ser verificada.
	 * @return bool Retorna true caso data válida.
	 */
	private function validaData(array $data) : bool
	{
		$anoBisexto = null;

		if ((int) $data[2] % 4 === 0){
			$anoBisexto = true;
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

			case '01':
			case '03':
			case '05':
			case '07':
			case '08':
			case '10':
			case '12':

			if ($data[0] > 31){
				$this->msg = Msg::setMsg('Verifique a Data, o mês não pode termais de 31 dias.', ERROR);
				return false;
			}

			break;

			case '04':
			case '06':
			case '09':
			case '11':

			if ($data[0] > 31){
				$this->msg = Msg::setMsg('Verifique a Data para este mês não pode ter mais de 30 dias.', ERROR);
				return false;
			}

			break;

		}

		return true;
	}

	/**
	 * <b>validaValor</b>:
	 * Verifica se o campo valoer do formulário foi corretamente preenchido.
	 * @param string $valor Recebe o valor a ser validado.
	 */
	private function validaValor(string $valor)
	{
		$verify = false;

		$valor = str_replace(array('R', '$', '.', ',', ' '), '', $valor);

		if ($valor === ''){
			$this->msg = Msg::setMsg('O campo valordeve ser preenchido', ERROR);

		} elseif (!is_numeric($valor)){
			$this->msg = Msg::setMsg('O campo valor so aceita números, e os seguintes caracteres: "<b>R</b>", "<b>$</b>", "<b>.</b>" e "<b>,</b>"', ERROR);

		} elseif (strlen($valor) > 8){
			$this->msg = Msg::setMsg('O valor máximo para o campo campo valor é de R$ 999.999,99', ERROR);

		} else {
			return true;
		}

		return $verify;
	}

}
