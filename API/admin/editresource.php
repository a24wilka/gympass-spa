<html>
<style>
		td.result{border-bottom: 1px solid green;}
		td.head{background-color:#ffeedd;border-top: 1px solid green;border-right: 1px solid green;border-bottom: 2px solid black;}
		table {border: 1px solid red;}
</style>
<body>
<?PHP
			
			include '../booking/dbconnect.php';
															
			dbConnect();

			$button=getpost("Button");
			$filter=getpost("filter");
			$ID=getpost("ID");
			$name=getpost("name");
			$type=getpost("type");
			$company=getpost("company");
			$size=getpost("size");
			$cost=getpost("cost");
			$location=getpost("location");
			$category=getpost("category");
			$auxdata=getpost("auxdata");
	
			if($filter==""){
					$filter="No filter applied";
			}
	
			if($button=='Save'){
					$stmt = $pdo->prepare("INSERT INTO resource (id,name,type,company,location,category,size,cost,auxdata) VALUES (:ID,:NAME,:TYPE,:COMPANY,:LOCATION,:CATEGORY,:SIZE,:COST,:AUXDATA);");
					$stmt->bindParam(':ID',$ID);
					$stmt->bindParam(':NAME',$name);
					$stmt->bindParam(':TYPE',$type);
					$stmt->bindParam(':COMPANY',$company);
					$stmt->bindParam(':SIZE',$size);
					$stmt->bindParam(':COST',$cost);
					$stmt->bindParam(':LOCATION',$location);
					$stmt->bindParam(':CATEGORY',$category);
					$stmt->bindParam(':AUXDATA',$auxdata);
					$stmt->execute();	
			}
			if($button=='Del'){	
					$stmt = $pdo->prepare("DELETE FROM resource where ID=:ID;");		
					$stmt->bindParam(':ID',$ID);
					$stmt->execute();	
			}
						
			echo "<form action='editresource.php' method='POST' >";
			echo "<table>";

			echo "<tr><td>Application:&nbsp;&nbsp;</td><td>";
			echo "<SELECT NAME='filter' onchange='this.form.submit()' >";
			echo "<option>&laquo;Select Application&raquo;</option>";
      foreach($pdo->query("SELECT DISTINCT type FROM resource order by type") as $row){
					if($filter==$row['type']){
							echo "<OPTION selected='selected'>".$row['type'];
					}else{
							echo "<OPTION>".$row['type'];
					}
			}
			echo "</SELECT>";

			echo "<input name='Button' type='submit' value='Filter'></td></tr>";					
			
			echo "<tr><td>ID:</td><td><input type='text' value='".$ID."' name='ID'></td>";
			echo "<td>Name:</td><td><input type='text' value='".$name."' name='name'></td></tr>";
			echo "<tr><td>Application:</td><td><input type='text' value='".$filter."' name='type'></td>";
			echo "<td>Company:</td><td><input type='text' value='".$company."' name='company'></td></tr>";
			echo "<tr><td>Size:</td><td><input type='size' value='".$size."' name='size'></td>";
			echo "<td>Cost:</td><td><input type='text' value='".$cost."' name='cost'></td></tr>";
			echo "<tr><td>Location:</td><td><input type='text' value='".$location."' name='location'></td>";
			echo "<td>Category:</td><td><input type='text' value='".$category."' name='category'></td></tr>";
			echo "<td>AuxData:</td><td><input type='text' value='".$auxdata."' name='auxdata'></td></tr>";
			echo "<tr><td><input name='Button' type='submit' value='Save'></td></tr>";
			echo "</table>";
			echo "</form>";									
			
			//---------------------------------------------------------------------------------------------------------------
			// Search Query!
			//---------------------------------------------------------------------------------------------------------------

			$stmt = $pdo->prepare("SELECT * FROM resource WHERE type=:FILTER;");
			$stmt->bindParam(':FILTER',$filter);
			$stmt->execute();	

			echo "<table cellspacing=0>\n";
			echo "<tr><td style='border-left: 1px solid green;' class='head'>ID</td><td class='head'>Name</td><td class='head'>Type</td><td class='head'>Company</td><td class='head'>Location</td><td class='head'>Category</td><td class='head'>Size</td><td class='head'>Cost</td><td class='head'>Auxdata</td></tr>";
			$i=0;

			foreach($stmt as $key => $row){

					echo "    <td class='result'>".$row['ID']."</id>\n";
					echo "    <td class='result'>".$row['name']."</td>\n";
					echo "    <td class='result'>".$row['type']."</td>\n";
					echo "    <td class='result'>".$row['company']."</td>\n";
					echo "    <td class='result'>".$row['location']."</td>\n";
					echo "    <td class='result'>".$row['category']."</td>\n";	
					echo "    <td class='result'>".$row['size']."</td>\n";
					echo "    <td class='result'>".$row['cost']."</td>\n";
					echo "    <td class='result'>".$row['auxdata']."</td>\n";
					echo "<td class='result'>";
					echo "<form action='editresource.php' method='POST'>";
					echo "<input type='hidden' name='ID' value='".$row['ID']."'>";
					echo "<input type='hidden' name='name' value='".$row['name']."'>";
					echo "<input type='hidden' name='type' value='".$row['type']."'>";
					echo "<input type='hidden' name='company' value='".$row['company']."'>";
					echo "<input type='hidden' name='location' value='".$row['location']."'>";
					echo "<input type='hidden' name='category' value='".$row['category']."'>";
					echo "<input type='hidden' name='size' value='".$row['size']."'>";
					echo "<input type='hidden' name='cost' value='".$row['cost']."'>";
					echo "<input type='hidden' name='auxdata' value='".$row['auxdata']."'>";
					if (isset($_POST['filter']))echo "<input type='hidden' name='filter' value='".$_POST['filter']."'>";
					echo "<input name='Button' type='submit' value='Del'>";
					echo "</form>";
					echo "</td>";
					echo "</tr>\n";
					$i++;
			}				
			echo "</table>\n";
	
			echo "<div style='width:300px;border:1px solid red; background:#fed;margin:10px'>$i lines found for &quot;$filter&quot; application</div>"
			
?>
</body>
</html>
