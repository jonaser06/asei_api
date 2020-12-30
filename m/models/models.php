<?php
require_once 'config.php';


try {
        $db = getDB();
        $userData ='';
        $sql = 
        "SELECT * FROM usuarios ,perfiles 
        WHERE perfiles.ID_PE = usuarios.ID_PE
        AND usuarios.EMAIL = :email 
        AND usuarios.CLAVE = :clave ";
        $stmt = $db->prepare($sql);
        $stmt->bindParam("username", $data->username, PDO::PARAM_STR);
        $password=hash('sha256',$data->password);
        $stmt->bindParam("password", $password, PDO::PARAM_STR);
        $stmt->execute();
        $userData = $stmt->fetch(PDO::FETCH_OBJ);
        return $user ? $user : FALSE;

}
catch(PDOException $e) {
    echo '{"error":{"text":'. $e->getMessage() .'}}';
}