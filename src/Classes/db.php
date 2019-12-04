<?php
namespace Blog\Model;

//echo dirname(__DIR__);
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

// $test = new db();
// $test->db();
?>