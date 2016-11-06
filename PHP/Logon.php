<?php
	#Include Header information, this includes layout, and DB conections
	include "Includes/Header.php";
?>
		<div id='LogonBoxForm'>
			<h2>File Access Logon</h2>
			<form id='Logon' name='Logon' action='Login.php' method='post'>
				<fieldset>
					<p>
						<label for='UserID'>
							User Name
						</label>
						<input id='UserID' type='text' name='UserID' class='text' placeholder='User Name' required>
					</p>
					<p>
						<label for='Password'>
							Password
						</label>
						<input id='Password' type='password' name='Password' class='text' placeholder='Password' required>
					</p>
					<p>
						<input id='Button1' type='submit' value='Logon'>
					</p>
				</fieldset>
			</form>
		</div>
	</body>
</html>
