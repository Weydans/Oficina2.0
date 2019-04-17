<?php

/**
* <b>Autoload</b> 
* Classe responsável pelo carregamento automático das classe do sistema.*
* @author Weydans Campos de Barros, 06/03/2019.
*/

abstract class Autoload {

    /**
    * <b>run</b>:
    * Verifivca a existencia de uma determinada classe 
    * e a inclui caso exista, caso não exibe mensagem de erro.
    */
    public static function run() {

        spl_autoload_register(function ($class) {

            $file = $class . '.php';
            $res = false;

            // Rotas pasta app
            $controller = './app/controller/';
            $model = './app/model/';

            // Rotas pasta core
            $helper = './core/helper/';
            $class = './core/class/';

            $dirName = [
                $controller,
                $model,
                $helper,
                $class
            ];

            foreach ($dirName as $dir) {
                if (file_exists($dir . $file) && !is_dir($dir . $file)) {
                    require_once $dir . $file;
                    $res = $dir . $file;
                }
            }

            if (!$res) {
                require_once $helper . 'Msg.php';
                echo Msg::setMsg("<b>Não foi possível incluir:</b> {$file}", ERROR);
            }
        });
    }

}
