<?php

require_once __DIR__ . "/../util/Database.php";


class UsuariosModel{

    private $db_conn;

    public function __construct(){
        $this->db_conn = Database::getConnection();
    }

    /******************************************************************************/
    public function create($user){
        
    }

    /******************************************************************************/
    public function update($user){
        
    }

    /******************************************************************************/
    public function delete($user){
        /*$delete_sql = "DELETE FROM `user` WHERE `id`=" . $user->getId();

        if ($this->db_conn->query($delete_sql) === true) {
            return true;
        } else {
            echo "Error: " . $delete_sql . "<br>" . $this->db_conn->error;
            return false;
        }*/

    }


}

