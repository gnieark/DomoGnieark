<?php
class User_Sql extends User {


        public static function create_user(PDO $db,$table_users,$login, $display_name,
                                            $password,$admin = false,$active = true)
        {
            $stmt = $db->prepare(
                "INSERT INTO " . $table_users . " 
                    (login, display_name, auth_method,password,admin,active) 
                VALUES 
                    (:login, :display_name, 'local', :password, :admin, :active)"
            );

            $stmt->bindParam(':login', $login);
            $stmt->bindParam(':display_name', $display_name);
            $stmt->bindParam(':password',$hashed_password);
            $stmt->bindParam(':admin', $adminInt);
            $stmt->bindParam(':active', $activeInt);

            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            
            $adminInt = $admin? 1 : 0;
            $activeInt = $active? 1 : 0;
            $stmt->execute();

            return $db->lastInsertId(); 
        }

        public function authentificate($login,$password)
        {
            
            $stmt = $this->db->prepare(
                "SELECT id,display_name,password,admin
                FROM users 
                WHERE login=:login 
                AND active=1
                AND auth_method='local'"
            );
            
            $stmt->bindParam(':login', $login);
            $stmt->execute();
            if($r = $stmt->fetch()){

                    //check password
                if(password_verify($password,$r["password"])){
                    
                    $this->is_connected = true;
                    $this->display_name = $r["display_name"];
                    $this->id = $r['id'];
                    $this->auth_method = 'sql';
                    $this->is_admin = ($r["admin"] == '1')? true : false;
                    
                    return $this;
                }
            }
            $this->is_connected = false;
            return $this;
        }

}
