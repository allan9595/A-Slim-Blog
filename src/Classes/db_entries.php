<?php

namespace Blog\Model;
//include ('db_connection.php');
//
require "../../vendor/autoload.php";
use Blog\Model;
class entriesModel {
    
    //create the read blog entry
    public function getBlog(){
        $db = new db();
        var_dump($db->db());
    }
    //create the add blog entry
    //create the edit blog entry
    //create the delete blog entry
    
}

$test = new entriesModel();
$test->getBlog();
?>
