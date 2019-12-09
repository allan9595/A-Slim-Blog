<?php
namespace Blog\Model;

class tagsModel{
    protected $db;
    public function __construct(){
        $this->db = new db();
    }
    //create the add blog entry
    public function addTag($data){

        try{
            $db = $this->db->db();
            //filter the post data 
            $tag_name = filter_var($data["tag"], FILTER_SANITIZE_STRING);

            //if mutiple tags entered, explode them by ',' then add them to db
            if(strpos($tag, ',') == true){
                $tag = explode(',', $tag);
                foreach ($tag as $value) {
                    try{
                        $value = trim($value);
                        //build the many to many replationship
                        $sql = "
                            INSERT INTO `tags` (`tagName`) VALUES(?);
                        ";
                        $pdo = $db->prepare($sql);
                        $pdo->bindValue(1, $value, PDO::PARAM_STR);
                        $pdo->execute();
                        $sql = "SELECT last_insert_rowid()";
                        $pdo = $db->prepare($sql);
                        $pdo->execute();
                        $tag_array =$pdo->fetch(PDO::FETCH_ASSOC);
                        $tag_id = $tag_array["last_insert_rowid()"];
                        
                        $sql = "
                            INSERT INTO entries_tags (entry_id, tag_id) VALUES (?, ?);
                        ";
                        $pdo = $db->prepare($sql);
                        $pdo->bindValue(1, $entries_id, PDO::PARAM_STR);
                        $pdo->bindValue(2, $tag_id, PDO::PARAM_STR);
                        $pdo->execute();
                    }catch(Exception $e){
                        echo $e->getMessage();
                    }
        
                }
            }else{
                try{
                    //if just one single tag entered at one time, then insert it directly
                    $sql = "
                        INSERT INTO `tags` (`tag_name`) VALUES(?);
                    ";
                    $pdo = $db->prepare($sql);
                    $pdo->bindValue(1, $tag, PDO::PARAM_STR);
                    $pdo->execute();
                    $sql = "SELECT last_insert_rowid()";
                    $pdo = $db->prepare($sql);
                    $pdo->execute();
                    $tag_array =$pdo->fetch(PDO::FETCH_ASSOC);
                    $tag_id = $tag_array["last_insert_rowid()"];
                    
                    $sql = "
                        INSERT INTO entries_tags (entry_id, tag_id) VALUES (?, ?);
                    ";
                    $pdo = $db->prepare($sql);
                    $pdo->bindValue(1, $entries_id, PDO::PARAM_STR);
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
}
?>