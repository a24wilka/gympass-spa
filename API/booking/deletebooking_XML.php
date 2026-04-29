<?PHP
			include 'dbconnect.php';

			if(empty($_POST)){
					$_POST=json_decode(file_get_contents('php://input', false),true);
			}

			// Get and escape the variables from post
			$resource=getpostAJAX("resourceID");
			$date=getpostAJAX("date");
			$user=getpostAJAX("customerID");

			if ($resource=="UNK" || $date=="UNK" || $user=="UNK" ) err("Missing Form Data: (resourceidID/userID/date)");

			try{
				 $querystring="DELETE FROM booking WHERE customerID=:CUSTID and date=:DDATE and resourceID=:RESOURCEID";
		     $stmt = $pdo->prepare($querystring);
		     $stmt->bindParam(':CUSTID',$user );
		     $stmt->bindParam(':DDATE', $date);
		     $stmt->bindParam(':RESOURCEID', $resource);
				 $stmt->execute();

				header ("Content-Type:text/xml; charset=utf-8");  
				echo '<deleted status="OK"/>';
			} catch (PDOException $e) {
			    err("Error!: ".$e->getMessage()."<br/>");
			    die();
			}
?>