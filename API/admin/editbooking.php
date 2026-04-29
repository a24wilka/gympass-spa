<!DOCTYPE html> 
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

			$position=getpost("position");
			$cost=getpost("cost");
			$rebate=getpost("rebate");
			$auxdata=getpost("auxdata");
			$resourceid=getpost("resourceID");

			$customer=getpost("customer");
			$date=getpost("date");
			$status=getpost("status");
			$auxdata=getpost("auxdata");
						
			//---------------------------------------------------------------------------------------------------------------
			// Build Form
			//---------------------------------------------------------------------------------------------------------------

			echo "<form action='editbooking.php' method='POST' >";
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

			$querystring="SELECT ID,name,company,location FROM resource WHERE type=:FILTER ORDER BY name,company,location";
			$stmt = $pdo->prepare($querystring);
			$stmt->bindParam(':FILTER',$filter);
			$stmt->execute();
					
			echo "<tr><td>ResourceID</td><td>";
				echo "<SELECT NAME='resourceID'>";
				foreach($stmt as $key => $row){
						if(isset($_POST['resourceID'])){
								if($resourceid==$row['ID']){
										echo "<OPTION selected='selected' value='".$row['ID']."'>&lt;".$row['name']."&gt; ".$row['company']." - ".$row['location'];
								}else{
										echo "<OPTION value='".$row['ID']."'>&lt;".$row['name']."&gt; ".$row['company']." - ".$row['location'];
								}
						}else{
								echo "<OPTION value='".$row['ID']."'>&lt;".$row['name']."&gt; ".$row['company']." - ".$row['location'];
						}
				}
				echo "</SELECT>";
			echo "</td></tr>";
			echo "<tr><td>";
			echo "Customer:&nbsp;</td><td>";
				echo "<SELECT NAME='customer'>";
	      foreach($pdo->query("SELECT * FROM customer order by ID") as $row){
						if(isset($_POST['customer'])){
								if($_POST['customer']==$row['ID']){
										echo "<OPTION selected='selected' value='".$row['ID']."'>&lt;".$row['ID']."&gt; ".$row['firstname']." ".$row['lastname'];							
								}else{
										echo "<OPTION value='".$row['ID']."'>&lt;".$row['ID']."&gt; ".$row['firstname']." ".$row['lastname'];							
								}
						}else{
								echo "<OPTION value='".$row['ID']."'>&lt;".$row['ID']."&gt; ".$row['firstname']." ".$row['lastname'];
						}
				}
				echo "</SELECT>";
			echo "</td></tr>";
			

			echo "<tr><td>Date: </td><td><input type='text' name='date' value=".$date."></td></tr>";														


			if(isset($_POST['position'])){
					echo "<tr><td>Position:</td><td><input type='text' value='".$position."' name='position'></td><tr>";
			}else{
					echo "<tr><td>Position:</td><td><input type='text' value='position' name='position'></td><tr>";
			}

			if(isset($_POST['cost'])){
					echo "<tr><td>Cost:</td><td><input type='text' value='".$cost."' name='cost'></td><tr>";
			}else{
					echo "<tr><td>Cost:</td><td><input type='text' value='cost' name='cost'></td><tr>";
			}

			if(isset($_POST['rebate'])){
					echo "<tr><td>Rebate:</td><td><input type='text' value='".$rebate."' name='rebate'></td><tr>";
			}else{
					echo "<tr><td>Rebate:</td><td><input type='text' value='rbate' name='rebate'></td><tr>";
			}

			if(isset($_POST['auxdata'])){
					echo "<tr><td>Aux Data:</td><td><input type='text' value='".$auxdata."' name='auxdata'></td><tr>";
			}else{
					echo "<tr><td>Aux Data:</td><td><input type='text' value='auxdata' name='auxdata'></td><tr>";
			}

			
			echo "<tr><td>Status:</td><td>";
			echo "<SELECT name='status'>";
			echo "<OPTION value='0'>Preliminary";
			echo "<OPTION value='1'>Booked";
			echo "</SELECT>";
			echo "</td></tr>";

			echo "<tr><td><input name='Button' type='submit' value='Save'></td></tr>";							
			echo "</table>";
			echo "</form>";								

			//---------------------------------------------------------------------------------------------------------------
			// Execuute!
			//---------------------------------------------------------------------------------------------------------------

			if($button=='Save'){
					$querystring="INSERT INTO booking(customerID,resourceID,position,date,cost,rebate,status,auxdata) values (:CUSTOMER,:RESOURCEID,:POSITION,DATE_FORMAT(:DATE,'%Y-%m-%d %H:%i'),:COST,:REBATE,:STATUS,:AUXDATA);";
					$stmt = $pdo->prepare($querystring);

					$stmt->bindParam(':CUSTOMER',$customer);
					$stmt->bindParam(':RESOURCEID',$resourceid);
					$stmt->bindParam(':POSITION',$position);
					$stmt->bindParam(':DATE',$date);
					$stmt->bindParam(':COST',$cost);
					$stmt->bindParam(':REBATE',$rebate);
					$stmt->bindParam(':STATUS',$status);
					$stmt->bindParam(':AUXDATA',$auxdata);

					$stmt->execute();	
			}
			if($button=='Del'){
					try{
		
							$querystring="DELETE FROM booking WHERE resourceID=:RESOURCEID and date=:DATE and position=:POSITION and customerID=:CUSTOMER;";
							$stmt = $pdo->prepare($querystring);
		
							$stmt->bindParam(':DATE',$date);
							$stmt->bindParam(':RESOURCEID',$resourceid);
							$stmt->bindParam(':POSITION',$position);
							$stmt->bindParam(':CUSTOMER',$customer);
		
							$stmt->execute();	
							} catch (PDOException $e) {
									echo("Error!: ".$e->getMessage()."<br/>");
									die();
							}
			}			

			//---------------------------------------------------------------------------------------------------------------
			// Search Query!
			//---------------------------------------------------------------------------------------------------------------

			$querystring="SELECT * FROM booking,resource WHERE resource.ID=booking.resourceID AND type=:FILTER ORDER BY customerID,company,location,booking.date,position";
			$stmt = $pdo->prepare($querystring);
			$stmt->bindParam(':FILTER',$filter);
			$stmt->execute();
					
			echo "<p>Press filter to update the resource list and the available booking list.</p>";

			echo "<table cellspacing=0>\n";
			echo "<tr><td style='border-left: 1px solid green;' class='head'>Customer</td><td class='head'>Resource</td><td class='head'>Name</td><td class='head'>Company</td><td class='head'>Location</td><td class='head'>Date</td><td class='head'>Date To</td><td class='head'>Position</td><td class='head'>Cost</td><td class='head'>Rebate</td><td class='head'>Status</td><td class='head'>Aux</td></tr>";
			
			$i=0;
			foreach($stmt as $key => $row){
					if($i%2==0){
							echo "<tr style='background-color:#fff8f8;'>\n";
					}else{
							echo "<tr style='background-color:#ffffff;'>\n";					
					}
					echo "    <td class='result'>".$row['customerID']."</id>\n";
					echo "    <td class='result'>".$row['resourceID']."</id>\n";
					echo "    <td class='result'>".$row['name']."</id>\n";
					echo "    <td class='result'>".$row['company']."</id>\n";
					echo "    <td class='result'>".$row['location']."</id>\n";
					echo "    <td class='result'>".$row['date']."</td>\n";
					echo "    <td class='result'>".$row['dateto']."</td>\n";
					echo "    <td class='result'>".$row['position']."</td>\n";
					echo "    <td class='result'>".$row['cost']."</td>\n";
					echo "    <td class='result'>".$row['rebate']."</td>\n";
					echo "    <td class='result'>".$row['status']."</td>\n";
					echo "    <td class='result'>".$row['auxdata']."</td>\n";
					echo "    <td class='result'>".$row['type']."</td>\n";				
					echo "<td class='result'>";
					echo "<form action='editbooking.php' method='POST'>";
					echo "<input type='hidden' name='customer' value='".$row['customerID']."'>";
					echo "<input type='hidden' name='resourceID' value='".$row['resourceID']."'>";
					echo "<input type='hidden' name='date' value='".$row['date']."'>";
					echo "<input type='hidden' name='dateto' value='".$row['dateto']."'>";
					echo "<input type='hidden' name='position' value='".$row['position']."'>";
					echo "<input type='hidden' name='cost' value='".$row['cost']."'>";
					echo "<input type='hidden' name='rebate' value='".$row['rebate']."'>";
					echo "<input type='hidden' name='status' value='".$row['status']."'>";
					echo "<input type='hidden' name='auxdata' value='".$row['auxdata']."'>";

					if (isset($_POST['filter']))echo "<input type='hidden' name='filter' value='".$_POST['filter']."'>";
					echo "<input name='Button' type='submit' value='Del'>";
					echo "</form>";
					echo "</td>";
					echo "</tr>\n";
					$i++;
			
			}

			echo "</table>\n";

			echo "<div style='width:300px;border:1px solid red; background:#fed;margin:10px;padding:10px;'>$i lines found for &quot;$filter&quot; application</div>";	
				
			$querystring="SELECT * FROM booking WHERE NOT EXISTS (SELECT * FROM resource where booking.resourceID=resource.id) ORDER BY customerID,resourceID,booking.date,position";
			$stmt = $pdo->prepare($querystring);
			$stmt->execute();				
	
			$str="<div style='width:300px;border:1px solid navy; background:#def;margin:10px;padding:10px;'>Orphaned bookings</div>";

			$str.= "<table cellspacing=0>\n";
			$str.= "<tr><td style='border-left: 1px solid green;' class='head'>Customer</td><td class='head'>Resource</td><td class='head'>Date</td><td class='head'>Date To</td><td class='head'>Position</td><td class='head'>Cost</td><td class='head'>Rebate</td><td class='head'>Status</td><td class='head'>Aux</td></tr>";			

			$i=0;
			foreach($stmt as $key => $row){
					if($i%2==0){
							$str.= "<tr style='background-color:#fff8f8;'>\n";
					}else{
							$str.= "<tr style='background-color:#ffffff;'>\n";					
					}
					$str.= "    <td class='result'>".$row['customerID']."</id>\n";
					$str.= "    <td class='result'>".$row['resourceID']."</id>\n";
					$str.= "    <td class='result'>".$row['date']."</td>\n";
					$str.= "    <td class='result'>".$row['dateto']."</td>\n";
					$str.= "    <td class='result'>".$row['position']."</td>\n";
					$str.= "    <td class='result'>".$row['cost']."</td>\n";
					$str.= "    <td class='result'>".$row['rebate']."</td>\n";
					$str.= "    <td class='result'>".$row['status']."</td>\n";
					$str.= "    <td class='result'>".$row['auxdata']."</td>\n";
					$str.= "<td class='result'>";
					$str.= "<form action='editbooking.php' method='POST'>";
					$str.= "<input type='hidden' name='customer' value='".$row['customerID']."'>";
					$str.= "<input type='hidden' name='resourceID' value='".$row['resourceID']."'>";
					$str.= "<input type='hidden' name='date' value='".$row['date']."'>";
					$str.= "<input type='hidden' name='dateto' value='".$row['dateto']."'>";
					$str.= "<input type='hidden' name='position' value='".$row['position']."'>";
					$str.= "<input type='hidden' name='cost' value='".$row['cost']."'>";
					$str.= "<input type='hidden' name='rebate' value='".$row['rebate']."'>";
					$str.= "<input type='hidden' name='status' value='".$row['status']."'>";
					$str.= "<input type='hidden' name='auxdata' value='".$row['auxdata']."'>";

					if (isset($_POST['filter'])) $str.= "<input type='hidden' name='filter' value='".$_POST['filter']."'>";
					$str.= "<input name='Button' type='submit' value='Del'>";
					$str.= "</form>";
					$str.= "</td>";
					$str.= "</tr>\n";
					$i++;

			}

			$str.= "</table>";

			$str.= "<div style='width:300px;border:1px solid red; background:#fed;margin:10px'>$i orphaned bookings found</div>";			
			
			if($i>0){
					echo $str;
			}
	
?>
</body>
</html>
