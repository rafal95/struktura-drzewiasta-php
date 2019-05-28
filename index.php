<?php 
	require "pdo.php";
	require "function.php";  

	
	if(!empty($_POST)){
		if(!empty($_POST['dodaj'])){
			add($_POST['select'],$_POST['name']);
		}
		else if(!empty($_POST['usun'])){
			del($_POST['select']);
		}
		else if(!empty($_POST['przenies'])){
			move($_POST['source'],$_POST['destination']);
		}
		else if(!empty($_POST['edit'])){
			edit($_POST['select'],$_POST['name']);
		}
	}
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
		<form  action="index.php" method="POST">
		<label> Wybierz węzeł: </label> <select name="select" >
			<?php foreach($nodes as $node){
				echo "<option>".$node['title']."</option>";
			} ?>
			</select>
			<input type="submit" value="Pokaż" name="pokaz" />
		</form>
		<?php
		
			if(!empty($_POST)){
				if(!empty($_POST['pokaz']))
				{
					showNode($_POST['select']);
				}
				else{
					showTree();
				}
			}
			else{
				showTree();
			}
		?>

	</div>
	</div>

</body>
</html>

<?php
}
?>
