<?php

class MainSql
{
    public $connection = null;
    private $server = '127.0.0.1';
    public $username = null;
    private $db_name = 'osbb';

    public function __construct($username, $password)
    {

        session_start();

        session_regenerate_id();

        if($username !== null && $password !== null) {

            $_SESSION['username'] = $username;
            $_SESSION['password'] = $password;

            $this->username = $_SESSION['username'];

            if ($_SESSION['username'] === 'root' && $_SESSION['password'] === '') {
                $this->connection = new mysqli($this->server, $_SESSION['username'], $_SESSION['password'], $this->db_name);
                $this->insertDefaultData($this->connection);
                $this->useDataBase();
            } else {
                $con = new mysqli($this->server, 'root', '', $this->db_name);

                $this->insertDefaultData($con);

                if ($con->connect_error) {
                    http_response_code(500);
                    die("Error connecting to database");
                }

                $user = $_SESSION['username'];
                $pass = $_SESSION['password'];

                $createUserQuery = $con->prepare("CREATE USER IF NOT EXISTS '$user'@'localhost' IDENTIFIED BY '$pass'");
                $createUserQuery->execute();

                if ($createUserQuery->errno) {
                    http_response_code(500);
                    die("Error creating user: " . $createUserQuery->error);
                }
                $createUserQuery->close();

                $flush = $con->query("FLUSH PRIVILEGES;");
                if ($flush !== TRUE) {
                    http_response_code(500);
                    die("Error while flushing privileges!");
                }

                $grantSelectQuery = $con->prepare("GRANT SELECT ON *.* TO '$user'@'localhost'");
                $grantSelectQuery->execute();

                if ($grantSelectQuery->errno) {
                    http_response_code(500);
                    die("Error granting SELECT privilege: " . $grantSelectQuery->error);
                }
                $grantSelectQuery->close();

                $flush = $con->query("FLUSH PRIVILEGES;");
                if ($flush !== TRUE) {
                    http_response_code(500);
                    die("Error while flushing privileges!");
                }
                $con->close();

                $this->connection = new mysqli($this->server, $_SESSION['username'], $_SESSION['password'], $this->db_name);
                $this->useDataBase();
            }
        }
        else{
            $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'root';
            $password = isset($_SESSION['password']) ? $_SESSION['password'] : '';

            $this->connection = new mysqli($this->server, $username, $password, $this->db_name);

            $this->useDataBase();

            if($_SESSION['username']){
                $this->username = $_SESSION['username'];
            }
        }

        session_write_close();
    }
    private function useDataBase()
    {
        mysqli_select_db($this->connection, $this->db_name);
    }

    public function checkPassword($password, $username)
    {
        $checkPassword = $this->connection->prepare('SELECT * FROM mysql.user WHERE User=?');
        $checkPassword->bind_param('s', $username);

        if ($checkPassword->execute() !== TRUE) {

            http_response_code(500);
            die("Error while selecting user!");

        } else {
            $result = $checkPassword->get_result();
            $checkPassword->close();

            if($result->num_rows === 0){
                return true;
            }

            if ($result->num_rows > 0) {
                $checkPasswordUser = $this->connection->prepare('SELECT * FROM mysql.user WHERE User=? and Password=PASSWORD(?)');

                $checkPasswordUser->bind_param('ss', $username, $password);

                if ($checkPasswordUser->execute() !== TRUE) {
                    http_response_code(500);
                    die("Error while selecting user!");
                }
                else{
                    $result = $checkPasswordUser->get_result();

                    if($result->num_rows > 0){
                        return true;
                    }
                    else{
                        return false;
                    }
                }
            }
        }
    }

    // permission for users
    public function getUsers(){
        $select_users = $this->connection->prepare('SELECT * FROM mysql.user WHERE LENGTH(Password) > 0;');
        $users_array = [];

        if($select_users->execute() !== TRUE){
            http_response_code(500);
            die('Error while select all users!');
        }
        else{
            $result = $select_users->get_result();

            if($result->num_rows > 0){
                while($row = $result->fetch_assoc()){
                    $users_array[] = $row;
                }
            }

            $select_users->close();

            return $users_array;
        }
    }
    public function updatePermissions($request)
    {
        foreach ($request as $value) {
            if($value['select'] === 'Y'){
                $grantSelect = "GRANT SELECT ON *.* TO '" . $value['user'] . "'@'localhost';";
                $allow = $this->connection->query($grantSelect);
                if ($allow !== TRUE) {
                    http_response_code(500);
                    die("Error while allowing access!");
                }
            }
            if($value['select'] === 'N'){
                $revokeSelect = "REVOKE SELECT FROM *.* FROM '" . $value['user'] . "'@'localhost';";
                $notAllow = $this->connection->query($revokeSelect);
                if ($notAllow !== TRUE) {
                    http_response_code(500);
                    die("Error while allowing access!");
                }
            }
            if($value['insert'] === 'Y'){
                $grantInsert = "GRANT INSERT ON *.* TO '" . $value['user'] . "'@'localhost';";
                $allow = $this->connection->query($grantInsert);
                if ($allow !== TRUE) {
                    http_response_code(500);
                    die("Error while allowing access!");
                }
            }
            if($value['insert'] === 'N'){
                $revokeInsert = "REVOKE INSERT ON *.* FROM '" . $value['user'] . "'@'localhost'";

                $notAllow = $this->connection->query($revokeInsert);
                if ($notAllow !== TRUE) {
                    http_response_code(500);
                    die("Error while allowing access!");
                }
            }
            if($value['update'] === 'Y'){
                $grantUpdate = "GRANT UPDATE ON *.* TO '" . $value['user'] . "'@'localhost';";
                $allow = $this->connection->query($grantUpdate);
                if ($allow !== TRUE) {
                    http_response_code(500);
                    die("Error while allowing access!");
                }
            }
            if($value['update'] === 'N'){
                $revokeUpdate = "REVOKE UPDATE ON *.* FROM '" . $value['user'] . "'@'localhost';";
                $notAllow = $this->connection->query($revokeUpdate);
                if ($notAllow !== TRUE) {
                    http_response_code(500);
                    die("Error while allowing access!");
                }
            }
            if($value['delete'] === 'Y'){
                $grantDelete = "GRANT DELETE ON *.* TO '" . $value['user'] . "'@'localhost';";
                $allow = $this->connection->query($grantDelete);
                if ($allow !== TRUE) {
                    http_response_code(500);
                    die("Error while allowing access!");
                }
            }
            if($value['delete'] === 'N'){
                $revokeDelete = "REVOKE DELETE ON *.* FROM '" . $value['user'] . "'@'localhost';";
                $notAllow = $this->connection->query($revokeDelete);
                if ($notAllow !== TRUE) {
                    http_response_code(500);
                    die("Error while allowing access!");
                }
            }
        }

        $flush = $this->connection->query("FLUSH PRIVILEGES;");
        if ($flush !== TRUE) {
            http_response_code(500);
            die("Error while flushing privileges!");
        }

        return $this->getUsers();
    }




    // main page

    // check permission

    function checkPermission($action){

        $permission = null;

        if($action === 'add_permission'){
            $permission = $this->connection->prepare('SELECT Insert_priv as act from mysql.user where User = ?');
        }
        if($action === 'update_permission'){
            $permission = $this->connection->prepare('SELECT Update_priv as act from mysql.user where User = ?');
        }
        if($action === 'delete_permission'){
            $permission = $this->connection->prepare('SELECT Delete_priv as act from mysql.user where User = ?');
        }

        $current_username = $this->username;
        $permission->bind_param('s', $current_username);

        if($permission->execute() !== TRUE){
            http_response_code(500);
            die('Error while check permission!');
        }
        else{
            $result = $permission->get_result();

            if($result->num_rows > 0) {
                while($row = $result->fetch_assoc()){
                    if($row['act'] === 'Y'){
                        return true;
                    }
                    else{
                        return false;
                    }
                }
            }
        }
    }


    // insert start value
    public function insertDefaultData($conn){
        if (!isset($_SESSION['added']) || !$_SESSION['added']) {
            $insert_data = $conn->prepare('
                    INSERT INTO owners_flats (`entrance_number`, `flat_number`, `owner_firstname`,
                            `owner_secondname`, `phone_number_owner`, `services_price`, `tenants`)
                    SELECT
                        flat.entrance_number AS entrance_number,
                        flat.flat_number AS flat_number,
                        flat.f_name_owner AS owner_firstname,
                        flat.s_name_owner AS owner_secondname,
                        flat.phone_number_owner AS phone_number_owner,
                        flat.services_price AS services_price,
                        flat.tenants AS tenants
                    FROM entrance_details
                    INNER JOIN flat ON flat.entrance_number = entrance_details.entrance_number;
                ');
            if ($insert_data->execute()) {
                $_SESSION['added'] = true;
            } else {
                http_response_code(500);
                die("Error while inserting data into owners_flats!");
            }
        }
    }
    public function getData()
    {
        $select_data = $this->connection->prepare('SELECT * FROM owners_flats;');
        $array_data = [];

        if ($select_data->execute() !== TRUE) {
            http_response_code(500);
            die("Error while select data from table!");
        }

        $result = $select_data->get_result();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $array_data[] = $row;
            }
        }

        $select_data->close();

        return $array_data;
    }
    public function checkPhoneNumber($request){
        $id = (int)$request['update_id'];
        $phone = $request['phone_number_owner'];

        $checkPhoneNumber = $this->connection->prepare('SELECT * FROM owners_flats where phone_number_owner = ?');
        $checkPhoneNumber->bind_param('s', $phone);

        if($checkPhoneNumber->execute() !== TRUE){
            http_response_code(500);
            die('Error while check phone number!');
        }
        else{
            $result = $checkPhoneNumber->get_result();
            $row = $result->fetch_assoc();
            $checkPhoneNumber->close();

            if(($result->num_rows === 1 && (int)$row['id'] === $id) ||
                ($result->num_rows === 0 && $id === 0) ||
                ($result->num_rows === 0 && $id !== 0)
            ){
                return true;
            }
            else{
                return false;
            }
        }
    }
    public function checkEntryFlat($request)
    {
        $id = (int)$request['update_id'];

        $checkEntryFlat = $this->connection->prepare('SELECT * FROM owners_flats WHERE entrance_number = ? AND flat_number = ?');

        $entry = (int)$request['entrance_number'];
        $flat = (int)$request['flat_number'];

        $checkEntryFlat->bind_param('ii', $entry, $flat);

        if (!$checkEntryFlat->execute()) {
            http_response_code(500);
            die("Error executing check query: " . $this->connection->error);
        } else {
            $result = $checkEntryFlat->get_result();
            $row = $result->fetch_assoc();

            $checkEntryFlat->close();


            if (($result->num_rows === 1 && (int)$row['id'] === $id) ||
                ($result->num_rows === 0 && $id === 0) ||
                ($result->num_rows === 0 && $id !== 0)
            ) {
                return true;
            }
            else {
                return false;
            }
        }
    }
    public function addEntry($request)
    {
            $entrance_number = (int)$request['entrance_number'];
            $flat_number = (int)$request['flat_number'];
            $owner_firstname = $request['owner_firstname'];
            $owner_secondname = $request['owner_secondname'];
            $phone_number_owner = $request['phone_number_owner'];
            $services_price = (int)$request['services_price'];
            $tenants = (int)$request['tenants'];

            $insert_data = $this->connection->prepare("INSERT INTO owners_flats (entrance_number, flat_number, owner_firstname, 
                  owner_secondname, phone_number_owner, services_price, tenants) 
        VALUES (?, ?, ?, ?, ?, ?, ?)");

            $insert_data->bind_param('iisssii', $entrance_number, $flat_number, $owner_firstname,
                $owner_secondname, $phone_number_owner, $services_price, $tenants);

            if ($insert_data->execute() !== TRUE) {
                http_response_code(500);
                die("Error while add new entry !");
            } else {
                $insert_data->close();
                return $this->connection->insert_id;
            }
    }
    public function updateEntry($request)
    {
            $id = (int)$request['update_id'];
            $entrance_number = (int)$request['entrance_number'];
            $flat_number = (int)$request['flat_number'];
            $owner_firstname = $request['owner_firstname'];
            $owner_secondname = $request['owner_secondname'];
            $phone_number_owner = $request['phone_number_owner'];
            $services_price = (int)$request['services_price'];
            $tenants = (int)$request['tenants'];

            $update_entry = $this->connection->prepare('UPDATE owners_flats SET  
            entrance_number = ?, flat_number = ?, owner_firstname = ?, owner_secondname = ?, phone_number_owner = ?,
            services_price = ?, tenants = ? where id = ?                  
        ');

            $update_entry->bind_param('iisssiii', $entrance_number, $flat_number, $owner_firstname, $owner_secondname,
                $phone_number_owner, $services_price, $tenants, $id);

            if ($update_entry->execute() !== TRUE) {
                http_response_code(500);
                die("Error while update entry !");
            } else {
                $update_entry->close();
                return $id;
            }
    }
    public function getNewEntry($id)
    {
        $select_added = $this->connection->prepare('SELECT * FROM owners_flats where id = ?');
        $id = (int)$id;

        $select_added->bind_param('i', $id);

        if ($select_added->execute() !== TRUE) {
            http_response_code(500);
            die("Error while getting added!");
        } else {
            $result = $select_added->get_result();
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    return $row;
                }
            }
            $select_added->close();
        }
    }
    public function deleteEntry($request)
    {
            $id = (int)$request['delete_id'];
            $delete_entry = $this->connection->prepare('DELETE FROM owners_flats where id = ?');

            $delete_entry->bind_param('i', $id);

            if ($delete_entry->execute() !== TRUE) {
                http_response_code(500);
                die("Error while deleting entry!");
            } else {
                $delete_entry->close();
                return $id;
            }
    }
    public function search($value)
    {
        $keyword = '%' . $value . '%';

        $search_array = [];

        $search = $this->connection->prepare("SELECT id FROM owners_flats WHERE CONCAT(owner_firstname, ' ', owner_secondname) LIKE ?");
        $search->bind_param('s', $keyword);

        if($search->execute() !== TRUE){
            http_response_code(500);
            die("Error while search!");
        }
        else{
            $result = $search->get_result();
            if($result->num_rows > 0){
                while($row = $result->fetch_assoc()){
                    $search_array[] = $row;
                }
            }
            $search->close();

            return $search_array;
        }
    }
}