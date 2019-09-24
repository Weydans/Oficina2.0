<?php

/**
* <b>Render</b> Classe do tipo HELPER* 
* Responsável por substituir os links das views por valores do sistema.
*@author Wydans Campos de Barros, 06/03/2019.
*/
abstract class Render {

    private static $data;
    private static $view;

    /**
    * <b>show</b>:
    * Realiza a substituição dos links pelos valores.
    * 
    * @param string $template Recebe o caminho do arquivo com o template a ser substituido
    * @param array $data Recebe um array associativo com os valores a serem passados para o template,
    * Obs: As chaves do array devem ser iguais aos links entrechaves "{}" no template.
    * @return Retorna uma string com o template.
    */
    public static function show(string $template, array $data) 
    {
        self::$view = file_get_contents($template);
        self::$data = $data;

        self::replaceLinks();
        return self::$view;
    }

    /**
    * <b>replaceLinks</b>:
    * Substitui os links pelos valores correspondentes.
    */
    private static function replaceLinks() 
    {
        foreach (self::$data as $key => $value) {
            self::$view = str_replace('{' . $key . '}', $value, self::$view);
        }
    }

}
