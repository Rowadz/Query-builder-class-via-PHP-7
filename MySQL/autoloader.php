<?php 

// if any error append in the production mode just display the page below

// error_reporting(E_ALL); 
// ini_set("display_errors", 0);
// include("errors/real_error.php");


spl_autoload_register(function ($className){
    include "{$className}.php";
});



