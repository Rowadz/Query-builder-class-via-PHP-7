# Simple MySQL class using PHP 7

A database class that minimize the number of queries I write,I'm building this just to expand my knowledge in PHP 7 and MySql.

Will expand it soon.

[Post]( http://therealrowad.blogspot.com/2018/06/building-simple-mysql-class-using-php-7.html )
### Database class
```php
<?php 

require_once("config.php");

class Database
{

    private $db;

    public function __construct(){
        $this->db = new mysqli(HOST, USER, PASSWORD, DATABASE); // from config.php file
        // connect_errorno returns the last error code number from the last call to mysqli_connect().
        // connect_error returns the last error message string from the last call to mysqli_connect().
        if(($this->db->connect_errno || $this->db->connect_error) && DEV ){
            // I added classes to make the errors more readable
            echo "
                <h3 class='connection_error'>
                    Faild to connect to MySQL: (<span> {$this->db->connect_errno} </span>), <span> {$this->db->connect_error}</span>
                </h3>
                ";
        }
    }

    // CRUD create, read, update, delete

    // create method
    public function create($table, $params) {
        $query = "INSERT INTO {$table} VALUES (" . str_repeat('?, ', count($params) - 1);
        $query .= " ?)"; // closing the query
        $this->prepare($stmt, $query);
        if(!is_null($stmt)) 
            if($this->bindNExecute($stmt, $params))
                return $stmt->affected_rows; 
            else return false;
        else return false; 
    }

    // read
    // $where will be an associative array => ["id" => 5, "name" => "sarah", "sex" => "F", "OR", "last_name" => "nice"]
    // for example if we want to change the whatToSelect value => $whatToSelect = "name, last_name, date_of_birth" as a string
    public function read($table, $where = [], $whatToSelect = "*"){
        if(!empty($where)){
            $this->getWhereData($where, $string, $params);
            $query = "SELECT {$whatToSelect} FROM {$table}
                     WHERE {$string}";
        }else $query = "SELECT {$whatToSelect} FROM {$table}"; // if there is no params
        $this->prepare($stmt, $query);
        if(!is_null($stmt)){
               return $this->bindNReturn($stmt, isset($params) ? $params: null);
        }
    }
    
    // update
    // $where will be an associative array => ["id" => 5, "name" => "sarah", "sex" => "F", "OR", "last_name" => "nice"]
    // $colNValues will be an array too  => ["name" => "new name", "last_name" => "rowad"]
    public function update($table, $where = [], $colNValues){
        $this->getWhereData($where, $string, $params); // structuring the query
        $this->columnsAndValues($string2, $params2,$colNValues); // structuring the query
        if(!empty($where)){
            $query = "UPDATE {$table} 
                      SET {$string2}
                      WHERE {$string}";
        }else { // if we want to update all the row just pass an empty 'where' array to this method
            $query = "UPDATE {$table} SET {$string2}";
            $params = [];
        }
        $this->prepare($stmt, $query);
        if(!is_null($stmt))
            if($this->bindNExecute($stmt, array_merge($params2, $params)))
                return $stmt->affected_rows; 
            else return false;
        else return false;
    }

    // delete 
    public function delete($table, $where = []){
        if(!empty($where)){
            $this->getWhereData($where, $string, $params);
            $query = "DELETE FROM {$table}
                     WHERE {$string}";
        }else{
            $query = "DELETE FROM {$table}"; // remove all the data in the table
        }
        $this->prepare($stmt, $query);
        if(!is_null($stmt)) 
            if($this->bindNExecute($stmt, (isset($params)? $params : null)))
                return $stmt->affected_rows;
            else return false;
        else return false;
    }

    // getting the columns names from array and return them as array 
    // for the update method
    private function columnsAndValues(&$string, &$params,$colNValues){
        foreach ($colNValues as $key => $value) {
            $params[] = $value;
            $string .= "{$key} = ?, ";
        }
        $string = substr($string, 0, -2); // removing the last ', '
    }

    // output a string with the below structure 
    // id = ? AND  name = ?
    // or
    // id = ? or name = ? AND role = ?
    // for the prepare statment to prevent SQL injection
    private function getWhereData(&$where, &$string = null, &$params = null){
        foreach ($where as $key => $value) {
            // $params[] = $value;
            if($key !== 0) $params[] = $value; // the key will be zero if it only or ony thing else
            // here we are adding or to the query if the value is or else we are adding the key and ? AND 
            if(strtolower($value) === "or") $string .= "OR ";
            else {
                $string .= "{$key} =  ? AND ";
            }
        }
        // the next two lines to replace the "AND OR" with " OR "
        $or =  "AND OR";
        $string = str_replace($or, " OR ", $string);
        // the last 4 characters are always "AND " and we need to remove them
        $string = substr($string, 0, -4); 
    }

    // method to bind the params and return the query result
    private function bindNReturn(&$stmt, $params = null){
        $data = [];
        $this->getTypesForPrepareStatment($types, $params); // getting the types as a one string
        if($this->bind($stmt, $types, $params)) // binding the params to the query
            if($this->execute($stmt)){
                $result = $stmt->get_result();
                while($row = $result->fetch_array(MYSQLI_ASSOC)) // // MYSQLI_ASSOC return an associative array where the column name is the key
                    $data[] = $row;
                if(isset($data))  return $data;
                else return [];
                /* if you want to print the data
                foreach ($data as $rowData)
                    foreach ($rowData as $key => $value)
                        echo "{$key} : {$value} <br>";

                */
            }
    }

    // method to bind the params and execute the query
    private function bindNExecute(&$stmt, $params){
        $this->getTypesForPrepareStatment($types, $params); // getting the types as a one string
        if($this->bind($stmt, $types, $params)){  // binding the params to the query
            if($this->execute($stmt))
                return true;
            else return false;
        }
    }

    // make a string that represents the types that will be binded
    private function getTypesForPrepareStatment(&$types, $params){
        for($i = 0; $i < sizeof($params); $i++){
            switch (gettype($params[$i])) {
                case "integer":
                    $types .= 'i'; // corresponding variable has type integer
                    break;
                case "string":
                    $types .=  's'; // corresponding variable has type double
                    break;
                case "double":
                    $types .= 'd'; // corresponding variable has type string
                    break;
                default:
                    // if the type is null this will work
                    $types .=  "b"; // corresponding variable is a blob and will be sent in packets
                    break;
            }
        }
    }

    // tha actual method that will prepare the statment
    private function prepare(&$stmt ,$query){
        if(!($stmt = $this->db->prepare($query))) {
            $stmt = null;
            echo "
            <h3 class='prepare_error'>
                Prepare failed: ( {$this->db->errno} )  {$this->db->error}
            </h3>
            ";
        }
        else $stmt = $this->db->prepare($query);
    }

    // tha actual method that will bind the params to the statment
    private function bind(&$stmt, $types, $params){
        // in case we want to select all rows we just return true
        if(is_array($params)){
            if(!($stmt->bind_param($types, ...$params))) { // ... operator. This is also known as the splat operator in other languages
                echo "<h3 class = 'bind_error'>Binding parameters failed: ( {$stmt->errno} )  {$stmt->error}</h3>";
                return false;
            } 
        }
        return true;
    }
    
    // tha actual method that will execute query
    public function execute(&$stmt){
        if (!$stmt->execute()) {
            echo "<h3 class = 'execute_error'>Execute failed: ( {$stmt->errno} ) {$stmt->error}</h3>";
            return false;
        }
        return true;
    }

    public function __destruct(){
        // closing the connection whenever the object is no longer being used
        $this->db->close();
    }
}
```
### A class that uses the Database class
users.php 
- The class should be the same name as the table in the database.
```php
<?php 
class users{
    
    private $connection;
    public function __construct(){
        // eg : called after login 
        $this->connection = new Database;
    }

    public function getAllRecoreds(){
        return $this->connection->read(__CLASS__);
    }

    public function create($arr){
        return $this->connection->create(__CLASS__, $arr);
    }

    public function read($condition, $columns = "*"){
        return $this->connection->read(__CLASS__, isset($condition) ? $condition : null , isset($columns)? $columns : "*");
    }

    public function update($condition = [], $columns){
        return $this->connection->update(__CLASS__, $condition, $columns);
    }

    public function delete($condition = []){
        return $this->connection->delete(__CLASS__, $condition);
    }
}
```
### Usage 
```php
$user = new users;
$user->getAllRecoreds(); // getting all records
// create new user, NULL for the default values
$user->create([NULL,"Rowad", "Rowad3@gmail.com" ,"password", NULL, NULL, NULL]);
$user->read([ "id" => 2460]); // get the user that have an id equal to 2460
$user->read([ "id" => 2460], "email, name"); // get name and email for the user which have the id of 2460
$user->read([]); // get all the records
$user->read([], "name"); // get the name column form the users table
// set password to secret where the role is admin OR name is Rowad
$user->update(["role" => "admin", "OR", "name" => "Rowad"], ["password" => "secret"]); 
// set password to secret where the role is admin AND name is Rowad
$user->update(["role" => "admin", "name" => "Rowad"], ["password" => "secret"]); 
// set all the password to bad
$user->update([],  ["password" => 'bad']);
// delete the row where the email field is Rowad3@gmail.com
$user->delete(["email" => "Rowad3@gmail.com"]);
```

### Or you can just use the Database class on its own 
```php
$x = new Database;
// any value that have a default value just pass NULL
$x->create("users", [ NULL,"Rowad", "Rowad3@gmail.com" ,"password", NULL, NULL, NULL]); // create new user
$x->read("users", [ "id" => 2460], "email, name"); // getting the email and name for the user that have an id of 2460
$x->read("users", [ "id" => 2460]); // getting the row for the user that have 2460 as an id
$x->read("users");   // getting all users
$x->read("users", [], "name"); // getting all the users names 

// getting the user where her id is 5 and name is sarah and sec is F OR her last_name = nice
$x->read("users", ["id" => 5, "name" => "sarah", "sex" => "F", "OR", "last_name" => "nice"]); 
// update the users where thier password is password to 24342
$x->update("users", ["password" => "password"], ["password" => "243423"]);
$x->update("users", [], ["password" => '$2y$10$XQsFHZbQUvLqCvfwqQBuTe8dDj1GGFeP28ULjYxZ6E248KgwzG7Ue']); // updating all the users passwords to 243423
$x->delete("users", ["email" => "Rowad@gmail.com"]);
$x->delete("users"); // delete all the data in the users table
```