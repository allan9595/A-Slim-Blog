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
            //var_dump($_SESSION['error']);
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

            //if there are empty, throw error msg
            
            if(
                $title == "" ||
                $body == ""
            ){ 
                $errors = ['title' => $title, 'body'=>$body];
                foreach($errors as $key => $value){
                    if($value == ""){
                        $_SESSION['error'][$key] = "$key is required!";
                    }
                }
                //the following section is to keep the input value filled
                $_SESSION['input']['title'] = $title;
                $_SESSION['input']['body'] = $body;
            }else{
                session_unset();
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

                if(!empty($tag)){
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
                                    INSERT INTO tags (tagName,slug, post_id) VALUES(?, (SELECT slug FROM posts WHERE id = $lastInsertPostId), $lastInsertPostId);
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
                                    INSERT INTO tags_posts (postId, tagId, postSlug) VALUES (?, ?, (SELECT slug FROM posts WHERE id = $lastInsertPostId));
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
                                INSERT INTO tags (tagName,slug, post_id) VALUES(?, (SELECT slug FROM posts WHERE id = $lastInsertPostId), $lastInsertPostId);
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
                                INSERT INTO tags_posts (postId, tagId, postSlug) VALUES (?, ?, (SELECT slug FROM posts WHERE id = $lastInsertPostId));
                            ";
                            $pdo = $db->prepare($sql);
                            $pdo->bindValue(1, $lastInsertPostId, PDO::PARAM_STR);
                            $pdo->bindValue(2, $tag_id, PDO::PARAM_STR);
                            $pdo->execute();
                        }catch(Exception $e){
                            echo $e->getMessage();
                        }
                    }   
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
            //$slug = filter_var($slug, FILTER_SANITIZE_STRING);
            $tag = filter_var($data["tag"], FILTER_SANITIZE_STRING);
            $date = date_format(new DateTime('NOW', new DateTimeZone('EST')), 'Y-m-d H:i:s'); // get the current date/time when the blog create

            //fetch the post id

            if(
                $title == "" ||
                $body == ""
            ){ 
                session_unset();
                $errors = ['title' => $title, 'body'=>$body];
                foreach($errors as $key => $value){
                    if($value == ""){
                        $_SESSION['error'][$key] = "$key is required!";
                    }
                }
                //the following section is to keep the input value filled
                $_SESSION['input']['title'] = $title;
                $_SESSION['input']['body'] = $body;
            }else{
                session_unset();
                $sql = "
                    SELECT id FROM posts WHERE slug = '$slug' LIMIT 1;
                ";
                $pdo = $db->prepare($sql);
                $pdo->execute();
                $result = $pdo->fetch(PDO::FETCH_ASSOC);
            
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

                //update the slug 

                $sql = "
                    UPDATE posts
                    SET slug = (
                            SELECT [REPLACE]( (
                                                SELECT LOWER( (
                                                                    SELECT title
                                                                    FROM posts
                                                                    WHERE slug = '$slug'
                                                                )
                                                        ) 
                                            ), ' ', '-') || '-' ||  ( (
                                                                        SELECT COUNT(title)
                                                                        FROM posts
                                                                        WHERE title = (
                                                                                            SELECT title
                                                                                            FROM posts
                                                                                            WHERE slug = '$slug'
                                                                                        )
                                                                    )
                                                                    ) ) 
                        
                    WHERE slug = '$slug'
                ";
                $pdo = $db->prepare($sql);
                $pdo->execute();

                //update the relateed tag to posts map
                if(isset($result['id'])){
                    $sql = "
                        UPDATE tags_posts SET postSlug = ( SELECT slug FROM posts WHERE id = $result[id] ) WHERE postId = $result[id];
                    ";
                    $pdo = $db->prepare($sql);
                    $pdo->execute();

                    $sql = "
                        UPDATE tags SET slug = ( SELECT slug FROM posts WHERE id = $result[id] ) WHERE post_id = $result[id];
                    ";
                    $pdo = $db->prepare($sql);
                    $pdo->execute();
                }                                                   
                
                //update tags
                if(isset($tag)){
                    //if mutiple tags entered, explode them by ',' then add them to db
                    if(strpos($tag, ',') == true){

                        //delete the tags name from tag table
                        $sql = "
                            DELETE FROM tags WHERE post_id = ?
                        ";
                        $pdo = $db->prepare($sql);
                        $pdo->bindValue(1, $result['id'], PDO::PARAM_INT);
                        $pdo->execute();

                        //delete the assocuate id number form third many-to-many table
                        $sql = "
                            DELETE FROM tags_posts WHERE postId = ?;
                        ";
                        $pdo = $db->prepare($sql);
                        $pdo->bindValue(1, $result['id'], PDO::PARAM_INT);
                        $pdo->execute();

                        //split the tag with comma ',', then loop through the tag array to add those tags into db
                        $tag = explode(',', $tag);
                        foreach ($tag as $value) {
                            $value = trim($value);
                            $sql = "
                                INSERT INTO tags (tagName, slug, post_id) VALUES(?, (SELECT slug FROM posts WHERE id = ?), ?);
                            ";
                            $pdo = $db->prepare($sql);
                            $pdo->bindValue(1, $value, PDO::PARAM_STR);
                            $pdo->bindValue(2, $result['id'], PDO::PARAM_INT);
                            $pdo->bindValue(3, $result['id'], PDO::PARAM_INT);
                            $pdo->execute();
            
                            $sql = "
                                SELECT last_insert_rowid()
                            ";
                            $pdo = $db->prepare($sql);
                            $pdo->execute();
            
                            $tag_array =$pdo->fetch(PDO::FETCH_ASSOC);
                            $tag_id = $tag_array["last_insert_rowid()"];
                            
                            $sql = "
                                INSERT INTO tags_posts (postId, tagId, postSlug) VALUES (?, ?, (SELECT slug FROM posts WHERE id = $result[id]));
                            ";
                            $pdo = $db->prepare($sql);
                            $pdo->bindValue(1, $result['id'], PDO::PARAM_STR);
                            $pdo->bindValue(2, $tag_id, PDO::PARAM_STR);
                            $pdo->execute();
                        }
                    }else{
                        //if only one tag insert

                        //delete the tags name from tag table
                        $sql = "
                            DELETE FROM tags WHERE post_id = ?
                        ";
                        $pdo = $db->prepare($sql);
                        $pdo->bindValue(1, $result['id'], PDO::PARAM_INT);
                        $pdo->execute();

                        //delete the assocuate id number form third many-to-many table
                        $sql = "
                            DELETE FROM tags_posts WHERE postId = ?;
                        ";
                        $pdo = $db->prepare($sql);
                        $pdo->bindValue(1, $result['id'], PDO::PARAM_INT);
                        $pdo->execute();
            
                        //re-insert the edited tag
                        $sql = "
                            INSERT INTO tags (tagName, slug, post_id) VALUES(?, (SELECT slug FROM posts WHERE id = ?), ?);
                        ";
                        $pdo = $db->prepare($sql);
                        $pdo->bindValue(1, $tag, PDO::PARAM_STR);
                        $pdo->bindValue(2, $result['id'], PDO::PARAM_INT);
                        $pdo->bindValue(3, $result['id'], PDO::PARAM_INT);
                        $pdo->execute();

                        $sql = "
                            SELECT last_insert_rowid()
                        ";
                        $pdo = $db->prepare($sql);
                        $pdo->execute();

                        $tag_array =$pdo->fetch(PDO::FETCH_ASSOC);
                        $tag_id = $tag_array["last_insert_rowid()"];
                        
                        $sql = "
                                INSERT INTO tags_posts (postId, tagId, postSlug) VALUES (?, ?, (SELECT slug FROM posts WHERE id = $result[id]));
                        ";
                        $pdo = $db->prepare($sql);
                        $pdo->bindValue(1, $result['id'], PDO::PARAM_STR);
                        $pdo->bindValue(2, $tag_id, PDO::PARAM_STR);
                        $pdo->execute();
                    }    
                }
                //if no tag inputed, just delete all the records
                if(empty($tag)){
                    //delete the tags name from tag table
                    $sql = "
                        DELETE FROM tags WHERE post_id = ?
                    ";
                    $pdo = $db->prepare($sql);
                    $pdo->bindValue(1, $result['id'], PDO::PARAM_INT);
                    $pdo->execute();

                    //delete the assocuate id number form third many-to-many table
                    $sql = "
                        DELETE FROM tags_posts WHERE postId = ?;
                    ";
                    $pdo = $db->prepare($sql);
                    $pdo->bindValue(1, $result['id'], PDO::PARAM_INT);
                    $pdo->execute();
                }
            }
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

