<?PHP
			include 'dbconnect.php';

			if(empty($_POST)){
					$_POST=json_decode(file_get_contents('php://input', false),true);
			}

			$customerID=getpostAJAX("customerID");

			if($customerID=="UNK"){
					err("Missing Form Data: (customerID)");					
			}
			
			try{
					$querystring="SELECT * FROM customer WHERE ID=:ID";
					$stmt = $pdo->prepare($querystring);
					$stmt->bindParam(':ID',$customerID);
					$stmt->execute();
							
					$output="<customers>\n";
					foreach($stmt as $key => $row){
							// At the moment do nothing!
							$output.="<customer \n";
							$output.="    id='".presenthtml($row['ID'])."'\n";
							$output.="    firstname='".presenthtml($row['firstname'])."'\n";
							$output.="    lastname='".presenthtml($row['lastname']." ")."'\n";
							$output.="    address='".presenthtml($row['address'])."'\n";
							$output.="    lastvisit='".presenthtml($row['lastvisit'])."'\n";
							$output.="    email='".presenthtml($row['email'])."'\n";
							$output.="    auxdata='".presenthtml($row['auxdata'])."'\n";
							$output.=" />\n";
					}
					$output.="</customers>";

					// Update first so if it crashes we have not printed the data first
					$querystring="UPDATE customer SET lastvisit=now() WHERE ID=:ID";
					$stmt = $pdo->prepare($querystring);
					$stmt->bindParam(':ID',$customerID);
					$stmt->execute();

					header ("Content-Type:text/xml; charset=utf-8");  
					echo $output;								
	
			} catch (PDOException $e) {
					err("Error!: ".$e->getMessage()."<br/>");
					die();
			}
			
			

?>