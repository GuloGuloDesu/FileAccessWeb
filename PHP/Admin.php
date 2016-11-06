<?php
	#Include Header information, this includes layout, and DB conections
	include 'Includes/Header.php';
	
	#Create User Group and UserID arrays
	$arrUserGroup = array();
	$arrUserID = array();
	
	#Connect to the SQL Database
	$objDBConRead = funcDBRead();

	#Query to pull a unique list of groups
	$strQueryGroup = "SELECT
								DISTINCT(tblUserGroup.UserGroup)
							FROM tblUserGroup";
	
	#Execute the Query
	$objQueryGroup = mysqli_query($strQueryGroup, $objDBConRead);
	
	#Loop through Query Results
	while($lpQueryGroup = mysqli_fetch_object($objQueryGroup)) {
		$arrUserGroup[] = $lpQueryGroup->UserGroup;
	}
	
	#Clear variables
	unset($strQueryGroup);
	unset($objQueryGroup);
	
	#Query to pull a unique list of users
	$strQueryUser = "SELECT
								DISTINCT(tblUsers.UserID)
							FROM tblUsers
							WHERE
								tblUsers.UserType IN (1,3)";
	
	#Execute the Query
	$objQueryUser = mysqli_query($strQueryUser, $objDBConRead);
	
	#Loop through Query Results
	while($lpQueryUser = mysqli_fetch_object($objQueryUser)) {
		$arrUserID[] = $lpQueryUser->UserID;
	}
	
	#Clear variables
	unset($strQueryUser);
	unset($objQueryUser);
	
	#Close SQL Connection
	mysqli_close($objDBConRead);
?>
		<div id='BlueBoxForm'>
			<h2>Create User</h2>
			<form id='CreateUser' name='CreateUser' action='AdminSubmit.php' method='post'>
				<fieldset>
					<p>
						<label for='FirstName'>
							First Name:
						</label>
						<br>
						<input id='FirstName' type='text' name='FirstName' class='text' placeholder='First Name' required>
					</p>
					<p>
						<label for='LastName'>
							Last Name:
						</label>
						<br>
						<input id='LastName' type='text' name='LastName' class='text' placeholder='Last Name' required>
					</p>
					<p>
						<label for='UserID'>
							User Name:
						</label>
						<br>
						<input id='UserID' type='text' name='UserID' class='text' placeholder='User Name' required>
					</p>
					<p>
						<label for='Password'>
							Password:
						</label>
						<br>
						<input id='Password' type='password' name='Password' class='text' placeholder='Password' required>
					</p>
					<p>
						<label for='VerifyPassword'>
							Verify Password:
						</label>
						<br>
						<input id='VerifyPassword' type='password' name='VerifyPassword' class='text' placeholder='Verify Password' required>
					</p>
					<p>
						<label for='EMailAddress'>
							E-mail Address:
						</label>
						<br>
						<input id='EMailAddress' type='text' name='EMailAddress' class='text' placeholder='JoeBob@BillyBob.com' required>
					</p>
					<p>
						<label for='UserGroup'>
							User Group:
						</label>
						<br>
						<select id='UserGroup' name='UserGroup'>
							<?php
								#Loop through all of the User Groups
								foreach($arrUserGroup as $lpUserGroup) {
									print "<option>" . $lpUserGroup . "</option>";
								}
								#Clear variables
								unset($lpUserGroup);
							?>
						</select>
					</p>
					<p>
						<label for='UserExpires'>
							User Expiration Date:
						</label>
						<br>
						Year
						<select id='UserExpiresYear' name='UserExpiresYear'>
							<?php
								#Loop through 50 years
								for ($intYear = date("Y"); $intYear <= (date("Y") + 50); $intYear++) {
									print "<option>" . $intYear . "</option>";
								}
								#Clear variables
								unset($intYear);
							?>
						</select>
						Month
						<select id='UserExpiresMonth' name='UserExpiresMonth'>
							<?php
								#Loop through 12 months
								for ($intMonth = 1; $intMonth <= 12; $intMonth++) {
									print "<option>" . $intMonth . "</option>";
								}
								#Clear variables
								unset($intMonth);
							?>
						</select>
						Day
						<select id='UserExpiresDay' name='UserExpiresDay'>
							<?php
								#Loop through 31 days
								for ($intDay = 1; $intDay <= 31; $intDay++) {
									print "<option>" . $intDay . "</option>";
								}
								#Clear variables
								unset($intDay);
							?>
						</select>
					</p>
					<p>
						<input id='Button1' type='submit' value='Create User'>
					</p>
				</fieldset>
			</form>
		</div>
		<br>
		<br>
		<div id='BlueBoxForm'>
			<h2>Create Group</h2>
			<form id='CreateGroup' name='CreateGroup' action='AdminSubmit.php' method='post'>
				<fieldset>
					<p>
						<label for='UserGroup'>
							User Group:
						</label>
						<br>
						<input id='UserGroup' type='text' name='UserGroup' class='text' placeholder='User Group Name' required>
					</p>
					<p>
						<label for='VerifyUserGroup'>
							Verify User Group:
						</label>
						<br>
						<input id='VerifyUserGroup' type='text' name='VerifyUserGroup' class='text' placeholder='Verify User Group' required>
					</p>
					<p>
						<input id='Button1' type='submit' value='Create Group'>
					</p>
				</fieldset>
			</form>
		</div>
		<br>
		<br>
		<div id='BlueBoxForm'>
			<h2>Change Password and or User Group</h2>
			<form id='ChangeUser' name='ChangeUser' action='AdminSubmit.php' method='post'>
				<fieldset>
					<p>
						<label for='UserID'>
							UserID:
						</label>
						<br>
						<select id='UserID' name='UserID'>
							<?php
								#Loop through all of the UserIDs
								foreach($arrUserID as $lpUserID) {
									print "<option>" . $lpUserID . "</option>";
								}
								#Clear variables
								unset($lpUserID);
								unset($arrUserID);
							?>
						</select>
					</p>
					<p>
						<label for='Password'>
							Password:
						</label>
						<br>
						<input id='Password' type='password' name='Password' class='text' placeholder='Password'>
					</p>
					<p>
						<label for='VerifyPassword'>
							Verify Password:
						</label>
						<br>
						<input id='VerifyPassword' type='password' name='VerifyPassword' class='text' placeholder='Verify Password'>
					</p>
					<p>
						<label for='UserGroup'>
							User Group:
						</label>
						<br>
						<select id='UserGroup' name='UserGroup'>
							<option selected>
								NoChange
							</option>
							<?php
								#Loop through all of the Groups
								foreach($arrUserGroup as $lpUserGroup) {
									print "<option>" . $lpUserGroup . "</option>";
								}
								#Clear variables
								unset($lpUserGroup);
								unset($arrUserGroup);
							?>
						</select>
					</p>
					<p>
						<input id='Button1' type='submit' value='Change User'>
					</p>
				</fieldset>
			</form>
		</div>
	</body>
</html>

