<?php 

/**
 * <b>OrcamentoFormCadastro<b>
 * Gerencia todas as açoes que unvolvem a view do formulário de cadastro.
 * Cadastra, atualiza e realiza diálogo com usuário caso dados informados sejam inválidos.
 * @author Weydans Campos de Barros, 18/04/2019.
 */
class OrcamentoFormCadastro extends OrcamentoController
{
	private $dadosForm = array();

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

		$resVerif = $this->validarDadosFormCad();

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

		// EXECUTA ATUALIZAÇÃO
		if (is_numeric($this->dadosForm['orcamento_id'])){
			$resVerif = $this->validarDadosFormCad();

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

		// CARREGA ORÇAMENTO A SER ATUALIZADO
		// EXIBE MENSÁGENS DE CADASTRO E ATUALIZAÇAO AO USUÁRIO
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
	 * <b>validarDadosForm</b>:
	 * Verifica se todos os dados infomados pelo usuário são válidos.
	 * @return bool Retorna true caso dados sejam válidos.
	 */
	private function validarDadosFormCad() : bool
	{
		$verify = false;

		if (!$this->validaCliente($this->dadosForm['orcamento_cliente'])){
			$this->validaCliente($this->dadosForm['orcamento_cliente']);

		} elseif (!$this->validaDataHora($this->dadosForm['orcamento_data_hora'])){
			$this->validaDataHora($this->dadosForm['orcamento_data_hora']);

		} elseif (!$this->validaVendedor($this->dadosForm['orcamento_vendedor'])){
			$this->validaVendedor($this->dadosForm['orcamento_vendedor']);

		} elseif (!$this->validaDescricao($this->dadosForm['orcamento_descricao'])){
			$this->validaDescricao($this->dadosForm['orcamento_descricao']);

		} elseif (!$this->validaValor($this->dadosForm['orcamento_valor'])) {
			$this->validaValor($this->dadosForm['orcamento_valor']);
			
		} else {
			$verify = true;
		}

		return $verify;
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

}
