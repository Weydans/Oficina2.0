<?php

/**
* <b>Conn</b>:
* Classe responsável por gerenciar todas as conexões ao 
* banco de dados através do design pathern Singleton.*
* @author Weydans Campos de Barros, 06/03/2019.
*/

class Conn {

    /** @var PDO */
    private static $connection = null;

    /**
    *<b>getConnection</b>
    * Realiza conexão com a base de dados caso não exista uma conexão ja aberta.
    * @return Retorna um objeto do tipo PDO.
    */
    public static function getConnection() {

        if (self::$connection == null) {
            try {

                self::$connection = new PDO(DSN, USER, PASS, OPTIONS);
                self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
            } catch (Exception $e) {

                echo $e->getMessage();
                die;
            }
        }

        return self::$connection;
    }

}
