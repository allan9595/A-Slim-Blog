<?php
namespace Blog\Model;
require "../../vendor/autoload.php";
//use PDO;

class entriesModel {
    protected $db;
    public function __construct(){
        $this->db = new db();
    }
    //create the read blog entry
    public function getBlog(){
        //var_dump($db->db());
        try{
            $sql = "
                select id, title, date from posts
            ";
            $pdo = $this->db->db()->prepare($sql);
            $pdo->execute();
            $results = $pdo->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        }catch(Exception $e){
            echo $e->getMessage();
        }

    }

    //create the add blog entry
    public function addBlog(){

        //filter the post data 
        $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
        $body = filter_input(INPUT_post, 'body', FILTER_SANITIZE_STRING);
        $date = date_format(new DateTime(), 'Y-m-d H:i:s'); // get the current date/time when the blog create

        $sql = "
            insert into posts (title, date, body) value (?, ?, ?)
        ";

        $pdo = $this->db->db()->prepare($sql);
        $pdo->bindValue(1, $title, \PDO::PARAM_STR);
        $pdo->bindValue(2, $date, \PDO::PARAM_STR);
        $pdo->bindValue(3, $body, \PDO::PARAM_STR);

        $pdo->execute();

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
