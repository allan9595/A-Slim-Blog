<?php
namespace Blog\Model;


class db{
    public function db(){
        try{
            //connect to the db
            $db = new \PDO("sqlite:".dirname(__DIR__)."/Classes/blog.db");
            $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            return $db;
        }catch(Exception $e){
            echo $e->getMessage();
            die();
        }
    }
}

?>