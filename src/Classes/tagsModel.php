<?php
namespace Blog\Model;
use PDO; 

class tagsModel{
    protected $db;
    public function __construct(){
        $this->db = new db();
    }

    public function fetchTags($id){
        try{
            $db = $this->db->db();
            $sql = "
                SELECT posts.id, tags.tagName FROM posts
                    LEFT JOIN tags_posts ON posts.id = tags_posts.postId
                    LEFT JOIN tags on tags.id = tags_posts.tagId
                    WHERE postId = ?;
            ";
            $pdo = $db->prepare($sql);
            $pdo->bindValue(1, $id, PDO::PARAM_INT);
            $pdo->execute();
            $results = $pdo->fetchAll(PDO::FETCH_ASSOC);
            return $results;
        }catch(Exception $e){
            echo $e->getMessage();
        }
    }

    public function fetchTagsBySlug($slug){
        try{
            $db = $this->db->db();
            $slug = filter_var($slug, FILTER_SANITIZE_STRING);
            $sql = "
                SELECT DISTINCT posts.id, tags.tagName FROM posts
                LEFT JOIN tags_posts ON posts.slug = postSlug
                LEFT JOIN tags on tags.slug = postSlug
                WHERE posts.slug = ?;
            ";
            $pdo = $db->prepare($sql);
            $pdo->bindValue(1, $slug, PDO::PARAM_STR);
            $pdo->execute();
            $results = $pdo->fetchAll(PDO::FETCH_ASSOC);
            return $results;
        }catch(Exception $e){
            echo $e->getMessage();
        }
    }

    public function tagsFiltered($filter){
        try{
            //filtered the tag based on its name
            $db = $this->db->db();
            $sql = "
                SELECT posts.id, posts.title, posts.date, posts.slug FROM posts 
                    JOIN tags_posts ON posts.id = postId
                    JOIN tags ON tagId = tags.id
                    WHERE tagName = ? ORDER BY date DESC
            "; 
            $pdo = $db->prepare($sql);
            $pdo->bindValue(1, $filter, PDO::PARAM_STR);
            $pdo->execute();
            $results = $pdo->fetchAll(PDO::FETCH_ASSOC);
            return $results;
        }catch(Exception $e){
            echo $e->getMessage();
        }
    }
}
?>