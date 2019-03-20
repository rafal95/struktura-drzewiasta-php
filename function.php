<?php
function showTree($con,$depth=0){

	$sql="SELECT title,
	 (SELECT count(parent.id)-1
	  FROM elements AS parent
	  WHERE node.lft BETWEEN parent.lft AND parent.rgt)
	 AS depth
	FROM elements AS node
	ORDER BY node.lft";
	
	$result = mysqli_query($con,$sql);
	
	$tree = array();
	while ($row = mysqli_fetch_assoc($result)) {
		$tree[] = $row;
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

//showTree($con);

function showNode($con,$nodeName,$depth=0){

	$sql = "SELECT lft, rgt FROM elements WHERE title='".$nodeName."'";
	$node = mysqli_query($con,$sql);

	$n = mysqli_fetch_object($node);
	
	$sql="SELECT title,
	 (SELECT count(parent.id)-1
	  FROM elements AS parent
	  WHERE node.lft BETWEEN parent.lft AND parent.rgt)
	 AS depth
	FROM elements AS node
	WHERE node.lft BETWEEN ".$n->lft." AND ".$n->rgt." ORDER BY node.lft";
	
	$result = mysqli_query($con,$sql);
	
	$tree = array();
	while ($row = mysqli_fetch_assoc($result)) {
		$tree[] = $row;
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

//showNode($con,'Linux');

function add($con,$nodeName,$newElement){

	$sql = "SELECT rgt FROM elements WHERE title='".$nodeName."'";
	$node = mysqli_query($con,$sql);

	$n = mysqli_fetch_object($node);
	$par1 = $n->rgt+1;
	
	$sql="UPDATE `elements` SET `lft` = `lft` + 2 WHERE `lft` >= ".$par1;
	mysqli_query($con,$sql);
	
	$sql="UPDATE `elements` SET `rgt` = `rgt` + 2 WHERE `rgt` >= ".$par1;
	mysqli_query($con,$sql);
	
	$sql ="INSERT INTO `elements` (`title`,`lft`,`rgt`) VALUES ('".$newElement."',".$n->rgt.",".$par1.")";
	mysqli_query($con,$sql);
}

//add($con,'Office','Access');

function del($con,$element){

	$sql = "SELECT lft, rgt FROM elements WHERE title='".$element."'";
	$node = mysqli_query($con,$sql);

	$n = mysqli_fetch_object($node);
	$par1 = $n->rgt+1;
	
	$sql ="DELETE FROM `elements` WHERE lft BETWEEN ".$n->lft." AND ".$n->rgt;
	mysqli_query($con,$sql);
	
	$sql="UPDATE `elements` SET `lft` = `lft` - 2 WHERE `lft` > ".$n->rgt;
	mysqli_query($con,$sql);
	
	$sql="UPDATE `elements` SET `rgt` = `rgt` - 2 WHERE `rgt` > ".$n->rgt;
	mysqli_query($con,$sql);
	
}

//del($con,'InternetExplorer');

function move($con,$element,$destination){
	
	$sql="SELECT title,
	(SELECT count(parent.id)-1
	FROM elements AS parent
	WHERE node.lft BETWEEN parent.lft AND parent.rgt)
	AS depth
	FROM elements AS node WHERE title='".$element."'";
	$result = mysqli_query($con,$sql);
	$row = mysqli_fetch_object($result);
	$source = $row->depth;
	
	$sql="SELECT title,
	(SELECT count(parent.id)-1
	FROM elements AS parent
	WHERE node.lft BETWEEN parent.lft AND parent.rgt)
	AS depth
	FROM elements AS node WHERE title='".$destination."'";
	$result = mysqli_query($con,$sql);
	$row = mysqli_fetch_object($result);
	$dst = $row->depth;
	
	if($element == $destination){
		echo "<font color='red'>Nie można przenieś. Żródło oraz miejsce docelowe muszą być inne</font>";
	}
	else{
		$sql = "SELECT lft, rgt FROM elements WHERE title='".$element."'";
		$node = mysqli_query($con,$sql);
		$move = mysqli_fetch_object($node);
		$movep = $move->rgt+1;
			
		$sql = "SELECT lft, rgt FROM elements WHERE title='".$destination."'";
		$node = mysqli_query($con,$sql);
		$dest = mysqli_fetch_object($node);
		$destp = $dest->rgt-1;
		
			if($dst + 1 == $source and $dest->lft < $move->lft){
				echo "<font color='red'>Przenoszony element już znajduje się w tym węźle</font>";
			}
			else{			
				if($dest->rgt > $move->rgt){
					$sql = "insert tmp
					select * from elements
					where lft >= ".$move->lft." and lft <= ".$move->rgt.";";
					mysqli_query($con,$sql);
					// l -3 , p - 4
					// ld - 18, pd - 19
					$pos = ($dest->rgt - $move->rgt) - 1;
					$pos2 = ($move->rgt - $move->lft) + 1;
					$sql = "UPDATE `tmp` SET `lft` = `lft` + ".$pos.";";
					mysqli_query($con,$sql);
					$sql = "UPDATE `tmp` SET `rgt` = `rgt` + ".$pos.";";
					mysqli_query($con,$sql);
					$sql = "DELETE FROM elements WHERE lft BETWEEN ".$move->lft." AND ".$move->rgt.";";
					mysqli_query($con,$sql);
					$sql = "UPDATE `elements` SET `lft` = `lft` - ".$pos2." WHERE `lft` BETWEEN ".$movep." AND ".$destp.";";
					mysqli_query($con,$sql);
					$sql = "UPDATE `elements` SET `rgt` = `rgt` - ".$pos2." WHERE `rgt` BETWEEN ".$movep." AND ".$destp.";";
					mysqli_query($con,$sql);
					$sql = "INSERT elements
							select * from tmp;";
					mysqli_query($con,$sql);
					$sql = "DELETE FROM tmp;";
					mysqli_query($con,$sql);
					
				}else{
					
					$sql ="insert tmp
					select * from elements
					where lft >= ".$move->lft." and lft <= ".$move->rgt.";";
					mysqli_query($con,$sql);
					$pos = $move->lft - $dest->rgt;
					$sql = "UPDATE `tmp` SET `lft` = `lft` - ".$pos.";";
					mysqli_query($con,$sql);
					$sql = "UPDATE `tmp` SET `rgt` = `rgt` - ".$pos.";";
					mysqli_query($con,$sql);
					$sql = "DELETE FROM elements WHERE lft BETWEEN ".$move->lft." AND ".$move->rgt.";";
					mysqli_query($con,$sql);
					$pos2 = ($move->rgt - $move->lft) +1;
					$movel = $move->lft-1;
					$sql = "UPDATE `elements` SET `lft` = `lft` + ".$pos2." WHERE `lft` BETWEEN ".$dest->rgt." AND ".$movel.";";
					mysqli_query($con,$sql);
					$sql = "UPDATE `elements` SET `rgt` = `rgt` + ".$pos2." WHERE `rgt` BETWEEN ".$dest->rgt." AND ".$movel.";";
					mysqli_query($con,$sql);
							$sql = "INSERT elements
							select * from tmp;";
					mysqli_query($con,$sql);
					$sql = "DELETE FROM tmp;";
					mysqli_query($con,$sql);
				}
			}
	}
}

//move($con,'InternetExplorer','Excel');

function edit($con, $element, $newName){
	$sql="UPDATE `elements` SET `title` = '".$newName."' WHERE title='".$element."'";
	mysqli_query($con,$sql);
}
	
?>