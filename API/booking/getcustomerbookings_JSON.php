<?PHP
			include 'dbconnect.php';

			if(empty($_POST)){
				$_POST=json_decode(file_get_contents('php://input', false),true);
			}			
			
			$customerID=getpostAJAX("customerID");
			$type=getpostAJAX("type");

			if($customerID=="UNK"||$type=="UNK"){
					err("Missing Form Data: (customerID ".$customerID." / type ".$type." ".print_r($_POST,true));					
			}

			try{
					$querystring="SELECT resource.type,booking.customerID,booking.resourceID,resource.name,resource.company,resource.location,DATE_FORMAT(booking.date,'%Y-%m-%d %H:%i') as date,DATE_FORMAT(booking.dateto,'%Y-%m-%d %H:%i') as dateto,booking.cost,booking.rebate,booking.position,booking.status,resource.category,resource.size,booking.auxdata FROM customer,booking,resource WHERE resource.ID=booking.resourceID AND booking.customerID=customer.ID AND customer.ID=:CUSTID AND type=:TYPE order by booking.date";
					$stmt = $pdo->prepare($querystring);
					$stmt->bindParam(':CUSTID',$customerID);
					$stmt->bindParam(':TYPE',$type);					
					$stmt->execute();
							
					header ("Content-Type: application/json; charset=utf-8'");  
          $str="[\n";
          $cnt=0;
					foreach($stmt as $key => $row){
              if($cnt>0) $str.=",";
              $cnt++;
              $str.="{";
              $key=$row['customerID'].$row['resourceID'].$row['position'].$row['date'];
              $key=str_replace(" ","",$key);
              $str.='"key":"'.$key.'",';
              $str.='"application":"'.$row['type'].'",';
              $str.='"customerID":"'.$row['customerID'].'",';
              $str.='"resourceID":"'.$row['resourceID'].'",';
              $str.='"name":"'.$row['name'].'",';
              $str.='"company":"'.$row['company'].'",';
              $str.='"location":"'.$row['location'].'",';
              $str.='"date":"'.$row['date'].'",';
              $str.='"dateto":"'.$row['dateto'].'",';
              $str.='"position":"'.$row['position'].'",';
              $str.='"cost":"'.$row['cost'].'",';              
              $str.='"category":"'.$row['category'].'",';  
              $str.='"size":"'.$row['size'].'",'; 
              $str.='"auxdata":"'.$row['auxdata'].'"'; 

              $str.="}\n";
					}
          $str.="]\n";
          echo $str;
	
			} catch (PDOException $e) {
					err("Error!: ".$e->getMessage()."<br/>");
					die();
			}
			
						
?>
