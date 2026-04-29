<?PHP
		include 'dbconnect.php';

		if(empty($_POST)){
				$_POST=json_decode(file_get_contents('php://input', false),true);
		}		

		// Normal Search or ID Search
		$resid=getpostAJAX("resid");	
		$name="%".getpostAJAX("name")."%";
		$location="%".getpostAJAX("location")."%";
		$company="%".getpostAJAX("company")."%";
		$type=getpostAJAX("type");

		// Full text Search
		$fulltext=getpostAJAX("fulltext");
		if($fulltext!="UNK"){
				$company="%".$fulltext."%";
				$location="%".$fulltext."%";
				$name="%".$fulltext."%";
				$resID="%".$fulltext."%";
		}

		if($type=="UNK"){
				err("Missing Form Data: (type)");					
		}

		try{
				// Search either for ID or for resource!
				if($fulltext!="UNK"){
					$querystring="SELECT DATE_FORMAT(date,'%Y-%m-%d %H:%i') as date,DATE_FORMAT(dateto,'%Y-%m-%d %H:%i') as dateto,resourceID,name,location,company,size,cost,category,auxdata FROM resource,resourceavailability where resourceavailability.resourceID=resource.ID and (resource.company like :COMPANY or resource.name like :NAME or resource.location like :LOCATION or resource.ID=:RESID) and resource.type=:TYPE order by resourceID,date";
					$stmt = $pdo->prepare($querystring);
					$stmt->bindParam(':TYPE',$type);
					$stmt->bindParam(':RESID',$resid);
					$stmt->bindParam(':COMPANY',$company);
					$stmt->bindParam(':NAME',$name);
					$stmt->bindParam(':LOCATION',$location);
					$stmt->execute();
				}else if($resid!="UNK"){
					$querystring="SELECT DATE_FORMAT(date,'%Y-%m-%d %H:%i') as date,DATE_FORMAT(dateto,'%Y-%m-%d %H:%i') as dateto,resourceID,name,location,company,size,cost,category,auxdata FROM resource,resourceavailability where resourceavailability.resourceID=resource.ID and resource.ID=:RESID and resource.type=:TYPE order by resourceID,date";
					$stmt = $pdo->prepare($querystring);
					$stmt->bindParam(':TYPE',$type);
					$stmt->bindParam(':RESID',$resid);
					$stmt->execute();
				}else if(($resid=="UNK")&&($name=="%UNK%")&&($location=="%UNK%")&&($company=="%UNK%")&&($fulltext=="UNK")){
					$querystring="SELECT DATE_FORMAT(date,'%Y-%m-%d %H:%i') as date,DATE_FORMAT(dateto,'%Y-%m-%d %H:%i') as dateto,resourceID,name,location,company,size,cost,category,auxdata FROM resource,resourceavailability where resourceavailability.resourceID=resource.ID and resource.type=:TYPE order by resourceID,date";
					$stmt = $pdo->prepare($querystring);
					$stmt->bindParam(':TYPE',$type);
					$stmt->execute();
				}else{
					$querystring="SELECT DATE_FORMAT(date,'%Y-%m-%d %H:%i') as date,DATE_FORMAT(dateto,'%Y-%m-%d %H:%i') as dateto,resourceID,name,location,company,size,cost,category,auxdata FROM resource,resourceavailability where resourceavailability.resourceID=resource.ID and (resource.company like :COMPANY or resource.name like :NAME or resource.location like :LOCATION) and resource.type=:TYPE  order by resourceID,date";
					$stmt = $pdo->prepare($querystring);
					$stmt->bindParam(':TYPE',$type);
					$stmt->bindParam(':COMPANY',$company);
					$stmt->bindParam(':NAME',$name);
					$stmt->bindParam(':LOCATION',$location);
					$stmt->execute();
				}
				
				$output="<avail>\n";
				foreach($stmt as $key => $row){	
						$output.="<availability \n";
						
						$querystring="SELECT count(*) as counted FROM booking where resourceid=:RESID and date=:DATE";
						$stmts = $pdo->prepare($querystring);
						$stmts->bindParam(':RESID',$row['resourceID']);
						$stmts->bindParam(':DATE',$row['date']);
						$stmts->execute();
		
						// Compute Remaining Resources for Date (equals)
						foreach($stmts as $kkey => $rrow){
								$counted=$rrow['counted'];
						}				
						$size=$row['size']; 
						$remaining=$size-$counted;
		
						$output.="    resourceID='".presenthtml($row['resourceID'])."'\n";
						$output.="    name='".presenthtml($row['name'])."'\n";
						$output.="    location='".presenthtml($row['location'])."'\n";
						$output.="    company='".presenthtml($row['company'])."'\n";
						$output.="    size='".$row['size']."'\n";
						$output.="    cost='".$row['cost']."'\n";
						$output.="    category='".$row['category']."'\n";
						$output.="    date='".$row['date']."'\n";
						$output.="    dateto='".$row['dateto']."'\n";
            $output.="    auxdata='".$row['auxdata']."'\n";
            $output.="    bookingcount='".$counted."'\n";
            $output.="    remaining='".$remaining."'\n";
		
						$output.=" />\n";
				}				
				$output.="</avail>\n";
			
		} catch (PDOException $e) {
				err("Error!: ".$e->getMessage()."<br/>");
				die();
		}
						
	  header ("Content-Type:text/xml; charset=utf-8");  
		echo $output;
?>
