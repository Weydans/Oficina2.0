<?php

/**
 * <b>Rout</b>:
 * Classe Responsável por todo o gerenciamento
 * de rotas do sistema, trabalha sobre arquitetura MVC.
 * @author Weydans Campos de Barros, 06/03/2019.
 */

class Route {

    private $url;
    private $route;
    private $result = null;

    /**
     * Pega o path da url que o usuario digitou
     * e configura propriedade $url com o valor obtido.
     */
    public function __construct() 
    {
        $this->url = strip_tags(trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)));
    }

    /**
    * Sinaliza metodo get
    */
    public function get(string $route, $callback){
        $this->route($route, $callback);
    }

    /**
    * Sinaliza metodo post
    */
    public function post(string $route, $callback){
        if ($_POST){
            $this->route($route, $callback);
        }
    }

    /**
    * Sinaliza metodo put
    */
    public function put(string $route, $callback){
        $this->route($route, $callback);
    }

    /**
    * Sinaliza metodo delete
    */
    public function delete(string $route, $callback){
        $this->route($route, $callback);
    }

    /**
     * <b>run</b>:
     * Realiza a exibição da view, 
     * caso rota inválida retorna página 404.
     *
     * @param objeto $_404 recebe página 404 personalizada.
     */
    public function run($_404 = null) 
    {
        if ($this->result === null && !empty($_404)){
            $_404->show();

        } elseif ($this->result === null) {
            echo '<div>';
            echo '<h1 style="margin: 0; padding: 0;">404</h1>';
            echo '<h3 style="margin: 0; padding: 0;">Página não encontrada!</h3>';
            echo '</div>';
        }
    }


    /**
     * <b>route</b>: Realiza todo o controle de rotas do sistema.
     *
     * @param string $method Ex(get, post, delete...)
     * @param string $route Rota a ser definida pelo programador
     * @param type $callback Recebe uma função callback com a ação (Controller)
     * que deve ser executado. Deve retornar uma string com Html a ser exibido.
     */
    private function route(string $route, $callback) 
    {
        $this->route = $route;

        $urlArray = explode('/', $this->url);
        $routeArray = explode('/', $this->route);

        $param = array();

        if ($this->route === $this->url && $this->result === null){
            $callback();
            $this->result = true;

        } elseif (count($routeArray) === count($urlArray)) {

            $j = 0;
            
            for ($i = 0; $i < count($urlArray); $i++) {

                // Verifica se valor dos indices são diferentes
                // e se o(s) parametro(s) passado(s) inicia(m) e termina(m) com "{}".
                if (
                    ($urlArray[$i] !== $routeArray[$i]) 
                    && 
                    (substr($routeArray[$i], 0, 1) === '{') 
                    && 
                    (substr($routeArray[$i], strrpos($routeArray[$i], '}'), 1) === '}')) 
                {

                    $routeArray[$i] = $urlArray[$i];

                    $key = "param{$j}";
                    $param[$key] = $urlArray[$i];

                    $j++;
                }
            }

            $this->route = implode('/', $routeArray);
            
            if ($this->result === null) {
                $this->setNumParam($param, $callback);
            }
        }
    }

    /**
     * <b>setNumParam</b>: Verifica se a rota informada pelo usuario
     * corresponde a uma rota válida do sistema. Verifica se existem
     * parâmetros e realiza a passagem dinamicamente de cada parametro.
     *
     * @param type $param Recebe um array associativo com os valores dos parâmetros.
     * @param type $callback Recebe a closure a ser executada.
     */
    private function setNumParam($param, $callback) 
    {
        $numParams = count($param);

        if ($this->route === $this->url && $numParams > 0) {

            extract($param);

            if ($numParams == 1) {
                $callback($param0);

            } elseif ($numParams == 2) {
                $callback($param0, $param1);

            } elseif ($numParams == 3) {
                $callback($param0, $param1, $param2);

            } elseif ($numParams == 4) {
                $callback($param0, $param1, $param2, $param3);

            } elseif ($numParams == 5) {
                $callback($param0, $param1, $param2, $param3, $param4);

            } elseif ($numParams > 5) {
                $this->result = Msg::setMsg('Número de parâmetros para rotas deve ser menor ou igual a 5.', ERROR);
            }

            $this->result = true;
        }
    }
}
