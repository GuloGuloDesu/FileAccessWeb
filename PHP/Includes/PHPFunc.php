<?php
#Disable the website from being directly accessed
	#Create an array of the Website location
	$arrSelfParts = explode('/', htmlentities($_SERVER['PHP_SELF']));
	
	#Create an array of the webpage OS file location
	$arrFileParts = explode('/',  __FILE__);
	
	#Compare the last variable of the arrays to see if they match
	if(end($arrSelfParts) == end($arrFileParts)) {
		print "You do not have access to this web page";
		
		#Clear variables
		unset($arrSelfParts);
		exit;
	}
	
	#Clean variables for HTML and SQL
	function funcHTMLSQL($strVarClean) {
	    $objDBConRead = funcDBRead();
		$strVarClean = mysqli_real_escape_string($objDBConRead, $strVarClean);
		$strVarClean = htmlspecialchars($strVarClean);
	    mysqli_close($objDBConRead);
		#Return the cleaned string
		return $strVarClean;
	}
	
	#Verify that only a number have been submitted
	function funcIntValidate($intInteger) {
		if(!filter_var($intInteger, FILTER_VALIDATE_INT)) {
			#Create an array, (Error Code, Error Message, Value)
			$arrInteger = array(1, "Only numbers are allowed to be submitted" .
			   " to this form field", $intInteger);
		}
		else {
			#Create an array, (Error Code, Error Message, Value)
			$arrInteger = array(0, '', $intInteger);
		}
		#Clear variables
		unset($intInteger);
			
		#Return an array
		return $arrInteger;
	}
	
	#Verify that only an IP Address have been submitted
	function funcIPValidate($strIPAddress) {
		if(!filter_var($strIPAddress, FILTER_VALIDATE_IP)) {
			#Create an array, (Error Code, Error Message, Value)
			$arrIPAddress = array(1, "Your IP Address does not appear to be " .
			   "a valid IP Address", $strIPAddress);
		}
		else {
			#Create an array, (Error Code, Error Message, Value)
			$arrIPAddress = array(0, '', $strIPAddress);
		}
		#Clear variables
		unset($strIPAddress);
		
		#Return an array
		return $arrIPAddress;
	}
	
	#Verify that only an Email Address have been submitted
	function funcEMailValidate($strEMailAddress) {
		if(!filter_var($strEMailAddress, FILTER_VALIDATE_EMAIL)) {
			#Create an array, (Error Code, Error Message, Value)
			$arrEMailAddress = array(1, "Your E-mail Address does not appear" .
			   "  to be a valid E-mail Address", $strEMailAddress);
		}
		else {
			#Create an array, (Error Code, Error Message, Value)
			$arrEMailAddress = array(0, '', $strEMailAddress);
		}
		#Clear variables
		unset($strEMailAddress);
		
		#Return an array
		return $arrEMailAddress;
	}
	
	#Random generator
	function funcRandom($intLen, $intComplexity, $arrHayStack) {
		#Define function constants
		$arrRandChars = array();
		$bolCheck = TRUE;
		$intCheck = 1;
		
		#Check to see if the possible chars need to contain symbols with 
		#letters and numbers
		if($intComplexity == 1) {
			$arrChars = array("a", "b", "c", "d", "e", "f", "g", "h", "i", "j",
			   "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "x",
			    "y", "z", "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K"
			    , "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "X", 
			    "Y", "Z", "1", "2", "3", "4", "5", "6", "7", "8", "9", "0", "!"
			    , "@", "#", "$", "^", "*", "(", ")", "-", "_", "+", "=", "[", 
			    "]", "{", "}", "|", ",", ".", "?");
		}
		#Use simple complexity
		elseif($intComplexity == 0) {
			$arrChars = array("a", "b", "c", "d", "e", "f", "g", "h", "i", "j",
			   "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "x",
			    "y", "z", "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K"
			    , "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "X", 
			    "Y", "Z", "1", "2", "3", "4", "5", "6", "7", "8", "9", "0");
		}
	
		#Loop to verify random generation is unique
		while($bolCheck) {
			#Loop through the random generator the amount of times specified
			while(count($arrRandChars) < $intLen) {
				$arrRandChars[] = $arrChars[mt_rand(0, (count($arrChars) - 1))];
			}
			#Assign completed random to a variable
			$strRandChars = implode("", $arrRandChars);
			
			#Verify that the created variable is not in the hay stack
			if(in_array($strRandChars, $arrHayStack)) {
				$bolCheck = TRUE;
				$intCheck++;
			}
			#Exit the loop once a unique file name has been created
			else {
				$bolCheck = FALSE;
			}
			
			#Protection against an infinite loop after 23 iterations
			if($intCheck > 23) {
				#Clear variables
				unset($bolCheck);
				unset($arrRandChars);
				unset($strRandChars);
				unset($intCheck);
				unset($arrChars);
				exit("General loop proteciton fault, random generation");
			}
		}
		
		#Clear variables
		unset($arrChars);
		unset($bolCheck);
		unset($intCheck);
		unset($arrRandChars);
		unset($intFileCheck);
		
		#Retrun a single string rather than an array
		return $strRandChars;
	}
	
	#Function for salting and hashing passwords
	function funcPasswordSalt($strPassword) {
		#Hash the password to avoid any character incompatibilities
		$strHash = hash("sha256", $strPassword);
		
		#Generate a pseudo random IV size
		$intMCryptSize = mcrypt_get_iv_size(MCRYPT_CAST_256, MCRYPT_MODE_CFB);
		
		#Generate a random salt based on the pseudo random IV size
		#You must remove the + otherwise BCrypt will fail
		$strSalt = str_replace("+", ".", base64_encode(
		  mcrypt_create_iv($intMCryptSize, MCRYPT_DEV_URANDOM)));
		
		#Generate the hashed and salted password
		$strPasswordHash = crypt($strHash, "$2y$13$" . $strSalt);
		
		#Clear variables
		unset($strHash);
		unset($intMCryptSize);
		
		#Verify there were no errors in generating the hash
		if(strlen($strPasswordHash) < 5) {
			exit("Salting failure<br>Please try again.");
		}
		
		return array($strPasswordHash, $strSalt);
	}
	
	#Function to verify a password
	function funcPasswordVerify($strPassword, $strSalt) {
		#Hash the password to avoid any character incompatibilities
		$strHash = hash("sha256", $strPassword);
		
		#Generate the hashed and salted password
		$strPasswordHash = crypt($strHash, "$2y$13$" . $strSalt);
		
		#Verify there were no errors in generating the hash
		if(strlen($strPasswordHash) < 5) {
			exit("Salting failure<br>Please try again.");
		}
		
		#Clear variables
		unset($strHash);
		
		return $strPasswordHash;
	}
	
	#Function to kill sessions
	function funcSessionKill() {
		#Unset all $_SESSION variables
		session_unset();
		#Destroy the session
		session_destroy();
		#Expire the cookie
		setcookie(session_name(), '', time() - 3600);
	}

?>
