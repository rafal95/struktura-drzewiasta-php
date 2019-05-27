<?php

function showTree($depth=0){
	require "pdo.php";

	$res = $pdo->query('SELECT title,
	(SELECT count(parent.id)-1
	FROM elements AS parent
	WHERE node.lft BETWEEN parent.lft AND parent.rgt)
	AS depth
	FROM elements AS node
	ORDER BY node.lft');

	$nodes = $res->fetchAll();
	
	$tree = array();
	foreach($nodes as $node) {
		$tree[] = $node;
	}
	
	$result = '';
	//$depth = 1;
	$currDepth = $depth-1;  
	while (!empty($tree)) {
	  $currNode = array_shift($tree);
	  
	  if($currNode['depth'] >= $depth){
		  if ($currNode['depth'] > $currDepth) {
			$result .= '<ul>';
		  }
		  if ($currNode['depth'] < $currDepth) {
			$result .= str_repeat('</ul>', $currDepth - $currNode['depth']);
		  }
		  $result .= '<li>' . $currNode['title'] . '</li>';
		  $currDepth = $currNode['depth'];
		  if (empty($tree)) {
			$result .= str_repeat('</ul>', $currDepth + 1);
		  }
	  }
	}

print $result;
}

//showTree(;

function showNode($nodeName,$depth=0){
	require "pdo.php";
	$res = $pdo->prepare("SELECT lft, rgt FROM elements WHERE title=:name");
	$res -> bindParam(':name',$nodeName);
	$res -> execute();
	$node = $res->fetch();

	$res = $pdo->query("SELECT title,
	(SELECT count(parent.id)-1
	FROM elements AS parent
	WHERE node.lft BETWEEN parent.lft AND parent.rgt)
	AS depth
	FROM elements AS node
	WHERE node.lft BETWEEN ".$node['lft']." AND ".$node['rgt']." ORDER BY node.lft");

	$nodes = $res->fetchAll();
	
	$tree = array();
	foreach($nodes as $node) {
		$tree[] = $node;
	}
	
	$result = '';
	$currDepth = $depth-1;  
	while (!empty($tree)) {
	  $currNode = array_shift($tree);
	  if($currNode['depth'] >= $depth){
		  if ($currNode['depth'] > $currDepth) {
			$result .= '<ul>';
		  }
		  if ($currNode['depth'] < $currDepth) {
			$result .= str_repeat('</ul>', $currDepth - $currNode['depth']);
		  }
		  $result .= '<li>' . $currNode['title'] . '</li>';
		  $currDepth = $currNode['depth'];
		  if (empty($tree)) {
			$result .= str_repeat('</ul>', $currDepth + 1);
		  }
	  }
	}

print $result;
}

//showNode('Linux');

function add($nodeName,$newElement){
	require "pdo.php";

	$res = $pdo->query("SELECT title FROM elements");
	$nodes = $res->fetchAll();

	$exist = 0;
	foreach($nodes as $node){
		if(strtoupper($node['title']) == strtoupper($newElement)){
			$exist = 1;
			break;
		}

	}

	if(!$exist){
		$res = $pdo->prepare("SELECT rgt FROM elements WHERE title=:name");
		$res -> bindParam(':name',$nodeName);
		$res -> execute();
		$node = $res->fetch();
		$par1 = $node['rgt']+1;

		$res = $pdo->prepare("UPDATE elements SET lft = `lft`+2  WHERE lft >=:par");
		$res -> bindParam(':par',$par1);
		$res -> execute();

		$res = $pdo->prepare("UPDATE elements SET rgt = `rgt`+2  WHERE rgt >=:par");
		$res -> bindParam(':par',$node['rgt']);
		$res -> execute();

		$res = $pdo->prepare("INSERT INTO elements (title,lft,rgt) VALUES (:new,:lft,:rgt)");
		$res -> bindParam(':new',$newElement);
		$res -> bindParam(':lft',$node['rgt']);
		$res -> bindParam(':rgt',$par1);
		$res -> execute();
	}
	else{
		echo "<font color='red'>Taki węzeł już istnieje</font>";
	}
}

//add($con,'Office','Access');

function del($element){
	require "pdo.php";
	$res = $pdo->prepare("SELECT lft, rgt FROM elements WHERE title=:title");
	$res -> bindParam(':title',$element);
	$res -> execute();

	$node = $res->fetch();
	$par1 = $node['rgt'] + 1;

	$res = $pdo->query("DELETE FROM `elements` WHERE lft BETWEEN ".$node['lft']." AND ".$node['rgt']);
	$res = $pdo->query("UPDATE `elements` SET `lft` = `lft` - 2 WHERE `lft` > ".$node['rgt']);
	$res = $pdo->query("UPDATE `elements` SET `rgt` = `rgt` - 2 WHERE `rgt` > ".$node['rgt']);

}

//del($con,'InternetExplorer');

function move($element,$destination){

	require "pdo.php";
	$res = $pdo->prepare("SELECT title,
	(SELECT count(parent.id)-1
	FROM elements AS parent
	WHERE node.lft BETWEEN parent.lft AND parent.rgt)
	AS depth
	FROM elements AS node WHERE title=:title");
	$res -> bindParam(':title',$element);
	$res -> execute();
	$node = $res->fetch();
	$source = $node['depth'];

	$res = $pdo->prepare("SELECT title,
	(SELECT count(parent.id)-1
	FROM elements AS parent
	WHERE node.lft BETWEEN parent.lft AND parent.rgt)
	AS depth
	FROM elements AS node WHERE title=:title");
	$res -> bindParam(':title',$destination);
	$res -> execute();
	$node = $res->fetch();
	$dst = $node['depth'];
	
	if($element == $destination){
		echo "<font color='red'>Nie można przenieś. Żródło oraz miejsce docelowe muszą być inne</font>";
	}
	else{
		$res = $pdo->prepare("SELECT lft, rgt FROM elements WHERE title=:title");
		$res -> bindParam(':title',$element);
		$res -> execute();
		$move = $res->fetch();
		$movep = $move['rgt']+1;

		$res = $pdo->prepare("SELECT lft, rgt FROM elements WHERE title=:title");
		$res -> bindParam(':title',$destination);
		$res -> execute();
		$dest = $res->fetch();
		$destp = $dest['rgt']-1;
					
				if($dest['rgt'] > $move['rgt']){

					$res = $pdo->query("insert tmp select * from elements where lft >= ".$move['lft']." and lft <= ".$move['rgt'].";");

					// l -3 , p - 4
					// ld - 18, pd - 19
					$pos = ($dest['rgt'] - $move['rgt']) - 1;
					$pos2 = ($move['rgt'] - $move['lft']) + 1;
					$res = $pdo->query("UPDATE `tmp` SET `lft` = `lft` + ".$pos.";");
					$res = $pdo->query("UPDATE `tmp` SET `rgt` = `rgt` + ".$pos.";");
					$res = $pdo->query("DELETE FROM elements WHERE lft BETWEEN ".$move['lft']." AND ".$move['rgt'].";");
					$res = $pdo->query("UPDATE `elements` SET `lft` = `lft` - ".$pos2." WHERE `lft` BETWEEN ".$movep." AND ".$destp.";");
					$res = $pdo->query("UPDATE `elements` SET `rgt` = `rgt` - ".$pos2." WHERE `rgt` BETWEEN ".$movep." AND ".$destp.";");
					$res = $pdo->query("INSERT elements select * from tmp;");
					$res = $pdo->query("DELETE FROM tmp;");
					
				}else{
					
					$res = $pdo->query("insert tmp select * from elements where lft >= ".$move['lft']." and lft <= ".$move['rgt'].";");

					$pos = $move['lft'] - $dest['rgt'];

					$res = $pdo->query("UPDATE `tmp` SET `lft` = `lft` - ".$pos.";");
					$res = $pdo->query("UPDATE `tmp` SET `rgt` = `rgt` - ".$pos.";");
					$res = $pdo->query("DELETE FROM elements WHERE lft BETWEEN ".$move['lft']." AND ".$move['rgt'].";");

					$pos2 = ($move['rgt'] - $move['lft']) +1;
					$movel = $move['lft']-1;

					$res = $pdo->query("UPDATE `elements` SET `lft` = `lft` + ".$pos2." WHERE `lft` BETWEEN ".$dest['rgt']." AND ".$movel.";");
					$res = $pdo->query("UPDATE `elements` SET `rgt` = `rgt` + ".$pos2." WHERE `rgt` BETWEEN ".$dest['rgt']." AND ".$movel.";");
					$res = $pdo->query("INSERT elements select * from tmp;");
					$res = $pdo->query("DELETE FROM tmp;");
				}
	}
}

//move($con,'InternetExplorer','Excel');

function edit($element, $newName){
	require "pdo.php";
	$res = $pdo->prepare("UPDATE elements SET title = :new_title  WHERE title=:old_title");
	$res -> bindParam(':new_title',$newName);
	$res -> bindParam(':old_title',$element);
	$res -> execute();

}
	
?>