<?php
require_once "header.php";

if (!empty($_POST['newpass']))
{
	if (!empty($_POST['oldpass']) && password_verify($mysqli->real_escape_string($_POST['oldpass']), $userrow['password']))
	{
		if ($_POST['newpass'] == $_POST['cnewpass'] && !empty($_POST['newpass']))
		{
			$newpass = password_hash($mysqli->real_escape_string($_POST['newpass']), PASSWORD_BCRYPT);
			$mysqli->query("UPDATE Players SET `password` = '$newpass' WHERE `Players`.`username` = '$username' LIMIT 1;");
			echo "Password changed successfully!<br/>";
		} else
			echo "Error changing password: Confirmation does not match new password, or the new password was left blank.<br/>";
	}
	else
		echo "Error changing password: Current password was incorrect.<br/>";
}

if (!empty($_POST['deleteconfirm']))
{
	if (password_verify($mysqli->real_escape_string($_POST['deleteconfirm']), $userrow['password']))
	{
		//clear all record of the player existing
		$mysqli->query("UPDATE `Players` SET `server_player` = '' WHERE `Players`.`server_player` = '$username'");
		$mysqli->query("UPDATE `Players` SET `client_player` = '' WHERE `Players`.`client_player` = '$username'");
		$mysqli->query("UPDATE `Players` SET `aiding` = '' WHERE `Players`.`aiding` = '$username'");
		$mysqli->query("UPDATE `Players` SET `autoassist` = '' WHERE `Players`.`autoassist` = '$username'");
		$mysqli->query("UPDATE `Sessions` SET `exchangeland` = '' WHERE `Sessions`.`exchangeland` = '$username'");
		$mysqli->query("DELETE FROM `Players` WHERE `Players`.`username` = '$username' LIMIT 1;");
		$mysqli->query("DELETE FROM `Echeladders` WHERE `Echeladders`.`username` = '$username' LIMIT 1;");
		$mysqli->query("DELETE FROM `Ability_Patterns` WHERE `Ability_Patterns`.`username` = '$username' LIMIT 1;");
		$mysqli->query("DELETE FROM `Messages` WHERE `Messages`.`username` = '$username' LIMIT 1;");
		echo "Done! The player account $username has been completely removed from the database. Have a nice day!<br/>";
		$_SESSION['username'] = "";
		echo "<script>
$(document).ready(function () {
    window.location = 'index.php';
});
</script>";
		session_destroy();
	}
	else
		echo "Error: Password incorrect. Your account still exists, so... yay? Unless you REALLY wanted your account gone, in which case not yay?<br/>";
}

if (!empty($_POST['newemail']))
{
	if ($_POST['newemail'] == $_POST['cnewemail']) {
		$newemail = $mysqli->real_escape_string($_POST['newemail']);
		$mysqli->query("UPDATE Players SET `email` = '$newemail' WHERE `Players`.`username` = '$username' LIMIT 1;");
		echo "Email address updated successfully!<br/>";
	} else
		echo "Error changing email: Confirmation does not match new email.<br/>";
}

$msgrow = fetchOne("SELECT username, feedbacknotice, newsnotice FROM Messages WHERE username = :username LIMIT 1;", ['username' => $username]);
if (!$msgrow)
	echo "ERROR: Message query didn't go through! Either you don't have a messages row or Blahdev really screwed up somewhere. In either case, please notify a developer immediately.<br/>";

if (empty($userrow['email']))
	$userrow['email'] = "None set yet.";

?>

Player Settings<br/>
You can change various things about your account here.<br/>
<br/>
<form method="post" action="playersettings.php">Change Password:<br/>
Current Password: <input type="password" name="oldpass"><br/>
New Password: <input type="password" name="newpass"><br/>
Confirm New Password: <input type="password" name="cnewpass"><br/>
<input type="submit" value="Change it!"></form><br/>

<form method="post" action="playersettings.php">Update Email:<br/>
Current Email: <?php echo $userrow['email']; ?> <br/>
New Email: <input type="text" name="newemail"><br/>
Confirm New Email: <input type="text" name="cnewemail"><br/>
<input type="submit" value="Update it!"></form>
Note: The Overseer Project uses these emails for the sole purpose of account recovery, should you forget your password. We will never give your email to any third parties, or send you anything without your permission.<br/><br/>

<form method="post" action="playersettings.php">Messaging Settings:<br/>
<input type="checkbox" <?php if ($msgrow['feedbacknotice'] == 1) echo ' selected="selected"'; ?> > Notify me when a flag is set on one of my item submissions<br/>
<input type="checkbox" <?php if ($msgrow['newsnotice'] == 1) echo ' selected="selected"'; ?> > Notify me when there is an item/art update<br/>
<input type="submit" value="Update it!"></form><br/>

Delete your account<br/>
If you plan on abandoning your account, we politely ask that you delete it to conserve space in our database.
Once done, a brand new account can be made with your username.
So, you could potentially use this to start from the beginning without changing names or making a side account.<br/>
Remember: <b>deleting your account is permanent.</b> Only do this if you are ABSOLUTELY SURE you will not be using your account, as it currently is, ever again.<br/>
If you wish to proceed, type your password in the box below for confirmation.<br/>
<form action="playersettings.php" method="post"><input type="password" name="deleteconfirm"><br/><input type="submit" value="Yes, I want my account nuked! Do it already!"></form>

<?php require_once "footer.php"; ?>
