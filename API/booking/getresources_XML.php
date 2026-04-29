<?php
			//---------------------------------------------------------------------------------------------------------------
			// Build Search Query!
			//---------------------------------------------------------------------------------------------------------------
			
			include 'dbconnect.php';

			if(empty($_POST)){
					$_POST=json_decode(file_get_contents('php://input', false),true);
			}

			$company="%".getpostAJAX("company")."%";
			$type=getpostAJAX("type");
			$location="%".getpostAJAX("location")."%";
			$name="%".getpostAJAX("name")."%";
			$fulltext=getpostAJAX("fulltext");
			$resID="%".getpostAJAX("resID")."%";
			$category="%".getpostAJAX("category")."%";
			
			if($fulltext!="UNK"){
					$company="%".$fulltext."%";
					$location="%".$fulltext."%";
					$name="%".$fulltext."%";
					$resID="%".$fulltext."%";
			}

			if($type=="UNK"){
					err("Missing Form Data: (type)");					
			}
			
			//---------------------------------------------------------------------------------------------------------------
			// Make Result!
			//---------------------------------------------------------------------------------------------------------------					

			try{
					if(getpostAJAX("category")!="UNK"||getpostAJAX("company")!="UNK"||getpostAJAX("location")!="UNK"||getpostAJAX("fulltext")!="UNK"||getpostAJAX("name")!="UNK"||getpostAJAX("resID")!="UNK"){
							$querystring="SELECT * FROM resource WHERE type=:TYPE AND (category like :CATEGORY or name like :NAME or company like :COMPANY or location like :LOCATION or id like :RESID)";
							$stmt = $pdo->prepare($querystring);
							$stmt->bindParam(':TYPE',$type);
							$stmt->bindParam(':COMPANY',$company);
							$stmt->bindParam(':CATEGORY',$category);							
							$stmt->bindParam(':NAME',$name);
							$stmt->bindParam(':LOCATION',$location);
							$stmt->bindParam(':RESID',$resID);
							$stmt->execute();
					}else{
							$querystring="SELECT * FROM resource WHERE type=:TYPE";
							$stmt = $pdo->prepare($querystring);
							$stmt->bindParam(':TYPE',$type);					
							$stmt->execute();
					}

					header ("Content-Type:text/xml; charset=utf-8");
					echo "<resources>\n";
							foreach($stmt as $key => $row){
							echo "<resource \n";
							echo "    id='".presenthtml($row['ID'])."'\n";
							echo "    name='".presenthtml($row['name'])."'\n";
							echo "    company='".presenthtml($row['company'])."'\n";
							echo "    location='".presenthtml($row['location'])."'\n";
							echo "    size='".$row['size']."'\n";
							echo "    cost='".$row['cost']."'\n";
							echo "    category='".$row['category']."'\n";
							echo "    auxdata='".$row['auxdata']."'\n";
							echo " />\n";
							echo "\n";
					}				
					echo "</resources>";	
				
			} catch (PDOException $e) {
					err("Error!: ".$e->getMessage()."<br/>");
					die();
			}		
?>