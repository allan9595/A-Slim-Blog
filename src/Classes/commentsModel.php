<?php
namespace Blog\Model;
require __DIR__ . "/../../vendor/autoload.php";

use Blog\Model\db;
use \DateTime;
use \DateTimeZone;

class commentsModel{
    protected $db;
    public function __construct(){
        $this->db = new db();
    }

    //add comments for a entry
    public function addComment($data, $slug){
        try{
            $db = $this->db->db();
            if($data['name'] == ''){
                $name = "Anonymous";
            }else{
                $name = filter_var($data['name'], FILTER_SANITIZE_STRING);
            }
            //filter the comment
            $slug = filter_var($slug, FILTER_SANITIZE_STRING);
            $comment = filter_var($data['comment'], FILTER_SANITIZE_STRING);
            $date = date_format(new DateTime('NOW', new DateTimeZone('EST')), 'Y-m-d H:i:s'); // get the current date/time when the comment create
            $sql = "
                INSERT INTO comments (name, body, postId, date) VALUES (?, ?, (SELECT id FROM posts WHERE slug = '$slug'), ?)
            ";
            $pdo = $db->prepare($sql);
            $pdo->bindValue(1, $name, \PDO::PARAM_STR);
            $pdo->bindValue(2, $comment, \PDO::PARAM_STR);
            $pdo->bindValue(3, $date, \PDO::PARAM_STR);
            $pdo->execute();
        }catch(Exception $e){
            echo $e->getMessage();
        }
    }

    //get comments for a entry
    public function getComments($slug){
        try{
            $db = $this->db->db();
            $slug = filter_var($slug, FILTER_SANITIZE_STRING);
            $sql = "
                SELECT id, name, body, postId, date FROM comments WHERE postId = (SELECT id FROM posts WHERE slug = '$slug') ORDER BY date DESC
            ";
            $pdo = $db->prepare($sql);
            $pdo->execute();
            return $pdo->fetchAll(\PDO::FETCH_ASSOC);
        }catch(Exception $e){
            echo $e->getMessage();
        }
    }
    
}

?>


