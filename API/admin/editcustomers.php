<html>
<style>
		td.result{border-bottom: 1px solid green;border-left: 3px solid #eee7e7; padding-top: 2px;padding-bottom:2px;padding-left: 4px;}
		td.head{background-color:#ffeedd;border-top: 1px solid green;border-right: 1px solid green;border-bottom: 2px solid black;}
		table {border: 1px solid red;}
</style>
<body>
<?PHP

			include '../booking/dbconnect.php';
															
			dbConnect();

			$button=getpost("Button");

			if($button=='Save'||$button=='Del'){
					echo "<form action='editcustomers.php' method='POST' >";
					echo "<table>";
					echo "<tr><td>ID:</td><td><input type='text' value='".$_POST['ID']."' name='ID'></td>";
					echo "<td>Last Visit Time:</td><td><input type='text' value='".$_POST['lastvisit']."' name='lastvisit'></td></tr>";
					echo "<tr><td>Firstname:</td><td><input type='text' value='".$_POST['firstname']."' name='firstname'></td>";
					echo "<td>Lastname:</td><td><input type='text' value='".$_POST['lastname']."' name='lastname'></td></tr>";
					echo "<tr><td>Address:</td><td><input type='text' value='".$_POST['address']."' name='address'></td></tr>";
					echo "<tr><td>Emal:</td><td><input type='text' value='".$_POST['email']."' name='email'></td></tr>";
					echo "<tr><td>Auxillary Data:</td><td><input type='text' value='".$_POST['auxdata']."' name='auxdata'></td></tr>";
					echo "<tr><td><input name='Button' type='submit' value='Save'></td></tr>";
					echo "</table>";
					echo "</form>";									
			}else{
					echo "<form action='editcustomers.php' method='POST' >";
					echo "<table>";
					echo "<tr><td>ID:</td><td><input type='text' value='ID' name='ID'></td>";
					echo "<td>Last Visit Time:</td><td><input type='text' value='lastvisit' name='lastvisit'></td></tr>";
					echo "<tr><td>Firstname:</td><td><input type='text' value='firstname' name='firstname'></td>";
					echo "<td>Lastname:</td><td><input type='text' value='lastname' name='lastname'></td></tr>";
					echo "<tr><td>Address:</td><td><input type='text' value='address' name='address'></td></tr>";
					echo "<tr><td>Email:</td><td><input type='text' value='email' name='email'></td></tr>";
					echo "<tr><td>Auxillary Data:</td><td><input type='text' value='auxdata' name='auxdata'></td></tr>";
					echo "<tr><td><input name='Button' type='submit' value='Save'></td></tr>";
					echo "</table>";
					echo "</form>";								
			}
			
			if($button=='Save'){

					$stmt = $pdo->prepare("INSERT INTO customer(ID,lastvisit,firstname,lastname,address,email,auxdata) values (:ID,:LASTVISIT,:FIRSTNAME,:LASTNAME,:ADDRESS,:EMAIL,:AUXDATA);");

					$stmt->bindParam(':ID',$_POST['ID']);
					$stmt->bindParam(':LASTVISIT',$_POST['lastvisit']);
					$stmt->bindParam(':FIRSTNAME',$_POST['firstname']);
					$stmt->bindParam(':LASTNAME',$_POST['lastname']);
					$stmt->bindParam(':ADDRESS',$_POST['address']);
					$stmt->bindParam(':EMAIL',$_POST['email']);
					$stmt->bindParam(':AUXDATA',$_POST['email']);

					$stmt->execute();	
					
			}
			if($button=='Del'){			
					$stmt = $pdo->prepare("DELETE FROM customer where ID=:ID");
					$stmt->bindParam(':ID',$_POST['ID']);
					$stmt->execute();	
			}
			
			//---------------------------------------------------------------------------------------------------------------
			// Search Query!
			//---------------------------------------------------------------------------------------------------------------

			echo "<table cellspacing=0>\n";
			echo "<tr><td style='border-left: 1px solid green;' class='head'>ID</td><td class='head'>Lastvisit</td><td class='head'>Firstname</td><td class='head'>Lastname</td><td class='head'>address</td><td class='head'>email</td><td class='head'>Auxillary Data</td></tr>";
			$i=0;

      foreach($pdo->query("SELECT * FROM customer") as $row){

					// At the moment do nothing!
					if($i%2==0){
							echo "<tr style='background-color:#fff8f8;'>\n";
					}else{
							echo "<tr style='background-color:#ffffff;'>\n";					
					}
					
					echo "    <td class='result'>".$row['ID']."</id>\n";
					echo "    <td class='result'>".$row['lastvisit']."</td>\n";
					echo "    <td class='result'>".$row['firstname']."</td>\n";
					echo "    <td class='result'>".$row['lastname']."</td>\n";
					echo "    <td class='result'>".$row['address']."</td>\n";
					echo "    <td class='result'>".$row['email']."</td>\n";
					echo "    <td class='result'>".$row['auxdata']."</td>\n";
					echo "<td class='result'>";
					echo "<form action='editcustomers.php' method='POST'>";
					echo "<input type='hidden' name='ID' value='".$row['ID']."'>";
					echo "<input type='hidden' name='lastvisit' value='".$row['lastvisit']."'>";
					echo "<input type='hidden' name='firstname' value='".$row['firstname']."'>";
					echo "<input type='hidden' name='lastname' value='".$row['lastname']."'>";
					echo "<input type='hidden' name='address' value='".$row['address']."'>";
					echo "<input type='hidden' name='email' value='".$row['email']."'>";
					echo "<input type='hidden' name='auxdata' value='".$row['auxdata']."'>";
					echo "<input name='Button' type='submit' value='Del'>";
					echo "</form>";
					echo "</td>";
					echo "</tr>\n";
					$i++;

			}

			echo "</table>\n";
			
?>
</body>
</html>
