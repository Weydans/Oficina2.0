<?php

/**
* <b>Msg</b>: Classe do tipo HELPER
* Responsável pela exibição de menságens ao usuário.
* @author Weydans Campos de Barros, 06/03/2019.
*/
abstract class Msg {

	/**
	* <b>setMsg</b>:
	* Configura a mnságem a ser exibida ao usuário.
	* @param string $content Recebe o texto da menságem.
	* @param Constante $class Recebe uma constante de Erro 
	* contendo a classe css a ser utilizada para estilização
	* @return Retorna uma menságem de erro estilizada.
	*/
    public static function setMsg($content, $class) {

        $msg = "<div class=\"msg {$class}\">"
                . "<p>{$content}</p>"
                . "</div>";

        return $msg;

    }

}
