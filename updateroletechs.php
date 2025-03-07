<?php
require_once "header.php";
require_once "includes/SQLconnect.php";
if ($username != "The Overseer") {
	echo "This is a developer script. It doesn't even do anything interesting, just some stuff related to updating the database. Seriously, total snoozefest.";
} else {
	//This file contains all the data in the abilities table. It is used to update the table and may also be used to restore it.
	//NOTE - That jumble of five numbers is: Aspect vial cost, rung required, god tier required, whether the ability is active, and whether it targets.
	$mysqli->query("INSERT INTO `Abilities` (`ID` ,`Name` ,`Aspect` ,`Class` ,`Aspect_Cost` ,`Rungreq` ,`Godtierreq` ,`Active` ,`targets` ,`Description` ,`Usagestr`) VALUES 
('1', '" . $mysqli->real_escape_string("Passive Aggress") . "', 'All', 'Seer', '0', '7', '0', '0', '0', '
" . $mysqli->real_escape_string("The Seer may now benefit from their passive multiplier whenever they use the Aggress command, even if it is used actively.") . "',  '
" . $mysqli->real_escape_string("Lv. 7 Seer ability Passive Aggress activates! You take advantage of your passive bonus this round.") . "');");
	$mysqli->query("INSERT INTO `Abilities` (`ID` ,`Name` ,`Aspect` ,`Class` ,`Aspect_Cost` ,`Rungreq` ,`Godtierreq` ,`Active` ,`targets` ,`Description` ,`Usagestr`) VALUES 
('2', '" . $mysqli->real_escape_string("Life's Bounty") . "', 'Life', 'All', '0', '17', '0', '0', '0', '
" . $mysqli->real_escape_string("Life, vibrant and potent, flows through you and is naturally harder to remove from you. You receive 85% damage from regular attacks.") . "',  '
" . $mysqli->real_escape_string("Lv. 17 Lifepower Life's Bounty activates! You receive 85% damage.") . "');");
	$mysqli->query("INSERT INTO `Abilities` (`ID` ,`Name` ,`Aspect` ,`Class` ,`Aspect_Cost` ,`Rungreq` ,`Godtierreq` ,`Active` ,`targets` ,`Description` ,`Usagestr`) VALUES 
('3', '" . $mysqli->real_escape_string("Chaotic Assault") . "', 'All', 'Bard', '0', '397', '0', '0', '0', '
" . $mysqli->real_escape_string("The Bard's unarmed power modifier fluctuates wildly whenever the Assualt command is used.") . "',  '
" . $mysqli->real_escape_string("Lv. 397 Minstreltech Chaotic Assault activates! Your unarmed power fluctuates wildly.") . "');");
	$mysqli->query("INSERT INTO `Abilities` (`ID` ,`Name` ,`Aspect` ,`Class` ,`Aspect_Cost` ,`Rungreq` ,`Godtierreq` ,`Active` ,`targets` ,`Description` ,`Usagestr`) VALUES 
('4', '" . $mysqli->real_escape_string("Dissipate") . "', 'Breath', 'All', '0', '205', '0', '0', '0', '
" . $mysqli->real_escape_string("Grants you a chance to dissolve into air, avoiding your opponent's strikes. This chance increases with rung.") . "',  '
" . $mysqli->real_escape_string("Level 205 Breath Skill Dissipate activates! You dissipate around your opponent's attacks, taking no damage.") . "');");
	$mysqli->query("INSERT INTO `Abilities` (`ID` ,`Name` ,`Aspect` ,`Class` ,`Aspect_Cost` ,`Rungreq` ,`Godtierreq` ,`Active` ,`targets` ,`Description` ,`Usagestr`) VALUES 
('5', '" . $mysqli->real_escape_string("Dissipate: Focus") . "', 'Breath', 'All', '4000', '400', '0', '1', '0', '
" . $mysqli->real_escape_string("Focus yourself, guaranteeing that Dissipate will activate against every attack next round.") . "',  '
" . $mysqli->real_escape_string("You focus carefully through the Aspect of Breath, allowing you to dissolve into air and back at will.") . "');");
	$mysqli->query("INSERT INTO `Abilities` (`ID` ,`Name` ,`Aspect` ,`Class` ,`Aspect_Cost` ,`Rungreq` ,`Godtierreq` ,`Active` ,`targets` ,`Description` ,`Usagestr`) VALUES 
('6', '" . $mysqli->real_escape_string("Esauna") . "', 'Life', 'Mage', '700', '113', '0', '1', '1', '
" . $mysqli->real_escape_string("Soothing steam cleanses the target, removing all debuffing effects from them.") . "',  '
" . $mysqli->real_escape_string("You use Lv. 113 Whi- er, Life Magic, Esauna: Cleansing steam relaxes the target, removing all debuffs from them.") . "');");
	$mysqli->query("INSERT INTO `Abilities` (`ID` ,`Name` ,`Aspect` ,`Class` ,`Aspect_Cost` ,`Rungreq` ,`Godtierreq` ,`Active` ,`targets` ,`Description` ,`Usagestr`) VALUES 
('7', '" . $mysqli->real_escape_string("Aspect Fighter") . "', 'All', 'Knight', '0', '83', '0', '0', '0', '
" . $mysqli->real_escape_string("You gain the ability to use your Aspect to bolster your fighting strength at no cost. You receive offensive and defensive bonuses based on how offensively minded and defensively minded your Aspect is.") . "',  '
" . $mysqli->real_escape_string("Level 83 Knightskill Aspect Fighter activates!") . "');"); //Most of the message is dynamic and so not stored here.
	$mysqli->query("INSERT INTO `Abilities` (`ID` ,`Name` ,`Aspect` ,`Class` ,`Aspect_Cost` ,`Rungreq` ,`Godtierreq` ,`Active` ,`targets` ,`Description` ,`Usagestr`) VALUES 
('8', '" . $mysqli->real_escape_string("Seek Fortune's Path") . "', 'Light', 'Seer', '1000', '137', '0', '1', '0', '
" . $mysqli->real_escape_string("You see the paths and courses of action bathed in fortune's Light, and advise your allies accordingly.") . "',  '
" . $mysqli->real_escape_string("As the alighted path reveals itself to you, you offer your allies a course of action sure to improve their fortune.") . "');");
	$mysqli->query("INSERT INTO `Abilities` (`ID` ,`Name` ,`Aspect` ,`Class` ,`Aspect_Cost` ,`Rungreq` ,`Godtierreq` ,`Active` ,`targets` ,`Description` ,`Usagestr`) VALUES 
('9', '" . $mysqli->real_escape_string("Aspect Connection") . "', 'All', 'Mage', '0', '57', '0', '0', '0', '
" . $mysqli->real_escape_string("Increases the amount of Aspect Vial you receive from resting to 150% of the regular rate.") . "',  '
" . $mysqli->real_escape_string("Lv. 57 Magus Operandi Aspect Connection activates! Your connection to your Aspect strengthens even further.") . "');");
	$mysqli->query("INSERT INTO `Abilities` (`ID` ,`Name` ,`Aspect` ,`Class` ,`Aspect_Cost` ,`Rungreq` ,`Godtierreq` ,`Active` ,`targets` ,`Description` ,`Usagestr`) VALUES 
('10', '" . $mysqli->real_escape_string("Hey! Listen") . "', 'All', 'Sylph', '0', '135', '0', '0', '0', '
" . $mysqli->real_escape_string("You are a font of helpful advice and assistance! Your aspect patterns have 120% power when targeted at an ally.") . "',  '
" . $mysqli->real_escape_string("Lv. 135 Navitech HEY! LISTEN! activates!! You offer high-quality assistance to your ally, increasing the effectiveness of the buff.") . "');");
	$mysqli->query("INSERT INTO `Abilities` (`ID` ,`Name` ,`Aspect` ,`Class` ,`Aspect_Cost` ,`Rungreq` ,`Godtierreq` ,`Active` ,`targets` ,`Description` ,`Usagestr`) VALUES 
('11', '" . $mysqli->real_escape_string("Blockhead") . "', 'Mind', 'All', '0', '29', '0', '0', '0', '
" . $mysqli->real_escape_string("Allows you to shake off negative power effects over time.") . "',  '
" . $mysqli->real_escape_string("Level 29 Mindcraft Blockhead activates! A combination of combat focus and stubbornness removes some of the power drain affecting you.") . "');");
	$mysqli->query("INSERT INTO `Abilities` (`ID` ,`Name` ,`Aspect` ,`Class` ,`Aspect_Cost` ,`Rungreq` ,`Godtierreq` ,`Active` ,`targets` ,`Description` ,`Usagestr`) VALUES 
('12', '" . $mysqli->real_escape_string("Battle Fury") . "', 'Rage', 'All', '0', '67', '0', '0', '0', '
" . $mysqli->real_escape_string("Things hitting you understandably make you very angry, which then lets you hit them harder. There's only so angry you can get, but the higher your rung, the angrier that is.") . "',  '
" . $mysqli->real_escape_string("Lv. 67 Angerbility Battle Fury activates! You're not particularly being happy about getting hit.") . "');");
	$mysqli->query("INSERT INTO `Abilities` (`ID` ,`Name` ,`Aspect` ,`Class` ,`Aspect_Cost` ,`Rungreq` ,`Godtierreq` ,`Active` ,`targets` ,`Description` ,`Usagestr`) VALUES 
('13', '" . $mysqli->real_escape_string("Spatial Warp") . "', 'Space', 'All', '0', '239', '0', '0', '0', '
" . $mysqli->real_escape_string("Space bends around you, causing your enemies to be struck by just a little bit of their own attacks.") . "',  '
" . $mysqli->real_escape_string("Lv. 239 Spacebending Spatial Warp activates! Your assailant is hit by a little of the force from their attack.") . "');");
	$mysqli->query("INSERT INTO `Abilities` (`ID` ,`Name` ,`Aspect` ,`Class` ,`Aspect_Cost` ,`Rungreq` ,`Godtierreq` ,`Active` ,`targets` ,`Description` ,`Usagestr`) VALUES 
('14', '" . $mysqli->real_escape_string("Strength of Spirit") . "', 'Heart', 'All', '0', '87', '0', '0', '0', '
" . $mysqli->real_escape_string("You draw strength from within yourself. Your aspect patterns cost 15% less to use.") . "',  '
" . $mysqli->real_escape_string("Lv. 87 Heartpower Strength of Spirit activates! Your ability costs less to use.") . "');");
	$mysqli->query("INSERT INTO `Abilities` (`ID` ,`Name` ,`Aspect` ,`Class` ,`Aspect_Cost` ,`Rungreq` ,`Godtierreq` ,`Active` ,`targets` ,`Description` ,`Usagestr`) VALUES 
('15', '" . $mysqli->real_escape_string("One with Nothing") . "', 'Void', 'All', '0', '0', '0', '0', '0', '
" . $mysqli->real_escape_string("You do not need equipment in order to be dangerous.") . "',  '
" . $mysqli->real_escape_string("Lv. 0 Void  One with Nothing activates! You fight unarmed, unaided, and powerfully.") . "');");
	$mysqli->query("INSERT INTO `Abilities` (`ID` ,`Name` ,`Aspect` ,`Class` ,`Aspect_Cost` ,`Rungreq` ,`Godtierreq` ,`Active` ,`targets` ,`Description` ,`Usagestr`) VALUES 
('16', '" . $mysqli->real_escape_string("Temporal Warp") . "', 'Time', 'All', '0', '1', '0', '0', '0', '
" . $mysqli->real_escape_string("All of your cooldowns are reduced to 90% of the normal values.") . "',  '
" . $mysqli->real_escape_string("Lv. 1 Timetech Temporal Warp activates! Your cooldowns are lower.") . "');");
	$mysqli->query("INSERT INTO `Abilities` (`ID` ,`Name` ,`Aspect` ,`Class` ,`Aspect_Cost` ,`Rungreq` ,`Godtierreq` ,`Active` ,`targets` ,`Description` ,`Usagestr`) VALUES 
('17', '" . $mysqli->real_escape_string("Blood Bonds") . "', 'Blood', 'All', '0', '78', '0', '0', '0', '
" . $mysqli->real_escape_string("Enhances your bond with your allies. Your unarmed power receives a multiplier equal to the number of players in the strife.") . "',  '
" . $mysqli->real_escape_string("Lv. 78 Bloodbending Blood Bonds activates! You are empowered by your connection to your allies.") . "');");
	$mysqli->query("INSERT INTO `Abilities` (`ID` ,`Name` ,`Aspect` ,`Class` ,`Aspect_Cost` ,`Rungreq` ,`Godtierreq` ,`Active` ,`targets` ,`Description` ,`Usagestr`) VALUES 
('18', '" . $mysqli->real_escape_string("Temporal Doppelganger") . "', 'Time', 'All', '200', '31', '0', '1', '0', '
" . $mysqli->real_escape_string("A version of you from the very recent future or past or even a doomed timeline appears, strikes your enemies, and vanishes just as quickly. This roletech costs an encounter to activate.") . "',  '
" . $mysqli->real_escape_string("You use Lv. 31 Timetech: Temporal Doppelganger. Your not-present self joins in the fight briefly before disappearing back to their own time and/or timeline.") . "');");
	$mysqli->query("INSERT INTO `Abilities` (`ID` ,`Name` ,`Aspect` ,`Class` ,`Aspect_Cost` ,`Rungreq` ,`Godtierreq` ,`Active` ,`targets` ,`Description` ,`Usagestr`) VALUES 
('19', '" . $mysqli->real_escape_string("Light's Favour") . "', 'Light', 'All', '0', '30', '0', '0', '0', '
" . $mysqli->real_escape_string("Passively makes you luckier.") . "',  '
" . $mysqli->real_escape_string("Lv. 30 Lightthing Light's Favour activates! You feel luckier somehow.") . "');");
	$mysqli->query("INSERT INTO `Abilities` (`ID` ,`Name` ,`Aspect` ,`Class` ,`Aspect_Cost` ,`Rungreq` ,`Godtierreq` ,`Active` ,`targets` ,`Description` ,`Usagestr`) VALUES 
('20', '" . $mysqli->real_escape_string("Hope Endures") . "', 'Hope', 'All', '0', '306', '0', '0', '0', '
" . $mysqli->real_escape_string("You have a chance to survive fatal attacks with a sliver of health remaining. This chance is equal to the percentage of your Aspect Vial remaining. When this occurs, your Vial is decreased by a flat amount.") . "',  '
" . $mysqli->real_escape_string("Lv. 306 Hopetech Hope Endures activates! You endure.") . "');");
	$mysqli->query("INSERT INTO `Abilities` (`ID` ,`Name` ,`Aspect` ,`Class` ,`Aspect_Cost` ,`Rungreq` ,`Godtierreq` ,`Active` ,`targets` ,`Description` ,`Usagestr`) VALUES 
('21', '" . $mysqli->real_escape_string("Inevitability") . "', 'Doom', 'All', '0', '108', '0', '0', '0', '
" . $mysqli->real_escape_string("Your attacks deal bonus damage based on the target's missing health.") . "',  '
" . $mysqli->real_escape_string("Lv. 108 Doomskill Inevitability activates! Additional damage is dealt.") . "');");
	$mysqli->query("INSERT INTO `Abilities` (`ID` ,`Name` ,`Aspect` ,`Class` ,`Aspect_Cost` ,`Rungreq` ,`Godtierreq` ,`Active` ,`targets` ,`Description` ,`Usagestr`) VALUES 
('22', '" . $mysqli->real_escape_string("Broken Record") . "', 'Time', 'Heir', '0', '327', '0', '0', '0', '
" . $mysqli->real_escape_string("Your attacks have a chance of repeating themselves") . "',  '
" . $mysqli->real_escape_string("Lv. 327 Timetech Broken Record activates! Time skips backwards and forwards across the moment of your blow, causing it to land multiple times.") . "');");
	$mysqli->query("INSERT INTO `Abilities` (`ID` ,`Name` ,`Aspect` ,`Class` ,`Aspect_Cost` ,`Rungreq` ,`Godtierreq` ,`Active` ,`targets` ,`Description` ,`Usagestr`) VALUES 
('23', '" . $mysqli->real_escape_string("Fortune's Protection") . "', 'Light', 'Heir', '0', '259', '0', '0', '0', '
" . $mysqli->real_escape_string("You gain a passive critical hit chance. In addition, enemies have a random chance of striking you for less damage.") . "',  '
" . $mysqli->real_escape_string("Lv. 259 Lightskill Fortune's Protection activates! Your opponent performs an anti-critical.") . "');");
	$mysqli->query("INSERT INTO `Abilities` (`ID` ,`Name` ,`Aspect` ,`Class` ,`Aspect_Cost` ,`Rungreq` ,`Godtierreq` ,`Active` ,`targets` ,`Description` ,`Usagestr`) VALUES 
('24', '" . $mysqli->real_escape_string("Aspect Obliteration") . "', 'All', 'Prince', '0', '93', '0', '0', '0', '
" . $mysqli->real_escape_string("You can manipulate your aspect to wreak destruction on all. As a Prince, you use your Aspect's highest modifier in place of that Aspect's direct damage modifier. [UPCOMING]") . "',  '
" . $mysqli->real_escape_string("Lv. 93 Royaltech Aspect Obliteration activates! You manipulate your Aspect's strength to annihilate your foes.") . "');");
	$mysqli->query("INSERT INTO `Abilities` (`ID` ,`Name` ,`Aspect` ,`Class` ,`Aspect_Cost` ,`Rungreq` ,`Godtierreq` ,`Active` ,`targets` ,`Description` ,`Usagestr`) VALUES 
('25', '" . $mysqli->real_escape_string("Siphon") . "', 'All', 'Thief', '0', '37', '0', '0', '0', '
" . $mysqli->real_escape_string("When in strife, your damaging aspect patterns heal you proportionately, your power draining patterns boost you proportionally, and vice versa. [UPCOMING]") . "',  '
" . $mysqli->real_escape_string("Lv. 37 Thief Art Siphon activates! Your aspect pattern functions by draining from your foes.") . "');");
	$mysqli->query("INSERT INTO `Abilities` (`ID` ,`Name` ,`Aspect` ,`Class` ,`Aspect_Cost` ,`Rungreq` ,`Godtierreq` ,`Active` ,`targets` ,`Description` ,`Usagestr`) VALUES 
('26', '" . $mysqli->real_escape_string("Capriciousness") . "', 'All', 'Bard', '0', '0', '0', '0', '0', '
" . $mysqli->real_escape_string("All use of Aspect Patterns is subject to completely wild variation, from obscenely powerful to backfiring. [UPCOMING]") . "',  '
" . $mysqli->real_escape_string("Lv. -1 Bardthing Capriciousness activates! WHEEEEEEEEEEEEEEEEEEEEEEEEEE!") . "');");
	echo "Done!";
}
?>