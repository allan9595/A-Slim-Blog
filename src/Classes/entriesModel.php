<?php
namespace Blog\Model;
require "../../vendor/autoload.php";
class entriesModel {
    
    //create the read blog entry
    public function getBlog(){
        $db = new db();
        var_dump($db->db());
    }
    //create the add blog entry
    
   
    public function addBlog(){

    }

    //create the edit blog entry
    public function editBlog(){

    }

    //create the delete blog entry
    public function deleteBlog(){

    }
    
}

$test = new entriesModel();
$test->getBlog();
?>
