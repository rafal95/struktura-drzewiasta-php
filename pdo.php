<?php
    try{
        $pdo = new PDO('mysql:host=localhost;dbname=tree;encoding=utf8', 'root','');
        $pdo -> setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        $pdo -> setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }
    catch(PDOException $e)
   {
      echo "Połączenie nie mogło zostać utworzone";
   }
?>