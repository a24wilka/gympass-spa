<?PHP
		include 'dbconnect.php';	

		if(empty($_POST)){
				$_POST=json_decode(file_get_contents('php://input', false),true);
		}

		$ID=getpostAJAX("ID");
		$firstname=getpostAJAX("firstname");
		$lastname=getpostAJAX("lastname");
		$address=getpostAJAX("address");
		$email=getpostAJAX("email");
		$auxdata=getpostAJAX("auxdata");
		
		if ($ID=="UNK" || $firstname=="UNK" || $lastname=="UNK" || $address=="UNK" || $email=="UNK") err("Missing Form Data: (ID/firstname/lastname/address/email)");

		try{
				$querystring="INSERT INTO customer(lastvisit,ID, firstname,lastname,address,email,auxdata) values (NOW(),:ID,:FIRSTNAME,:LASTNAME,:ADDRESS,:EMAIL,:AUXDATA);";

				$stmt = $pdo->prepare($querystring);
				$stmt->bindParam(':ID',$ID );
				$stmt->bindParam(':FIRSTNAME',$firstname );
				$stmt->bindParam(':LASTNAME',$lastname );
				$stmt->bindParam(':ADDRESS',$address );
				$stmt->bindParam(':EMAIL',$email );
				$stmt->bindParam(':AUXDATA',$auxdata );
				$stmt->execute();
				
				// Make random artificial delay 1.5s - 2s
				usleep( rand( 300,5000 ) * 1000 );

				header ("Content-Type:text/xml; charset=utf-8");  
				echo '<created status="OK"/>';

		} catch (PDOException $e) {
				err("Error!: ".$e->getMessage()."<br/>");
				die();
		}

?>