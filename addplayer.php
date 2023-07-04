<?php
require_once("header.php");
/*$con = $mysqli->connect("localhost","theovers_DC","pi31415926535");
if (!$con)
  {
  echo "Connection failed.\n";
  die('Could not connect: ' . $mysqli->error());
  }

$mysqli->select_db("theovers_HS", $con);*/
require_once("includes/SQLconnect.php");

$inactivetime = 1209600; //the amount of time a head admin has to log in before their session is considered "inactive"
//this number amounts to 14 days; if set to 0, the script won't care how long it's been

$playerclash = False;
$sessionlogin = False;
$_POST['username'] = str_replace(">", "", $_POST['username']); //this is why we can't have nice things
$_POST['username'] = str_replace("<", "", $_POST['username']);
$_POST['username'] = str_replace("'", "", $_POST['username']); //kill apostrophes while we're at it
$playerresult = $mysqli->query("SELECT * FROM Players WHERE `Players`.`username` = '" . $_POST['username'] . "'");
if (empty($_POST['randomsession']))
$sessionresult = $mysqli->query("SELECT * FROM Sessions WHERE `Sessions`.`name` = '" . $_POST['session'] . "'");
else {
  $sessionresult = $mysqli->query("SELECT `name` FROM Sessions WHERE `Sessions`.`allowrandoms` = 1"); //look up all sessions accepting randoms
  $allowsessions = 0;
  while ($row = $sessionresult->fetch_array()) {
  	$allowsessions++; //count them
  	$randname[$allowsessions] = $row['name'];	 
  }
  if ($allowsessions == 0) { //if there aren't any
  	$playerclash = True;
  	echo "Player entry failed: No sessions found that are allowing random entries at this time.";
  } else {
  	$pickedsession = rand(1,$allowsessions);
  	$firstattempt = $pickedsession;
  	$currenttime = time();
  	$foundactivesession = false;
  	while ($foundactivesession == false && $playerclash == false) {
  	$_POST['session'] = $randname[$pickedsession]; //do this so that the rest of the page is fooled into thinking this session was chosen to begin with
  	$sessionresult = $mysqli->query("SELECT * FROM Sessions WHERE `Sessions`.`name` = '" . $_POST['session'] . "' LIMIT 1"); //select the one that was randomly picked
  	$row = $sessionresult->fetch_array();
  	$adminname = $row['admin'];
  	$adminresult = $mysqli->query("SELECT * FROM Players WHERE `Players`.`username` = '$adminname' LIMIT 1;");
  	$adminrow = $adminresult->fetch_array();
  	if ($currenttime - $adminrow['lasttick'] > $inactivetime || $inactivetime == 0) { //head admin hasn't logged in for the amount of time specified
  		$pickedsession++; //try the next session
  		if ($pickedsession > $allowsessions) $pickedsession = 0;
  		if ($pickedsession == $firstattempt) $playerclash = true;
  	} else $foundactivesession = true;
  	}
  	if ($foundactivesession) $sessionresult = $mysqli->query("SELECT * FROM Sessions WHERE `Sessions`.`name` = '" . $_POST['session'] . "' LIMIT 1"); //select the one that was randomly picked
  	else echo "Player entry failed: No sessions that are allowing random entries seem to have an active head admin.";
  }
}

if ($_POST['username'] != "" && $_POST['password'] == $_POST['confirmpw']) {
  while ($row = $playerresult->fetch_array()) {
    if ($_POST['username'] == $row['username']) { //Name clash: Player name is already taken.
      echo "Player entry failed: Player with this name is already in the Medium.";
      $playerclash = True;
    }
  }
  if ($playerclash == False) {
    while ($row = $sessionresult->fetch_array()) {
      if ($_POST['session'] == $row['name'] && ($_POST['sessionpw'] == $row['password'] || !empty($_POST['randomsession']))) {
	$sessionlogin = True;
      }
    }
    if ($sessionlogin == True) {
      $name = $mysqli->real_escape_string($_POST['username']);
      $pw = crypt($mysqli->real_escape_string($_POST['password']));
      $session = $mysqli->real_escape_string($_POST['session']);
      echo "Now entering session $session </br>"; //echo this so that randoms know what they're getting into
      if ($_POST['email'] == $_POST['cemail']) $email = $mysqli->real_escape_string($_POST['email']);
      else {
      	echo "The email and confirm email fields didn't match, so your email will be left blank for now. You can set it in Player Settings whenever you are ready.</br>";
      	$email = "";
      }
      $protostrength = intval($mysqli->real_escape_string($_POST['prototyping_strength']));
      $spritestrength = $protostrength * 2; //Sprite receives twice as much power from prototyping as an enemy would.
      $spritename = $mysqli->real_escape_string($_POST['sprite_name']);
      $protoitem1 = $mysqli->real_escape_string($_POST['protoitem1']);
      $protoitem2 = $mysqli->real_escape_string($_POST['protoitem2']);
      $client = $mysqli->real_escape_string($_POST['client']);
      $dreamer = $_POST['dreamer'];
      $gristtype = $_POST['grist_type'];
      $clientfound = False;
      $sessionmates = $mysqli->query("SELECT * FROM Players WHERE `Players`.`session_name` = '$session'");
      $chumroll = 0;
      while ($row = $sessionmates->fetch_array()) {
	if ($row['session_name'] == $session) {
	  $chumroll++;
	  if ($row['username'] == $client && $row['server_player'] == "") {
	    $clientrow = $row;
	    $clientfound = True; //Player exists in session and does not already have a server player
	  }
	}
      }
      $grist = 20;
      while ($chumroll > 0 && $grist < 20000) { //Increment starting grist based on connections.
	$grist = ($grist * 10);
	$chumroll--;
      }
      if ($clientfound || $client == "") { //No client or specified client found.
	if (!empty($_POST['land1']) && !empty($_POST['land2'])) {
	  $land1 = $mysqli->real_escape_string($_POST['land1']);
	  $land2 = $mysqli->real_escape_string($_POST['land2']);
	  if ($protostrength < 1000 && $protostrength > -1000 && $protoitem1 != "") { //Valid prototype strength, item 1 prototyped.
	    $spritename = $spritename . "sprite";
	    if ($protoitem2 != "") { //Double prototyped!
	      $mysqli->query("INSERT INTO `Players` (`username` ,`password` ,`email` ,`session_name` ,`Build_Grist` ,`sprite_name` ,`client_player` ,`prototyping_strength` ,`prototype_item_1` ,`prototype_item_2` ,`sprite_strength` ,`pre_entry_prototypes` ,`grist_type` ,`land1` ,`land2` ,`dreamer`) VALUES ('$name', '$pw', '$email', '$session', '$grist', '$spritename', '$client', '$protostrength', '$protoitem1', '$protoitem2', '$spritestrength', '2', '$gristtype', '$land1', '$land2', '$dreamer');");
	    } else {
	      $mysqli->query("INSERT INTO `Players` (`username` ,`password` ,`email` ,`session_name` ,`Build_Grist` ,`sprite_name` ,`client_player` ,`prototyping_strength` ,`prototype_item_1` ,`prototype_item_2` ,`sprite_strength` ,`pre_entry_prototypes` ,`grist_type` ,`land1` ,`land2` ,`dreamer`) VALUES ('$name', '$pw', '$email', '$session', '$grist', '$spritename', '$client', '$protostrength', '$protoitem1', '$protoitem2', '$spritestrength', '1', '$gristtype', '$land1', '$land2', '$dreamer');");
	    }
	  } else {
	    $spritename = "Sprite";
	    $mysqli->query("INSERT INTO `Players` (`username` ,`password` ,`email` ,`session_name` ,`Build_Grist` ,`sprite_name` ,`client_player` ,`prototyping_strength` ,`pre_entry_prototypes` ,`grist_type` ,`land1` ,`land2` ,`dreamer`) VALUES ('$name', '$pw', '$email', '$session', '$grist', '$spritename', '$client', '0', '0', '$gristtype', '$land1', '$land2', '$dreamer');");  //YOUR PROTOTYPING HAS FAILED
	  }
	  if ($clientfound) { //Client player registered
	    $mysqli->query("UPDATE `Players` SET `server_player` = '$name' WHERE `Players`.`username` = '$clientrow[username]' LIMIT 1 ;");
	  }
	  $mysqli->query("INSERT INTO `Echeladders` (`username`) VALUES ('$name');"); //Give the player an Echeladder. Players love echeladders.
	  $mysqli->query("INSERT INTO `Messages` (`username`) VALUES ('$name');"); //Create entry in message table.
	  $mysqli->query("INSERT INTO `Ability_Patterns` (`username`) VALUES ('$name');"); //Create entry in pattern table.
	  $sessionresult = $mysqli->query("SELECT * FROM Sessions WHERE `Sessions`.`name` = '" . $session . "'");
	  $sessionrow = $sessionresult->fetch_array();
	  if ($sessionrow['admin'] == "default") {
	    $mysqli->query("UPDATE `Sessions` SET `admin` = '" . $name . "' WHERE `name` = '$session' LIMIT 1 ;");
	    $mysqli->query("UPDATE `Players` SET `admin` = 1 WHERE `Players`.`username` = '$name' LIMIT 1 ;");
	  }
	  echo "Player $name entry successful. You have been credited with $grist Build Grist.";
	} else {
	  echo "Player entry failed: Lands not specified.";
	}
      } else {
	echo "Player entry failed: Client player not found or already has a server player.";
      }
    } else {
      echo "Player entry failed: Session details incorrect.";
    }
  }
} else {
  echo "Player entry failed: Player name empty or passwords do not match.";
}
$mysqli->close();
require_once("footer.php");
echo '</br><a href="/">Home</a>';
?> 