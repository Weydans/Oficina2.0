<?php 

/**
 * <b>Controller</b>:
 * classe genérica de controle do projeto
 * Responsável por fornecer interface comun a todos os controllers do sistema.
 * @author Weydans Campos de Barros, 18/04/2019.
 */

abstract class Controller
{
	protected $msg;
	protected $header;
	protected $content;
	protected $footer;
	protected $finalView;

	/**
	 * <b>setHead</b>:
	 * Configura o header da view dinamicamente
	 * @param string $caminhoArquivo recebe o caminho do arquivo view.
	 * @param array $params recebe um array associativo caso existam links aerem substituidos na view.
	 */
	protected function setHeader(string $caminhoArquivo, array $params = array())
	{
		$this->header = Render::show($caminhoArquivo, $params);
	}

	/**
	 * <b>setContent</b>:
	 * Configura o conteúdo principal da view dinamicamente
	 * @param string $caminhoArquivo recebe o caminho do arquivo view.
	 * @param array $params recebe um array associativo caso existam links aerem substituidos na view.
	 */
	protected function setContent(string $caminhoArquivo, array $params = array())
	{
		$this->content = Render::show($caminhoArquivo, $params);
	}

	/**
	 * <b>setContent</b>:
	 * Configura o footer da view dinamicamente
	 * @param string $caminhoArquivo recebe o caminho do arquivo view.
	 * @param array $params recebe um array associativo caso existam links aerem substituidos na view.
	 */
	protected function setFooter(string $caminhoArquivo, array $params =  array())
	{
		$this->footer = Render::show($caminhoArquivo, $params);
	}

	/**
	 * <b>getPreparedView</b>:
	 * Configura uma determinada view dinamicamente
	 * @param string $caminhoArquivo recebe o caminho do arquivo view.
	 * @param array $params recebe um array associativo caso existam links aerem substituidos na view.
	 * @return string Retorna uma string contendo a view no formato HTML
	 */
	protected function getPreparedView(string $caminhoArquivo, array $params) : string
	{
		$preparedView = Render::show($caminhoArquivo, $params);
		return $preparedView;
	}

	/**
	 * <b>getFinalView</b>:
	 * Monta view de exibição
	 * @return string Retorna a view pronta para exibição;
	 */
	protected function getFinalView()
	{
		$this->finalView = $this->header . $this->content . $this->footer;
		return $this->finalView;
	}
}
