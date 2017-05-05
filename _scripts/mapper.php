<?php
global $print;

include('../in/config.php');
include('../in/modules/helpers.php');
include('functions.php');


// Initialize the database handle
$db = new PDOTester('mysql:host='. DB_HOST .';dbname=pdm_dev;charset=utf8', DB_USER, DB_PASS);

echo '<table border=1 cellpadding=3 width="100%"><thead></thead><tbody>';
 

$query_dept = $db->prepare("SELECT distinct(unit_gd) FROM `main_departments`"); // where is main_departments??
$query_dept->execute();
$structure = $query_dept->fetchAll();

$query_wiki = $db->prepare("SELECT * FROM `wiki_hierarchy`");
$query_wiki->execute();
$wikiz = $query_wiki->fetchAll();	

// This function is responsible for determining if there is already a mapping
// between the given pdm department and a wiki department-cat and it returns
// the retrieved data (id, pdm_id, wiki_id, type)
function mapper_exists($pdm_id){
	global $db;

	$query = $db->prepare('SELECT * FROM `wiki_departments` where pdm_id=:pdm_id '  );
	
	$query->bindValue(':pdm_id', 			$pdm_id, 			PDO::PARAM_STR);  
	$query->execute();
	$service = $query->fetchObject();	
	
	if(empty($service)) return false;
	
	return true;
}

// Handler for the pdm_dept-wiki_cat submission.
if(isset($_GET['dept_id']) and isset($_GET['wiki_id']) and isset($_GET['type_id'])){
	
	// Checking to see whether mapping already exists.
	if(mapper_exists(trim($_GET['dept_id']))) { echo 'Exists!<br />'; } else {
		
		// Else a new record is added in the table
		$query = $db->prepare("INSERT INTO wiki_departments (id, pdm_id, wiki_id, type) VALUES (NULL, :pdm_id, :wiki_id, :type_id)");
								
		$query->bindValue(':wiki_id', 			trim($_GET['wiki_id']), 			PDO::PARAM_INT);  
		$query->bindValue(':pdm_id', 			trim($_GET['dept_id']), 				PDO::PARAM_STR);   
		$query->bindValue(':type_id', 			trim($_GET['type_id']), 				PDO::PARAM_INT); 
		
		$query->execute();
		
		$id = $db->lastInsertId();
		
		if ($id == 0) {
			echo 'Error for '.$query->getSQL().'<br />';
		} else 
			echo 'Saved!<br />';
	}
}

// The function that prints the form to 
function print_form($pdm_id, $wikiz, $type, $parent){
?>
<!-- CAUTION: THE HANDLER IS HARDCODED. TO BE ALTERED FOR OTHER ORGANIZATIONS -->
<form action="http://apps.pdm.gov.gr/episey/mapper.php" method="get"> 
	<input type="hidden" name="dept_id" value="<?php echo $pdm_id; ?>" >
	<input type="hidden" name="type_id" value="<?php echo $type; ?>" >
	<select name="wiki_id">
		<?php
			// Renders all the wiki parent items in a drop down list to make a choice for the mapping.
			foreach($wikiz as $wiki){ if($wiki['wiki_parent'] != $parent) continue;
				echo '<option value="'.$wiki['wiki_id'].'" >'.$wiki['wiki_title'].'</option>';
			}
		?>
	</select>
	<input type="submit" value="Save" />
</form>
<?php
}

function print_wiki($pdm_id){
	global $db;
	$query = $db->prepare('SELECT * FROM `wiki_departments` where pdm_id=:pdm_id '  );
	$query->bindValue(':pdm_id', 			$pdm_id, 			PDO::PARAM_STR);  
	$query->execute();
	$mapper = $query->fetchObject();
	
	$query = $db->prepare('SELECT * FROM `wiki_hierarchy` where wiki_id=:wiki_id '  );

	$query->bindValue(':wiki_id', 			$mapper->wiki_id, 			PDO::PARAM_INT);  
	$query->execute();
	$service = $query->fetchObject();	
	return $service;
}

$saved = array();

foreach($structure as $gen_id){  
	$gen_dept = get_gen_department($gen_id['unit_gd'] );
	$wiki_id_now = 0;
?>

	<tr style="background: black; color: #ffffff;">
		<td><strong><?php echo  $gen_dept['unit_gd']; ?></strong></td>
		<td><strong><?php echo  $gen_dept['gen_department']; ?></strong></td>
		<td>
			<?php
			if(mapper_exists($gen_dept['unit_gd'])){
				$wiki_item = print_wiki($gen_dept['unit_gd']);
				$wiki_id_now = $wiki_item->wiki_id;
				echo $wiki_item->wiki_title;
			}else
				print_form($gen_dept['unit_gd'], $wikiz, 0, $wiki_id_now);
			?>
		</td>
	</tr>
<?php
	$dief_dept_list = get_department($gen_dept['unit_gd']);
	foreach($dief_dept_list as $dief_dept){
		if(empty($dief_dept['unit_g'])) continue;
?>
	<tr style="background: yellow; color: #000000;">
		<td><strong><?php echo  $dief_dept['unit_g']; ?></strong></td>
		<td><strong><?php echo  $dief_dept['department']; ?></strong></td>
		<td>
			<?php
			if(mapper_exists($dief_dept['unit_g'])){
				$wiki_item_2 = print_wiki($dief_dept['unit_g']);
				$wiki_id_2 = $wiki_item_2->wiki_id;
				echo $wiki_item_2->wiki_title;
			}else
				print_form($dief_dept['unit_g'], $wikiz, 1, $wiki_id_now);
			?>
		</td>
	</tr>
<?php
	}
		/*
		foreach($dief_dept as $tmim_id => $tmim_dept){ if($tmim_id == 'user') continue;
			$tmimatos = $users[$tmim_dept['user']];
			
			echo '<tr style="background: #e4e1e1;">';
			echo '<td></td><td></td><td  colspan="2"><strong>Τμήμα</strong></td>';
			echo '<td>'.get_office($dief_id , $gen_id, $tmim_id).'</td>';
			echo '<td></td>';
			echo '<td></td>';
			echo '<td></td>';
			echo '<td></td>';
			echo '</tr>';
			
		} 
	} */
}
echo '</tbody></table>';

?>