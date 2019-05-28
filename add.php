<?php
	require "pdo.php";

if(isset($pdo)){
	$res = $pdo->query("SELECT title,
	(SELECT count(parent.id)-1
	FROM elements AS parent
	WHERE node.lft BETWEEN parent.lft AND parent.rgt)
	AS depth
	FROM elements AS node
	ORDER BY node.lft");

	$nodes = $res->fetchAll();

?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pl" lang="pl">
<head>
	  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
	  
	  <link rel="Stylesheet" href="style.css">
	     
</head>
<body>
	<div id="container">
		<div id="header">
			<div id="nav">
				<ul class="nav">
					<li><a href="index.php">Wyświetl drzewo</a></li>
					<li><a href="add.php">Dodaj węzeł</a></li>
					<li><a href="delete.php">Usuń węzeł</a></li>
					<li><a href="move.php">Przenieś węzeł</a></li>
					<li><a href="edit.php">Edytuj węzeł</a></li>
				</ul>
			</div>
		</div>
		<div id="content">
			<form action="index.php" method="POST">
				<label> Węzeł: </label> <select name="select">
				<?php foreach($nodes as $node){
							echo "<option>".$node['title']."</option>";
					  } ?>
				</select> </br>
				<label> Nazwa: </label> <input type="text" name="name" required />
				<input type="submit" value="Dodaj" name="dodaj" />
			</form>
		</div>
	</div>
</body>
</html>
<?php
}
?>