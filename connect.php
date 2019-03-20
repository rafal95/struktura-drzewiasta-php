<?php

	function connection(){
		$host='localhost';
		$username='root';
		$password='';
		$db_name='tree';
		$con = mysqli_connect($host, $username, $password,$db_name)or die("Nie mozna polączyc sie z MySQL");
		return $con;
	}
?>