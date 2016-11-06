<?php
	#Include Header information, this includes layout, and DB conections
	include 'Includes/Header.php';
	
	#Create Group and UserID arrays
	$arrUserGroup = array();
	$arrUserID = array();

	#Clean all the variables for SQL and HTML
	if(isset($_POST["FirstName"])) {
		$CLNstrFirstName = funcHTMLSQL($_POST["FirstName"]);
	}
	if(isset($_POST["LastName"])) {
		$CLNstrLastName = funcHTMLSQL($_POST["LastName"]);
	}
	if(isset($_POST["UserID"])) {
		$CLNstrUserID = funcHTMLSQL($_POST["UserID"]);
	}
	if(isset($_POST["EMailAddress"])) {
		$CLNstrEMailAddress = funcHTMLSQL($_POST["EMailAddress"]);
		#Verify EMail Address (Array returns error code, error message, value
		$arrEMailAddress = funcEMailValidate($CLNstrEMailAddress);
		if($arrEMailAddress[0] == 0) {
			#Assign verified EMail Address
			$CLNstrEMailAddress = $arrEMailAddress[2];
			
			#Clear Variables
			unset($arrEMailAddress);
		}
		elseif($arrEMailAddress[0] == 1) {
			#Print Error message
			print $arrEMailAddress[1];
			
			#Clear variables and exit
			unset($arrEMailAddress);
			exit;
		}
	}
	if(isset($_POST["UserExpiresYear"])) {
		#Verify Year Integer (Array returns error code, error message, value
		$arrYear = funcIntValidate($_POST["UserExpiresYear"]);
		if($arrYear[0] == 0) {
			#Assign verified Year Integer
			$CLNintUserExpiresYear = $arrYear[2];
			
			#Clear Variables
			unset($arrYear);
		}
		elseif($arrYear[0] == 1) {
			#Print Error message
			print $arrYear[1];
			
			#Clear variables and exit
			unset($arrYear);
			exit;
		}
		if(!($CLNintUserExpiresYear >= date("Y") && 
		  $CLNintUserExpiresYear <= (date("Y") + 50))) {
			#Clear Variables
			unset($arrUserGroup);
			unset($arrUserID);
			unset($CLNintUserExpiresYear);
			exit("Only years between now and 50 years from now can be " . 
			  "submitted");
		}
	}
	if(isset($_POST["UserExpiresMonth"])) {
		#Verify Month Integer (Array returns error code, error message, value
		$arrMonth = funcIntValidate($_POST["UserExpiresMonth"]);
		if($arrMonth[0] == 0) {
			#Assign verified Month Integer
			$CLNintUserExpiresMonth = $arrMonth[2];
			
			#Clear Variables
			unset($arrMonth);
		}
		elseif($arrMonth[0] == 1) {
			#Print Error message
			print $arrMonth[1];
			
			#Clear variables and exit
			unset($arrMonth);
			exit;
		}
		if(!($CLNintUserExpiresMonth >= 1 && 
		  $CLNintUserExpiresMonth <= 12)) {
			#Clear Variables
			unset($CLNintUserExpiresMonth);
			unset($arrUserGroup);
			unset($arrUserID);
			exit("Only months between 1 and 12 can be submitted");
		}
	}
	if(isset($_POST["UserExpiresDay"])) {
		#Verify Day Integer (Array returns error code, error message, value
		$arrDay = funcIntValidate($_POST["UserExpiresDay"]);
		if($arrDay[0] == 0) {
			#Assign verified Day Integer
			$CLNintUserExpiresDay = $arrDay[2];
			
			#Clear Variables
			unset($arrDay);
		}
		elseif($arrDay[0] == 1) {
			#Print Error message
			print $arrDay[1];
			
			#Clear variables and exit
			unset($arrDay);
			exit;
		}
		if(!($CLNintUserExpiresDay >= 1 && 
		  $CLNintUserExpiresDay <= 31)) {
			#Clear Variables
			unset($CLNintUserExpiresDay);
			unset($arrUserGroup);
			unset($arrUserID);
			exit("Only days between 1 and 31 can be submitted");
		}
	}
	if(isset($_POST["UserGroup"])) {
		$CLNstrUserGroup = funcHTMLSQL($_POST["UserGroup"]);
	}
	if(isset($_POST["VerifyUserGroup"])) {
		$CLNstrVerifyUserGroup = funcHTMLSQL($_POST["VerifyUserGroup"]);
	}
	if(isset($_POST["UserID"])) {
		$CLNstrUserID = funcHTMLSQL($_POST["UserID"]);
	}
	
	#Connect to the SQL Database
	$objDBConRead = funcDBRead();
	
	#Query to pull a unique list of groups
	$strQueryGroup = "SELECT
          DISTINCT(tblUserGroup.UserGroup)
        FROM tblUserGroup";
	
	#Execute the Query
	$objSQLQueryGroup = mysqli_query($strQueryGroup, $objDBConRead);
	
	#Loop through Query Results
	while($lpSQLQueryGroup = mysqli_fetch_object($objSQLQueryGroup)) {
		$arrUserGroup[] = $lpSQLQueryGroup->UserGroup;
	}

	#Clear variables
	unset($strQueryGroup);
	unset($objSQLQueryGroup);
	
	#Query to pull a unique list of users
	$strQueryUserID = "SELECT
          DISTINCT(tblUsers.UserID)
        FROM tblUsers
        WHERE
          tblUsers.UserType IN (1,3)";
	
	#Execute the Query
	$objSQLQueryUserID = mysqli_query($strQueryUserID, $objDBConRead);
	
	#Loop through Query Results
	while($lpSQLQueryUserID = mysqli_fetch_object($objSQLQueryUserID)) {
		$arrUserID[] = $lpSQLQueryUserID->UserID;
	}
	
	#Clear variables
	unset($lpSQLQueryUserID);
	unset($objSQLQueryUserID);
	
	#Close SQL Connection
	mysqli_close($objDBConRead);
	
	#Check to see if this is a New User Submission
	if(isset($CLNstrFirstName) && isset($CLNstrUserID)) {
		#Verify that the UserID does not already exist
		if(!in_array($CLNstrUserID, $arrUserID)) {
			#Verify that the password match
			if($_POST["Password"] == $_POST["VerifyPassword"]) {
				#Generate Password and Salt
				$arrPassSalt = funcPasswordSalt($_POST["Password"]);
				
				#Connect to the SQL Database
				$objDBConAdmin = funcDBAdmin();
				
				#Query to insert the new user into the users table
				$strUserInsert = "INSERT
                    INTO tblUsers (
                        FirstName
                      , LastName
                      , UserID
                      , Password
                      , Salt
                      , UserType
                      , EMailAddress
                      , UserExpires
                      , DateStamp
                    )
                    VALUES (
                        '" . $CLNstrFirstName . "'
                      , '" . $CLNstrLastName . "'
                      , '" . $CLNstrUserID . "'
                      , '" . $arrPassSalt[0] . "'
                      , '" . $arrPassSalt[1] . "'
                      , 1
                      , '" . $CLNstrEMailAddress . "'
                      , '" . $CLNintUserExpiresYear . "-" . 
                        $CLNintUserExpiresMonth . "-" . 
                        $CLNintUserExpiresDay . "'
                      , CURDATE()
                    )";
														  
				#Insert into the DB
				mysqli_query($strUserInsert, $objDBConAdmin);
				
				#Query to insert the new user into the Group table
				$strGroupInsert = "INSERT 
                    INTO tblUserGroup (
                        UserID
                      , UserGroup
                      , DateStamp
                    )
                    VALUES (
                        '" . $CLNstrUserID . "'
                      , '" . $CLNstrUserGroup . "'
                      , CURDATE()
                    )";
												  
				#Insert into the DB
				mysqli_query($strGroupInsert, $objDBConAdmin);
				
				print "Your new user has been successfully created<br>";
				
				#Clear variables
				unset($CLNstrEMailAddress);
				unset($CLNstrUserGroup);
				unset($CLNstrFirstName);
				unset($CLNstrLastName);
				unset($CLNstrUserID);
				unset($CLNintUserExpiresYear);
				unset($CLNintUserExpiresMonth);
				unset($CLNintUserExpiresDay);
				unset($arrUserID);
				unset($arrUserGroup);
				unset($arrPassSalt);
				unset($strGroupInsert);
				unset($strUserInsert);
				
				#Close SQL Connection
				mysqli_close($objDBConAdmin);
			}
			else {
				print "Your passwords do not match<br>";
				print "Please go back and try again<br>";
				
				#Clear variables
				unset($CLNstrEMailAddress);
				unset($CLNstrUserGroup);
				unset($CLNstrFirstName);
				unset($CLNstrLastName);
				unset($CLNstrUserID);
				unset($CLNintUserExpiresYear);
				unset($CLNintUserExpiresMonth);
				unset($CLNintUserExpiresDay);
				unset($arrUserID);
				unset($arrUserGroup);
			}
		}
		else {
			print "Your UserID already exists<br>";
			print "Please go back and try again<br>";
			
			#Clear variables
			unset($CLNstrEMailAddress);
			unset($CLNstrUserGroup);
			unset($CLNstrFirstName);
			unset($CLNstrLastName);
			unset($CLNstrUserID);
			unset($CLNintUserExpiresYear);
			unset($CLNintUserExpiresMonth);
			unset($CLNintUserExpiresDay);
			unset($arrUserID);
			unset($arrUserGroup);
		}
	}
	#Check to see if this is a new group submission
	if(isset($CLNstrUserGroup) && isset($CLNstrVerifyUserGroup)) {
		#Verify that the Group does not already exist
		if(!in_array($CLNstrUserGroup, $arrUserGroup)) {
			#Check to see that the Group Names match
			if($CLNstrUserGroup == $CLNstrVerifyUserGroup) {
				#Connect to the SQL Database
				$objDBConAdmin = funcDBAdmin();
				
				#Query to inster a new group
				$strNewGroupInsert = "INSERT
                    INTO tblUserGroup (
                        UserGroup
                      , DateStamp
                    )
                    VALUES (
                        '" . $CLNstrUserGroup . "'
                      , CURDATE()
                    )";
														  
				#Insert into the DB
				mysqli_query($strNewGroupInsert, $objDBConAdmin);
				
				print "Your new group has been successfullly created<br>";
				
				#Clear variables
				unset($CLNstrUserGroup);
				unset($CLNstrVerifyUserGroup);
				unset($arrUserID);
				unset($arrUserGroup);
				unset($strNewGroupInsert);
				
				#Close SQL Connection
				mysqli_close($objDBConAdmin);
			}
			else {
				print "Your Group Names do not match<br>";
				print "Please go back and try again<br>";
				
				#Clear variables
				unset($CLNstrUserGroup);
				unset($CLNstrVerifyUserGroup);
				unset($arrUserID);
				unset($arrUserGroup);
			}
		}
		else {
			print "Your Group already exists<br>";
			print "Please go back and try again<br>";
			
			#Clear variables
			unset($CLNstrUserGroup);
			unset($CLNstrVerifyUserGroup);
			unset($arrUserID);
			unset($arrUserGroup);
		}
	}
	#Check to see if it is a password change or a group change
	if(isset($CLNstrUserID)) {
		#Verify that the UserID already exists in the DB
		if(in_array($CLNstrUserID, $arrUserID)) {
			#Check to see if there is a password reset
			if(strlen($_POST["Password"]) > 1) {
				#Verify that the passwords match
				if($_POST["Password"] == $_POST["VerifyPassword"]) {
					#Generate Password and Salt
					$arrPassSalt = funcPasswordSalt($_POST["Password"]);
					
					#Connect to the SQL Database
					$objDBConAdmin = funcDBAdmin();
					
					#Query to change the password
					$strPasswordUpdate = "UPDATE
                            tblUsers
                        SET 
                            Password = '" . $arrPassSalt[0] . "'
                          , Salt = '" . $arrPassSalt[1] . "'
                          , DateStamp = CURDATE()
                        WHERE
                          UserID = '" . $CLNstrUserID . "'";
					
					#Insert into the DB
					mysqli_query($strPasswordUpdate, $objDBConAdmin);
					
					print "Your password has been successfully updated<br>";
					
					#Clear variables
					unset($arrPassSalt);
					unset($arrUserID);
					unset($strPasswordUpdate);
					
					#Close SQL Connection
					mysqli_close($objDBConAdmin);
				}
				else {
					print "Your passwords do not match<br>";
					print "Please go back and try again<br>";
				
					#Clear variables
					unset($arrUserID);
				}
			}
			#Check to see if a Group Changes has been submitted
			if($CLNstrUserGroup != "NoChange") {
				#Verify that the Group already exists
				if(in_array($CLNstrUserGroup, $arrUserGroup)) {
					#Connect to the SQL Database
					$objDBConAdmin = funcDBAdmin();
					
					#Query to change the group
					$strGroupUpdate = "UPDATE 
                            tblUserGroup
                        SET
                            UserGroup = '" . $CLNstrUserGroup . "'
                          , DateStamp = CURDATE()
                        WHERE
                            UserID = '" . $CLNstrUserID . "'";
													
					#Insert into the DB
					mysqli_query($strGroupUpdate, $objDBConAdmin);
					
					print "Your Group has successfully been updated<br>";
					
					#Clear variables
					unset($arrUserID);
					unset($arrUserGroup);
					unset($strGroupUpdate);
					
					#Close SQL Connection
					mysqli_close($objDBConAdmin);
				}
			}
			#If the form is submitted blank then print no info
			if(strlen($_POST["Password"]) < 2 && 
			  $CLNstrUserGroup == "NoChange") {
				print "No password or group change submitted<br>";
				print "Please go back and try again";
			}
		}
		else {
			print "Your UserID does not exist<br>";
			print "Please go back and try again or create this UserID<br>";
		}
	}
?>
	</body>
</html>
