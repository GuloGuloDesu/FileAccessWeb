<?php
	#Include Header information, this includes layout, and DB conections
	include 'Includes/Header.php';
	
	#Clean UserID for SQL and HTML
	$CLNstrUserID = funcHTMLSQL($_POST["UserID"]);
	
	#Verify IP Address (Array returns error code, error message, value
	$arrIPAddress = funcIPValidate($_SERVER["REMOTE_ADDR"]);
	if($arrIPAddress[0] == 0) {
		#Assign verified IP Address
		$CLNstrIPAddress = $arrIPAddress[2];
		
		#Clear Variables
		unset($arrIPAddress);
	}
	elseif($arrIPAddress[0] == 1) {
		#Print Error message
		print $arrIPAddress[1];
		
		#Clear variables and exit
		unset($arrIPAddress);
		unset($CLNstrUserID);
		exit;
	}
	
	#Connect to the SQL Database
	$objDBConRead = funcDBRead();
	
	#Query to pull Logon Attempts, User Type, Password, and Salt
	$strQueryUserPass = "SELECT
          (SELECT
            COUNT(tblLogonLog.FailedLogon)
          FROM tblLogonLog
          WHERE
            tblLogonLog.UserID = '" . $CLNstrUserID . "'
          AND
            tblLogonLog.TimeAttempt > DATE_ADD(
              CURRENT_TIMESTAMP, INTERVAL -15 MINUTE))
          AS FailedLogonCount
          , tblUsers.Password
          , tblUsers.Salt
          , tblUsers.UserType
          , tblUsers.UserExpires
        FROM tblUsers
        WHERE
          tblUsers.UserID = '" . $CLNstrUserID . "'
        AND
          tblUsers.UserExpires >= CURDATE()";
								
	#Execute the Query
	$objQueryUserPass = mysql_query($strQueryUserPass, $objDBConRead);
	
	#Loop through Query Results
	while($lpQueryUserPass = mysql_fetch_object($objQueryUserPass)) {
		$strPassword = $lpQueryUserPass->Password;
		$intFailedLogon = $lpQueryUserPass->FailedLogonCount;
		$strSalt = $lpQueryUserPass->Salt;
		$intUserType = $lpQueryUserPass->UserType;
		$dteUserExpires = $lpQueryUserPass->UserExpires;
		$dteUserExpires = $lpQueryUserPass->UserExpires;
	}
	
	#Clear Variables
	unset($strQueryUserPass);
	unset($objQueryUserPass);
	unset($lpQueryUserPass);
	
	#Close SQL Connection
	mysql_close($objDBConRead);
	
	#Connect to the SQL Database
	$objDBConWrite = funcDBWrite();
	
	#Verify that the SQL Query returned results
	if(isset($intFailedLogon)) {
		#Verify that there have been less than 3 login attempts
		if($intFailedLogon < 3) {
			#If the passwords match then update the database with a successful login,  
			#set the session variables, and then send the user to the correct page
			#based on their UserType
			if($strPassword == funcPasswordVerify($_POST["Password"], $strSalt)) {
				$strQueryInsertLog = "INSERT
										  INTO tblLogonLog (
											  UserID
											, TimeAttempt
											, IPAddress
											, DateSTamp
										  )
										  VALUES (
											  '" . $CLNstrUserID . "'
											, CURRENT_TIMESTAMP
											, ''" . $CLNstrIPAddress . "'
											, CURDATE()
										  )";
										  
				#Insert into the DB
				mysql_query($strQueryInsertLog, $objDBConWrite);
				
				#Set session variables
				$_SESSION["UserID"] = $CLNstrUserID;
				$_SESSION["UserType"] = $intUserType;
				$_SESSION["UserExpires"] = $dteUserExpires;
				
				#Clear variables
				unset($CLNstrUserID);
				unset($CLNstrIPAddress);
				unset($dteUserExpires);
				unset($strPassword);
				unset($intFailedLogon);
				unset($strSalt);
				unset($intUserType);
				unset($strQueryInsertLog);
		
				#Close SQL Connection
				mysql_close($objDBConWrite);
				
				if($_SESSION["UserType"] == 1 OR $_SESSION["UserType"] == 2) {
					header("Location:/Tags.php");
				}
				elseif($_SESSION["UserType"] == 0) {
					header("Location:/Tags.php");
				}
				elseif($_SESSION["UserType"] == 3) {
					header("Location:/Admin.php");
				}
				print "You have successfully logged on! <br>" . 
				  "Now transfering you to your homepage.";
			}
			#If username and password do not match
			#Insert failed logon attempt into DB and warn the user
			else {
				$strQueryFail = "INSERT
										  INTO tblLogonLog (
											  UserID
											, FailedLogon
											, TimeAttempt
											, IPAddress
											, DateSTamp
										  )
										  VALUES (
											  '" . $CLNstrUserID . "'
											, 1
											, CURRENT_TIMESTAMP
											, '" . $CLNstrIPAddress . "'
											, CURDATE()
										  )";
										  
				#Insert into the DB
				mysql_query($strQueryFail, $objDBConWrite);
				
				print "Invalid Username or Password<br>" . 
				  "This was Attempt " . $intFailedLogon + 1 . " of 3<br>" . 
				  "After 3 failed attempts your account will be locked out"  . 
				  " for 15 minutes" . 
				  "Please go <a href='Logon.php'>Back</a> and try again";
				
				#Clear variables
				unset($CLNstrUserID);
				unset($CLNstrIPAddress);
				unset($strPassword);
				unset($intFailedLogon);
				unset($strSalt);
				unset($intUserType);
				unset($strQueryFail);
		
				#Close SQL Connection
				mysql_close($objDBConWrite);
			}
		}
		#Continue to update failed log in attempts
		else {
			$strQueryFailLogin = "INSERT
									  INTO tblLogonLog (
										  UserID
										, FailedLogon
										, TimeAttempt
										, IPAddress
										, DateSTamp
									  )
									  VALUES (
										  '" . $CLNstrUserID . "'
										, 1
										, CURRENT_TIMESTAMP
										, ''" . $CLNstrIPAddress . "'
										, CURDATE()
									  )";
									  
			#Insert into the DB
			mysql_query($strQueryFailLogin, $objDBConWrite);
			
			print "You have had more than 3 failed logon attemps in the last" . 
			  " 15 minutes<br>" . 
			  "Please try again in 15  minutes";
			
			#Clear variables
			unset($CLNstrUserID);
			unset($CLNstrIPAddress);
			unset($strPassword);
			unset($intFailedLogon);
			unset($strSalt);
			unset($intUserType);
			unset($strQueryFailLogin);
	
			#Close SQL Connection
			mysql_close($objDBConWrite);
		}
	}
	else {
		$strQueryFailUser = "INSERT
										  INTO tblLogonLog (
											  UserID
											, FailedLogon
											, TimeAttempt
											, IPAddress
											, DateSTamp
										  )
										  VALUES (
											  '" . $CLNstrUserID . "'
											, 1
											, CURRENT_TIMESTAMP
											, '" . $CLNstrIPAddress . "'
											, CURDATE()
										  )";
										  
		#Insert into the DB
		mysql_query($strQueryFailUser, $objDBConWrite);
		
		print "Invalid Username or Password<br>" . 
		  "Please go <a href='Logon.php'>Back</a> and try again";
		
		#Clear variables
		unset($CLNstrUserID);
		unset($CLNstrIPAddress);
		unset($strQueryFailUser);

		#Close SQL Connection
		mysql_close($objDBConWrite);
	}
?>
	</body>
</html>
