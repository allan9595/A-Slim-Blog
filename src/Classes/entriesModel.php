<?php
namespace Blog\Model;
require __DIR__ . "/../../vendor/autoload.php";

use Blog\Model\db;
use \DateTime;
use \DateTimeZone;

class entriesModel{
    protected $db;
    public function __construct(){
        $this->db = new db();
    }
    //get blogs entries
    public function getBlog(){
        // var_dump($this->db->db());
        try{
            $sql = "
                SELECT id, title, date FROM posts ORDER BY date DESC
            ";
            $pdo = $this->db->db()->prepare($sql);
            $pdo->execute();
            $results = $pdo->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        }catch(Exception $e){
            echo $e->getMessage();
        }
    }

      //get blog by title
      public function getBlogByTitle($title){
        $title = filter_var($title, FILTER_SANITIZE_STRING);
        try{
            $sql = "
                SELECT id, title, date FROM posts ORDER BY date DESC WHERE title = 'title'
            ";
            $pdo = $this->db->db()->prepare($sql);
            $pdo->bindValue(1, $title, \PDO::PARAM_STR);
            $pdo->execute();
            $results = $pdo->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        }catch(Exception $e){
            echo $e->getMessage();
        }
    }

    //create the add blog entry
    public function addBlog($data){

        try{
            $db = $this->db->db();
            //filter the post data 
            $title = filter_var($data["title"], FILTER_SANITIZE_STRING);
            $body = filter_var($data["entry"], FILTER_SANITIZE_STRING);
            $date = date_format(new DateTime('NOW', new DateTimeZone('EST')), 'Y-m-d H:i:s'); // get the current date/time when the blog create

            //insert new data into the db
            $sql = "
                INSERT INTO posts (title, date, body, slug) VALUES (?, ?, ?, NULL)
            ";

            $pdo = $db->prepare($sql);
            $pdo->bindValue(1, $title, \PDO::PARAM_STR);
            $pdo->bindValue(2, $date, \PDO::PARAM_STR);
            $pdo->bindValue(3, $body, \PDO::PARAM_STR);
            $pdo->execute();

            // $sql = "
            //     SELECT last_insert_rowid();
            // ";
            // $pdo = $db->prepare($sql);
            // $pdo->execute();
            // $results = $pdo->fetchAll(\PDO::FETCH_ASSOC);
            // var_dump($results);
            // create the slug for the record just created
            $sql = "
                UPDATE posts
                SET slug = (
                        SELECT [REPLACE]( (
                                            SELECT LOWER( (
                                                                SELECT title
                                                                FROM posts
                                                                WHERE id = (
                                                                                SELECT last_insert_rowid() 
                                                                            )
                                                            )
                                                    ) 
                                        ), ' ', '-') || '-' || (
                                                                    SELECT last_insert_rowid() 
                                                                )
                    )
            WHERE id = (
                            SELECT last_insert_rowid() 
                        )
         
            ";
            $pdo = $db->prepare($sql);
            $pdo->execute();
        }catch(Exception $e){
            echo $e->getMessage();
        }
    }

    // public function createSlug(){
    //     $sql = "
    //         UPDATE posts
    //         SET slug = (
    //                 SELECT [REPLACE]( (
    //                                     SELECT LOWER( (
    //                                                         SELECT title
    //                                                         FROM posts
    //                                                         WHERE id = (
    //                                                                         SELECT last_insert_rowid() 
    //                                                                     )
    //                                                     )
    //                                             ) 
    //                                 ), ' ', '-') || '-' || (
    //                                                             SELECT last_insert_rowid() 
    //                                                         )
    //             )
    //     WHERE id = (
    //                     SELECT last_insert_rowid() 
    //                 );
    
    //     ";
    //     $pdo = $this->db->db()->prepare($sql);
    //     $pdo->execute();
    // }
    //create the edit blog entry
    public function editBlog(){

    }

    //create the delete blog entry
    public function deleteBlog(){

    }
    
}

// $test = new entriesModel();
// $test->getBlog();

//php query
// INSERT INTO posts (title, date, body, slug) VALUES ('PHP is awesome', datetime(), 'I love php', NULL);
// UPDATE posts SET slug = (SELECT lower(replace(title,' ','-')) From posts WHERE id = last_insert_rowid()) || '-' || last_insert_rowid() WHERE id = last_insert_rowid();
?>

