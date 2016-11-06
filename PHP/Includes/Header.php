<?php
	error_reporting(E_ALL);

#Disable the website from being directly accessed
	#Create an array of the Website location
	$arrSelfParts = explode('/', htmlentities($_SERVER['PHP_SELF']));
	
	#Create an array of the webpage OS file location
	$arrFileParts = explode('/',  __FILE__);
	
	#Compare the last variable of the arrays to see if they match
	if(end($arrSelfParts) == end($arrFileParts)) {
		echo "You do not have access to this web page";
		
		#Clear variables
		unset($arrSelfParts);
		exit;
	}
	
	#DBCon.php for DB Connections and PHPFun for PHP Functions
	require "DBCon.php";
	require "PHPFunc.php";
	
	#Define Constants
	$dteDateTime = date("Y-m-d H:i:s");
	$strWelcome = "";
	$strStatusLink = "";
	$strRedirect = "";
	$strReportLink = "";
	
#Set Session Data
	#Define the Session User Agent Pepper
	define("DoNotHackMySessions", "DoNotHackMySessions");
	
	#Start the Session
	session_start();
	
	#Check to see if the Session already exists
	#If it does create a new ID and set the Session
	if(!isset($_SESSION["Access"])) {
		session_regenerate_id();
		$_SESSION["Access"] = TRUE;
	}
	
	#Check if the Browser Agent has been set, 
	#if not then set to the User Agent with a Pepper
	if(!isset($_SESSION['User_Browser_Agent_OS'])) {
		$_SESSION['User_Browser_Agent_OS'] = md5($_SERVER['HTTP_USER_AGENT'] 
		  . DoNotHackMySessions);
	}
	#Check that the Browser Agent matches the User Agent, 
	#if not then a Session hijacking may be taking place
	else {
		if($_SESSION["User_Browser_Agent_OS"] != 
		  md5($_SERVER["HTTP_USER_AGENT"] . DoNotHackMySessions)) {
			#Clear Variables
			unset($dteDateTime);
			unset($strWelcome);
			unset($strRedirect);
			unset($strReportLink);
			
			#Kill the session
			funcSessionKill();
			exit("Session Hijacking may be in progress");
		}
	}
	#Check the time of the session 
	#and close if the session is older than 30 mins
	if(isset($_SESSION["LastActivity"]) && 
	  (time() - $_SESSION["LastActivity"] > 1800)) {
		#Unset all $_SESSION variables
		session_unset();
		#Destroy the Session
		session_destroy();
		#Expire the Cookie
		setcookie(Session_name(), "", time() -3600);
	}
	else {
		$_SESSION["LastActivity"] = time();
	}
	
	#Check to see if the user has already logged on, 
	#and if not and they are not at the logon page,
	#send them to the logon page
#	if(!isset($_SESSION["UserID"]) && strtolower(htmlentities(
#	  $_SERVER["PHP_SELF"])) != "/logon.php") {
		#Check to see if logon POST variables exist
#		if(!isset($_POST["UserID"]) && !isset($_POST["Password"])
#		  && $_SERVER["PHP_SELF"] != "/login.php") {
#			#Clear Variables
#			unset($dteDateTime);
#			unset($strWelcome);
#			unset($strRedirect);
#			unset($strReportLink);
#			header("Location:/Logon.php");
#			exit;
#		}
#	}
	
	#Check to see if the user has already logged on, and if so, 
	#log them off if they go to the logon page
	if(isset($_SESSION["UserID"]) and strtolower(htmlentities(
	  $_SERVER["PHP_SELF"])) == "/logon.php") {
		#Clear Variables
		unset($dteDateTime);
		unset($strWelcome);
		unset($strRedirect);
		unset($strReportLink);
		header("Location:/Logoff.php");
		exit;
	  }
	
#Set Headers for each of the pages
	#If the UserID is set and they are not on the Logoff Page
	if(isset($_SESSION["UserID"]) && strtolower(htmlentities(
	  $_SERVER["PHP_SELF"])) != "/logoff.php") {
		#Assign Logoff Link
		$strStatusLink = "<a href='/Logoff.php'>Log Off</a>";
		  
		#Check if they are an Upload (1), Download (0), or Anon (2) Admin (3)
		if($_SESSION["UserType"] == 1 OR $_SESSION["UserType"] == 2) {
			#Set the Home Page to the Upload Page
			$strHomePage = "<a href='/Upload.php'>Home</a>";
			$strWelcome = "Hello " . $_SESSION["UserID"] . "<br>" . 
			  "Your user expires on:  "  . $_SESSION["UserExpires"] . "<br>";
			 
		}
		#If the user is part of the Read or Write group
		# assign a homepage of Tags
		if($_SESSION["UserType"] == 0 || $_SESSION["UserType"] == 1) {
			#Assign a homepage of Tags
			$strHomePage = "<a href='/Tags.php'>Home</a>";
			$strWelcome = "Hello " . $_SESSION["UserID"] . "<br>" . 
			  "Your user expires on:  "  . $_SESSION["UserExpires"] . "<br>";
		}
		#If the user is part of the Admin group
		# assign a homepage of Admin
		elseif($_SESSION["UserType"] == 3) {
			#Assign a homepage of Admin
			$strHomePage = "<a href='/Admin.php'>Home</a>";
			$strWelcome = "Hello " . $_SESSION["UserID"] . "<br>" . 
			  "Your user expires on:  "  . $_SESSION["UserExpires"] . "<br>";
		}
	}
	#If the user has not logged on then assign the 
	#logon page as the home page and status link
	else {
		#Assign the ome page and status link to Logon.php
		$strHomePage = "<a href='/Logon.php'>Home</a>";
		$strStatusLink = "<a href='/Logon.php'>Log On</a>";
	}
	
#Determing the current website loading, and build a custom header page
	#Check if the user is on the Logon page
	if(strtolower(htmlentities($_SERVER["PHP_SELF"])) == "/logon.php") {
		#Assign the Page Title
		$strPageTitle = "Logon";
	}
	
	#Check if the user is on the LogIn page
	if(strtolower(htmlentities($_SERVER["PHP_SELF"])) == "/login.php") {
		#Check to see if a UserID and Password were submitted
		if(isset($_POST["UserID"]) && isset($_POST["Password"])) {
			#Assign the Page Title
			$strPageTitle = "Login";
		}
		#Send back to the Logon Page
		else {
			#Clear Variables
			unset($dteDateTime);
			unset($strWelcome);
			unset($strRedirect);
			unset($strHomePage);
			unset($strStatusLink);
			unset($strReportLink);
			header("Location:/Logon.php");
			exit;	
		}
	}
	
	#Check if the user is on the Upload page
	if(strtolower(htmlentities($_SERVER["PHP_SELF"])) == "/upload.php") {
		#Verify the User Type before continueing to the upload page
		if($_SESSION["UserType"] == 1 OR $_SESSION["UserType"] == 2) {
			#Assign the Page Title
			$strPageTitle = "Upload";
		}
		#If user is a download user
		elseif($_SESSION["UserType"] == 0) {
			#Send user to the download page
			#Clear Variables
			unset($dteDateTime);
			unset($strWelcome);
			unset($strRedirect);
			unset($strHomePage);
			unset($strStatusLink);
			unset($strReportLink);
			header("Location:/Download.php");
			exit;
		}
		#Else they have an invalid session type
		else{
			#Send user to the logoff page
			#Clear Variables
			unset($dteDateTime);
			unset($strWelcome);
			unset($strRedirect);
			unset($strHomePage);
			unset($strStatusLink);
			unset($strReportLink);
			header("Location:/Logoff.php");
			exit;
		}
	}
	
	#Check if the user is on the UploadFile page
	if(strtolower(htmlentities($_SERVER["PHP_SELF"])) == "/uploadfile.php") {
		#Verify the User Type before continueing to the upload page
		if($_SESSION["UserType"] == 1 OR $_SESSION["UserType"] == 2) {
			#Assign the Page Title
			$strPageTitle = "Upload File";
		}
		#If user is a download user
		elseif($_SESSION["UserType"] == 0) {
			#Send user to the download page
			#Clear Variables
			unset($dteDateTime);
			unset($strWelcome);
			unset($strRedirect);
			unset($strHomePage);
			unset($strStatusLink);
			unset($strReportLink);
			header("Location:/Download.php");
			exit;
		}
		#Else they have an invalid session type
		else{
			#Send user to the logoff page
			#Clear Variables
			unset($dteDateTime);
			unset($strWelcome);
			unset($strRedirect);
			unset($strHomePage);
			unset($strStatusLink);
			unset($strReportLink);
			header("Location:/Logoff.php");
			exit;
		}
	}
	
	#Check if the user is on the Search page
	if(strtolower(htmlentities($_SERVER["PHP_SELF"])) == "/search.php") {
		#Verify the User Type before continueing to the search page
		if($_SESSION["UserType"] == 0 || $_SESSION["UserType"] == 1) {
			#Assign the Page Title
			$strPageTitle = "Search";
		}
		#If user is a upload user
		elseif($_SESSION["UserType"] == 3) {
			#Send user to the Admin page
			#Clear Variables
			unset($dteDateTime);
			unset($strWelcome);
			unset($strRedirect);
			unset($strHomePage);
			unset($strStatusLink);
			unset($strReportLink);
			header("Location:/Admin.php");
			exit;
		}
		#Else they have an invalid session type
		else{
			#Send user to the logoff page
			#Clear Variables
			unset($dteDateTime);
			unset($strWelcome);
			unset($strRedirect);
			unset($strHomePage);
			unset($strStatusLink);
			unset($strReportLink);
			header("Location:/Logoff.php");
			exit;
		}
	}
	
	#Check if the user is on the DownloadFile page
	if(strtolower(htmlentities($_SERVER["PHP_SELF"])) == "/downloadfile.php") {
		#Verify the User Type before continueing to the download page
		if($_SESSION["UserType"] == 0 OR $_SESSION["UserType"] == 1) {
			#Assign the Page Title
			$strPageTitle = "Download File";
		}
		#Else they have an invalid session type
		elseif($_SESSION["UserType"] == 2 OR $_SESSION["UserType"] == 3){
			#Send user to the logoff page
			#Clear Variables
			unset($dteDateTime);
			unset($strWelcome);
			unset($strRedirect);
			unset($strHomePage);
			unset($strStatusLink);
			unset($strReportLink);
			header("Location:/Logoff.php");
			exit;
		}
	}
	
	#Check if the user is on the LogOff page
	if(strtolower(htmlentities($_SERVER["PHP_SELF"])) == "/logoff.php") {
		#Assign the Page Title
		$strPageTitle = "User Logoff";
	}
	
	#Check if the user is on the Admin page
	if(strtolower(htmlentities($_SERVER["PHP_SELF"])) == "/admin.php") {
		if($_SESSION["UserType"] == 3) {
			#Assign the Page Title
			$strPageTitle = "User Admin";
		}
#		else {
			#Send user to the logoff page
			#Clear Variables
#			unset($dteDateTime);
#			unset($strWelcome);
#			unset($strRedirect);
#			unset($strHomePage);
#			unset($strStatusLink);
#			unset($strReportLink);
#			header("Location:/Logoff.php");
#			exit;
#		}
	}
	
	#Check if the user is on the Admin Submit page
	if(strtolower(htmlentities($_SERVER["PHP_SELF"])) == "/adminsubmit.php") {
		if($_SESSION["UserType"] == 3) {
			#Assign the Page Title
			$strPageTitle = "User Admin Submit";
		}
#		else {
			#Send user to the logoff page
			#Clear Variables
#			unset($dteDateTime);
#			unset($strWelcome);
#			unset($strRedirect);
#			unset($strHomePage);
#			unset($strStatusLink);
#			unset($strReportLink);
#			header("Location:/Logoff.php");
#			exit;
#		}
	}
?>
<!DOCTYPE html>
<html lang='en'>
	<head>
		<meta charset='utf-8'>
		<link rel='stylesheet' type='text/css' href='Main.css'>
		<?php
			echo $strRedirect;
		?>
		<meta http-equiv='expires' content='0'>
		<title>
			<?php
				echo $strPageTitle;
			?>
		</title>
		<script type='text/javascript'>
			function PageReload() {
				window.location.reload()
			}
		</script>
	</head>
	<body>
	<?php
		echo $strHomePage . "<br>";
		echo $strStatusLink . "<br>";
		echo $strReportLink . "<br>";
		echo $strWelcome . "<br>";
		
		#Clear Variables
		unset($strRedirect);
		unset($strPageTitle);
		unset($strHomePage);
		unset($strStatusLink);
		unset($strWelcome);
		unset($strReportLink);
	?>
