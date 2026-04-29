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
			$resourceid=getpost("resourceID");
			$date=getpost("date");
			$dateto=getpost("dateto");
			
			echo "<form action='editavailability.php' method='POST' >";
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

			echo "<tr><td>ResourceID</td><td>";
			echo "<SELECT NAME='resourceID'>";
					$querystring="SELECT ID,name,company,location FROM resource";
					if($filter!="UNK") $querystring.=" WHERE type='".$filter."'";
					$querystring.=" ORDER BY name,company,location;";
		      foreach($pdo->query($querystring) as $row){
							echo "<OPTION value='".$row['ID']."'>&lt;".$row['name']."&gt; ".$row['company']." - ".$row['location'];
					}					
			echo "</SELECT></td></tr>";

			echo "<td>Date:</td><td><input type='text' value='".$date."' name='date'></td></tr>";
			echo "<td>Date to:</td><td><input type='text' value='".$dateto."' name='dateto'></td></tr>";
			echo "<tr><td><input name='Button' type='submit' value='Save'></td></tr>";
			echo "</table>";
			echo "</form>";									
				
			if($button=='Save'){
					$querystring="INSERT INTO resourceavailability(resourceID,date,dateto) values (:RESOURCEID,DATE_FORMAT(:DATE,'%Y-%m-%d %H:%i'),DATE_FORMAT(:DATETO,'%Y-%m-%d %H:%i'));";
					$stmt = $pdo->prepare($querystring);
					$stmt->bindParam(':RESOURCEID',$resourceid);
					$stmt->bindParam(':DATE',$date);
					$stmt->bindParam(':DATETO',$dateto);
					$stmt->execute();	
			}
			if($button=='Del'){			
					$querystring="DELETE FROM resourceavailability WHERE resourceID=:RESOURCEID and date=:DATE";
					$stmt = $pdo->prepare($querystring);
					$stmt->bindParam(':RESOURCEID',$resourceid);
					$stmt->bindParam(':DATE',$date);
					$stmt->execute();	
			}
			
			//---------------------------------------------------------------------------------------------------------------
			// Search Query!
			//---------------------------------------------------------------------------------------------------------------

			$querystring="SELECT resourceID,DATE_FORMAT(date,'%Y-%m-%d %H:%i') as date,DATE_FORMAT(dateto,'%Y-%m-%d %H:%i') as dateto,name,company,location FROM resourceavailability,resource where resource.ID=resourceavailability.resourceID AND type=:FILTER order by name,company,location,date";
			$stmt = $pdo->prepare($querystring);
			$stmt->bindParam(':FILTER',$filter);
			$stmt->execute();
						
			echo "<table cellspacing=0>\n";

			echo "<p>Note: Date and to-Detes are given as yyyy mm dd hh:mm</p><p>Press filter button with your application seleted to reduce resource list size.</p><br/>";
			
			echo "<table cellspacing=0>\n";
			echo "<tr><td style='border-left: 1px solid green;' class='head'>Resource</td><td class='head'>Name</td><td class='head'>Company</td><td class='head'>Location</td><td class='head'>Date</td><td class='head'>To Date</td></tr>";

			$i=0;

			foreach($stmt as $key => $row){
					// At the moment do nothing!
					if($i%2==0){
							echo "<tr style='background-color:#fff8f8;'>\n";
					}else{
							echo "<tr style='background-color:#ffffff;'>\n";					
					}
					echo "    <td class='result'>".$row['resourceID']."</td>\n";
					echo "    <td class='result'>".$row['name']."</td>\n";
					echo "    <td class='result'>".$row['company']."</td>\n";
					echo "    <td class='result'>".$row['location']."</td>\n";
					echo "    <td class='result'>".$row['date']."</td>\n";
					echo "    <td class='result'>".$row['dateto']."</td>\n";
					echo "<td class='result'>";
					echo "<form action='editavailability.php' method='POST'>";
					echo "<input type='hidden' name='resourceID' value='".$row['resourceID']."'>";
					echo "<input type='hidden' name='date' value='".$row['date']."'>";
					if ($filter!="UNK") echo "<input type='hidden' name='filter' value='".$filter."'>";
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
