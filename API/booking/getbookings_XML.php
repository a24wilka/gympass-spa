<?PHP
			include 'dbconnect.php';

			if(empty($_POST)){
					$_POST=json_decode(file_get_contents('php://input', false),true);
			}			
			
			$resourceID=getpostAJAX("resourceID");
			$searchresource=getpostAJAX("searchresource");
			$date=getpostAJAX("date");
			$type=getpostAJAX("type");

			if($type=="UNK"){
					err("Missing Form Data: (type)");					
			}

			try{
					// Set up query string
					$querystring="SELECT resource.size,resource.type,booking.customerID,booking.resourceID,resource.name,resource.company,resource.location,DATE_FORMAT(booking.date,'%Y-%m-%d %H:%i') as date,DATE_FORMAT(booking.dateto,'%Y-%m-%d %H:%i') as dateto,booking.cost,booking.rebate,booking.position,booking.status,booking.auxdata FROM booking,resource WHERE resource.ID=booking.resourceID AND type=:TYPE ";					

					if($date!="UNK") $querystring.=" AND date=:DATE";
					if($searchresource!="UNK") {
							$querystring.=" AND resourceID like :RESID";
					}else if ($resourceID!="UNK"){
							$querystring.=" AND resourceID=:RESID";
					}
					$querystring.=" ORDER BY resourceid,position";
					
					$stmt = $pdo->prepare($querystring);
		
					// Bind parameters
					if($date!="UNK") $stmt->bindParam(':DATE',$date);
					if($resourceID!="UNK") $stmt->bindParam(':RESID',$resourceID);
					$stmt->bindParam(':TYPE',$type);

					// Execute
					$stmt->execute();
		
					$output="<bookings>\n";
					foreach($stmt as $key => $row){
						  $output.="<booking \n";
							$output.="    application='".presenthtml($row['type'])."'\n";
							$output.="    customerID='".presenthtml($row['customerID'])."'\n";
							$output.="    resourceID='".presenthtml($row['resourceID'])."'\n";
							$output.="    name='".presenthtml($row['name'])."'\n";
							$output.="    company='".presenthtml($row['company'])."'\n";
							$output.="    location='".presenthtml($row['location'])."'\n";
							$output.="    date='".$row['date']."'\n";
							$output.="    dateto='".$row['dateto']."'\n";
							$output.="    position='".$row['position']."'\n";
							$output.="    status='".$row['status']."'\n";
							$output.="    cost='".$row['cost']."'\n";				
							$output.="    size='".$row['size']."'\n";				
							$output.="    auxdata='".presenthtml($row['auxdata'])."'\n";				
							$output.=" />\n";
					}				
					$output.="</bookings>\n";
			} catch (PDOException $e) {
					err("Error!: ".$e->getMessage()."<br/>");
					die();
			}
			
			header ("Content-Type:text/xml; charset=utf-8");  
			echo $output;						
?>
