<?php
namespace Blog\Model;
require __DIR__ . "/../../vendor/autoload.php";

use Blog\Model\db;
use \DateTime;
use \DateTimeZone;
use PDO;

class entriesModel{
    protected $db;
    public function __construct(){
        $this->db = new db();
    }
    //get blogs entries
    public function getBlog(){
        try{
            $db = $this->db->db();
            $sql = "
                SELECT id, title, date, slug FROM posts ORDER BY date DESC
            ";
            $pdo = $db->prepare($sql);
            $pdo->execute();
            $results = $pdo->fetchAll(PDO::FETCH_ASSOC);
            return $results;
        }catch(Exception $e){
            echo $e->getMessage();
        }
    }

      //get blog by title
      public function getBlogBySlug($slug){
        $slug = filter_var($slug, FILTER_SANITIZE_STRING);
        try{
            $db = $this->db->db();
            $sql = "
                SELECT id, title, date, body, slug FROM posts WHERE slug = ?
            ";
            $pdo = $db->prepare($sql);
            $pdo->bindValue(1, $slug, PDO::PARAM_STR);
            $pdo->execute();
            return $pdo->fetch(PDO::FETCH_ASSOC);
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
            $tag = filter_var($data["tag"], FILTER_SANITIZE_STRING);

            //insert new data into the db
            $sql = "
                INSERT INTO posts (title, date, body, slug) VALUES (?, ?, ?, NULL)
            ";

            $pdo = $db->prepare($sql);
            $pdo->bindValue(1, $title, PDO::PARAM_STR);
            $pdo->bindValue(2, $date, PDO::PARAM_STR);
            $pdo->bindValue(3, $body, PDO::PARAM_STR);
            $pdo->execute();

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
                                        ), ' ', '-') || '-' ||  ( (
                                                                    SELECT COUNT(title)
                                                                    FROM posts
                                                                    WHERE title = (
                                                                                        SELECT title
                                                                                        FROM posts
                                                                                        WHERE id = (
                                                                                                        SELECT last_insert_rowid() 
                                                                                                    )
                                                                                    )
                                                                )
                                                                ) ) 
                    
                WHERE id = (
                            SELECT last_insert_rowid() 
                        );
            ";
            $pdo = $db->prepare($sql);
            $pdo->execute();


            //handle tags insertion and build many to many relationship
            $sql = "
                SELECT last_insert_rowid()
            "; 

            $pdo = $db->prepare($sql);
            $pdo->execute();
            $lastInsertPostIdArray = $pdo->fetch(PDO::FETCH_ASSOC);
            $lastInsertPostId = $lastInsertPostIdArray["last_insert_rowid()"];

            //if mutiple tags entered, explode them by ',' then add them to db
            if(strpos($tag, ',') == true){
                $tag = explode(',', $tag);
                foreach ($tag as $value) {
                    try{
                        $value = strtolower(trim($value));
                        //build the many to many replationship
                        $sql = "
                            INSERT INTO tags (tagName) VALUES(?);
                        ";
                        $pdo = $db->prepare($sql);
                        $pdo->bindValue(1, $value, PDO::PARAM_STR);
                        $pdo->execute();

                        $sql = "
                            SELECT last_insert_rowid()
                        ";
                        $pdo = $db->prepare($sql);
                        $pdo->execute();

                        $tag_array = $pdo->fetch(PDO::FETCH_ASSOC);
                        $tag_id = $tag_array["last_insert_rowid()"];
                    
                        $sql = "
                            INSERT INTO tags_posts (postId, tagId) VALUES (?, ?);
                        ";
                        $pdo = $db->prepare($sql);
                        $pdo->bindValue(1, $lastInsertPostId, PDO::PARAM_STR);
                        $pdo->bindValue(2, $tag_id, PDO::PARAM_STR);
                        $pdo->execute();
                    }catch(Exception $e){
                        echo $e->getMessage();
                    }
        
                }
            }else{
                try{
                    $tag = strtolower(trim($tag));
                    //if just one single tag entered at one time, then insert it directly
                    $sql = "
                        INSERT INTO tags (tagName) VALUES(?);
                    ";
                    $pdo = $db->prepare($sql);
                    $pdo->bindValue(1, $tag, PDO::PARAM_STR);
                    $pdo->execute();
                    $sql = "
                        SELECT last_insert_rowid()
                    ";
                    $pdo = $db->prepare($sql);
                    $pdo->execute();
                    $tag_array =$pdo->fetch(PDO::FETCH_ASSOC);
                    $tag_id = $tag_array["last_insert_rowid()"];
                    
                    $sql = "
                        INSERT INTO tags_posts (postId, tagId) VALUES (?, ?);
                    ";
                    $pdo = $db->prepare($sql);
                    $pdo->bindValue(1, $lastInsertPostId, PDO::PARAM_STR);
                    $pdo->bindValue(2, $tag_id, PDO::PARAM_STR);
                    $pdo->execute();
                }catch(Exception $e){
                    echo $e->getMessage();
                }
            }    
        }catch(Exception $e){
            echo $e->getMessage();
        }
    }

    //create the edit blog entry
    public function editBlog($data, $slug){
        try{
            $db = $this->db->db();
            //filter the post data 
            $title = filter_var($data["title"], FILTER_SANITIZE_STRING);
            $body = filter_var($data["entry"], FILTER_SANITIZE_STRING);
            $slug = filter_var($slug, FILTER_SANITIZE_STRING);
            $date = date_format(new DateTime('NOW', new DateTimeZone('EST')), 'Y-m-d H:i:s'); // get the current date/time when the blog create

            //insert new data into the db
            $sql = "
                UPDATE posts SET title = ?, date = ?, body = ? WHERE slug = ?;
            ";

            $pdo = $db->prepare($sql);
            $pdo->bindValue(1, $title, PDO::PARAM_STR);
            $pdo->bindValue(2, $date, PDO::PARAM_STR);
            $pdo->bindValue(3, $body, PDO::PARAM_STR);
            $pdo->bindValue(4, $slug, PDO::PARAM_STR);
            $pdo->execute();
        }catch(Exception $e){
            echo $e->getMessage();
        }
    }

    //create the delete blog entry
    public function deleteBlog($id){
        try{
            //delete the related entrie, tag, and many to many table id in the following order
            $db = $this->db->db();
            $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
            $sql = "
                DELETE FROM posts WHERE id = ? 
            ";
            $pdo = $db->prepare($sql);
            $pdo->bindValue(1, $id, PDO::PARAM_INT);
            $pdo->execute();

            $sql = "
                DELETE FROM tags WHERE id IN (select tagId from tags_posts where postId = ?);
            ";
            $pdo = $db->prepare($sql);
            $pdo->bindValue(1, $id, PDO::PARAM_INT);
            $pdo->execute();

            $sql = "
                DELETE FROM comments WHERE postId = ?;
            ";
            $pdo = $db->prepare($sql);
            $pdo->bindValue(1, $id, PDO::PARAM_INT);
            $pdo->execute();

            $sql = "
                DELETE FROM tags_posts WHERE postId = ?;
            ";
            $pdo = $db->prepare($sql);
            $pdo->bindValue(1, $id, PDO::PARAM_INT);
            $pdo->execute();
        }catch(Exception $e){
            echo $e->getMessage();
        }
    }
}
?>

