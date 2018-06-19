<?php 
require_once "MySQL/autoloader.php";
// create an object that represent the users table, the class should be the same name as the table
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

// $x = new Database;
// any value that hae a default value just pass NULL
// $x->create("users", [ NULL,"Rowad", "Rowad3@gmail.com" ,"password", NULL, NULL, NULL]); // create new user
// $x->read("users", [ "id" => 2460], "email, name"); // getting the email and name for the user that have an id of 2460
// $x->read("users", [ "id" => 2460]); // getting the row for the user that have 2460 as an id
// $x->read("users");   // getting all users
// $x->read("users", [], "name"); // getting all the users names 

// getting the user where her id is 5 and name is sarah and sec is F OR her last_name = nice
// $x->read("users", ["id" => 5, "name" => "sarah", "sex" => "F", "OR", "last_name" => "nice"]); 
// update the users where thier password is password to 243423
// $x->update("users", ["password" => "password"], ["password" => "243423"]);
// $x->update("users", [], ["password" => '$2y$10$XQsFHZbQUvLqCvfwqQBuTe8dDj1GGFeP28ULjYxZ6E248KgwzG7Ue']); // updating all the users passwords to 243423
// $x->delete("users", ["email" => "Rowad@gmail.com"]);
// $x->delete("users"); // delete all the data in the users table
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="css/app.css">
    <title>MySQL CLASS</title>
</head>
<body>
    
</body>
</html>