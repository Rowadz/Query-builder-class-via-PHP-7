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