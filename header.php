<?php
session_start();
$supertime_begin = microtime(true);
require_once 'includes/headerin.php';
require_once 'includes/global_functions.php';
?>

<!DOCTYPE html>
<html>

<head>
	<title>The Overseer Project</title>
	<meta name="description" content="The Overseer Project is a free text-based roleplaying game based on Homestuck's SBURB system, featuring Alchemy, Strifing, Denizens, Quests and more">
	<meta name="keywords" content="homestuck,SBURB,rpg,game,browser game,simulator,roleplaying,rp,overseer project,alchemy,strifing">
	<link href="core.css?1" rel="stylesheet" />
	<link href="coring.css?1" rel="stylesheet" media="screen and (max-width: 1000px)" />
	<link href="mobile.css?1" rel="stylesheet" media="screen and (max-width: 800px)" />

	<?php
	$imagestr = "Images/title/corpia.png";
	if (!empty($userrow['dreamingstatus'])) {
		if ($userrow['dreamingstatus'] == "Prospit") { //User on Prospit
			echo '<link href="prospit.css?1" rel="stylesheet"/>';
			$imagestr = "Images/title/corpiaprospit.png";
		} elseif ($userrow['dreamingstatus'] == "Derse") {
			echo '<link href="derse.css?1" rel="stylesheet"/>';
		}
	}
	if (mdetect())
	{
		echo '<link href="coring.css?1" rel="stylesheet"/>';
		echo '<link href="mobile.css?1" rel="stylesheet"/>';
	}
	?>

	<?php if (!empty($userrow['colour'])) echo "<style>favcolour{color: $userrow[colour];}</style>"; ?>

	<meta name="viewport" content="width=device-width">
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<script src="https://code.jquery.com/jquery-latest.min.js" type="text/javascript"></script>

	<?php if (mdetect()) { ?>

		<script>
			$(document).ready(function () {
				$("ul.drop li ul").hide();
				$("ul.drop li").click(function (e) {
					e.stopPropagation();
					var child = $(this).children("ul");
					vis = $(child).is(":visible");
					$("ul.drop li ul").hide();
					vis ? child.hide() : child.show();
				});
				$("ul.adrop li").click(function (e) {
					e.stopPropagation();
					var child = $(this).children("ul");
					vis = $(child).is(":visible");
					$("ul.adrop li ul").hide();
					vis ? child.hide() : child.show();
				});
				$("#banner, #spanner, body, #mained, html").click(function () {
					$("ul.drop li ul, ul.adrop li ul").hide();
				});
			});
		</script>
		<style>
			.asessions {
				width: 80px;
			}

			.aabout {
				width: 110px;
			}

			.aplayer {
				width: 115px;
			}

			.astrife {
				width: 120px;
			}

			.aexplore {
				width: 100px;
			}

			.ashop {
				width: 100px;
			}

			.rhyme {
				text-align: center;
			}
		</style>

	<?php } else { ?>

		<style>
			ul.drop li:hover>ul {
				display: block;
			}

			ul.adrop li:hover>ul {
				display: block;
			}
		</style>

	<?php } ?>

	<script>
		window.start = new Date().getTime();
		var countdown = setInterval(function () {
			window.minutes = parseInt($("span.c1").html());
			window.seconds = parseInt($("span.c2").html());
			window.encounters = parseInt($("span.c3").html());
			var current = new Date().getTime();
			var diff = current - start;
			if (diff >= 1000) {
				window.seconds--;
				if (window.seconds == -1) {
					window.seconds = 59;
					window.minutes--;
				}
				if (window.minutes == -1) {
					window.minutes = 0;
					window.seconds = 10;
					if (window.encounters < 100) {
						window.encounters++;
					}
				}
				$("span.c1, span.d1").html(("0" + window.minutes).slice(-2));
				$("span.c2, span.d2").html(("0" + window.seconds).slice(-2));
				$("span.c3, span.d3").html(window.encounters);
				window.start = current - (diff - 1000);
			}
		}, 10);

		$(document).ready(function () {
			$('li.dream').click(function () {
				$('#dreamsequence').submit();
			});
		});
	</script>
</head>

<body>

	<div id="mained">
		<div id="banner">
			<a href="/">
				<div id="bannerd"></div><img id="banning" src="/Images/title/banner.png">
			</a>

			<?php
			if (empty($_SESSION['username'])) {
				?>
				<div class="intercross">
					<script>
						$(document).ready(function () {
							var username = "";
							var password = "";
							$('#login').submit(function () {
								username = $("#username").val();
								password = $("#password").val();
								$('#password').val('');
								$('#catch').text('Logging you in...').attr('style', 'color: #FFA500;');
								$.post("login.php", { username: username, password: password, mako: "kawaii" })
									.done(function (data) {
										if (data == "true") {
											window.location = 'index.php';
											$('#catch').text('Success!').attr('style', 'color: green;');
										}
										else {
											$('#catch').text('Incorrect login details').attr('style', 'color: red;');
										}
									});
								return false;
							});
						});
					</script>

					<?php if (empty($_SESSION['username'])) { ?>

						<style>
							.intercross {
								height: 70px;
							}
						</style>
						<form id="login" action="login.php" method="post">
							<p>Username: <input id="username" maxlength="50" name="username" type="text"/></p>
							<p>Password: <input id="password" maxlength="50" name="password" type="password"/></p>
							<input name="Submit" type="submit" value="Submit"/>
						</form>
						<span style="color: red;" id="catch"></span>

					<?php } else { ?>

						<script>
							$(document).ready(function () {
								window.location = 'index.php';
							});
						</script>

					<?php } ?>

				</div>

				<div class="intermix">
					<script>
						$(document).ready(function () {
							var username = "";
							var password = "";
							$('#logina').submit(function () {
								usernamea = $("#usernamea").val();
								passworda = $("#passworda").val();
								$('#passworda').val('');
								$('.catch').text('Logging you in...').attr('style', 'color: #FFA500;');
								$.post("login.php", { username: usernamea, password: passworda, mako: "kawaii" })
									.done(function (data) {
										if (data == "true") {
											window.location = 'index.php';
											$('.catch').text(' Success!').attr('style', 'color: green;');
										}
										else {
											$('.catch').text(' Incorrect login details').attr('style', 'color: red;');
										}
									});
								return false;
							});
						});
					</script>

					<?php if (empty($_SESSION['username'])) { ?>

						<form id="logina" action="login.php" method="post">
							&nbsp;
							<nobr>Username: <input id="usernamea" maxlength="50" name="usernamea" type="text" /></nobr>
							<nobr>Password: <input id="passworda" maxlength="50" name="passworda" type="password" /></nobr>
							<input name="Submit" type="submit" value="Submit" />
						</form>
						<span style="color: red;" class="catch"></span>

					<?php } else { ?>

						<script>
							$(document).ready(function () {
								window.location = 'index.php';
							});
						</script>

					<?php } ?>
				</div>
			<?php } else { ?>
				<div class="intercross">
					<?php
					if ($userrow['Boondollars'] > 10000000) {
						$booned = number_format($userrow['Boondollars'] / 1000000, 2);
						$booni = "bucki.png";
					} else {
						$booned = number_format($userrow['Boondollars']);
						$booni = "booni.png";
					}
					$gristed = number_format($userrow['Build_Grist']);
					$ecchi = $userrow['Echeladder'];
					$classy = "Class";
					$classresulta = $mysqli->query("SELECT * FROM `Class_modifiers` WHERE `Class_modifiers`.`Class` = '$userrow[$classy]';");
					$classrowa = $classresulta->fetch_array();
					$unarmedpowera = floor($userrow['Echeladder'] * (pow(((empty($classrowa['godtierfactor']) ? 0 : $classrowa['godtierfactor']) / 100), $userrow['Godtier'])));
					
					if (!empty($_POST['equipmain']))
						$mainhandInvSlot = $_POST['equipmain'];
					elseif (!empty($userrow['equipped']))
						$mainhandInvSlot = $userrow['equipped'];
					else
						$mainhandInvSlot = '';
					$mainPower = getItemPower($userrow, $mainhandInvSlot);

					if (!empty($_POST['equipoff']))
						$offhandInvSlot = $_POST['equipoff'];
					elseif (!empty($userrow['offhand']) && $userrow['offhand'] != $userrow['equipped'])
						$offhandInvSlot = $userrow['offhand'];
					else
						$offhandInvSlot = '';
					$offPower = getItemPower($userrow, $offhandInvSlot) / 2;

					$spritePower = $userrow['sprite_strength'];
					if ($spritePower < 0)
						$spritePower = 0;
					
					if ($userrow['dreamingstatus'] == "Awake") {
						$healthy = strval(floor(($userrow['Health_Vial'] / $userrow['Gel_Viscosity']) * 100)); //Computes % of max HP remaining.
						$powerLevel = $unarmedpowera + $mainPower + $offPower + $spritePower + $userrow['powerboost'];
					} else {
						$healthy = strval(floor(($userrow['Dream_Health_Vial'] / $userrow['Gel_Viscosity']) * 100)); //Computes % of max HP remaining in a Dreaming state.
						$powerLevel = $unarmedpowera + $userrow['powerboost'];
					}

					$minuta = strval(produceMinutes($interval - ($time - $lasttick)));
					$seconda = strval(produceSeconds($interval - ($time - $lasttick)));
					?>
					<span class="ripe">
						<div class="lined">
							<a href="overview.php">
								<?php echo $_SESSION['username']; ?>
							</a>
						</div>
						<div class="pined">
							<a href="overview.php"><img src="/Images/title/health.png" align="center" title="Health"></a>
							<?php echo $healthy; ?>%
						</div>
					</span>

					<div class="lefy">
						<a href="strife.php"><img src="/Images/title/sl.png" align="center" title="Number of Encounters"></a> <span class="c3">
							<?php echo strval($encounters); ?>
						</span>
						<br/>
						<a href="portfolio.php"><img src="/Images/title/power.png" align="center" title="Strife Power"></a>
						<?php echo $powerLevel; ?>
						<br/>
						<a href="grist.php"><img src="/Images/title/gristling.png" align="center" title="Grist Count"></a>
						<?php echo strval($gristed); ?>
					</div>

					<div class="righy">
						<a href="strife.php"><img src="/Images/title/enc.png" align="center" title="Time Until Next Encounter"></a> <span class="c1">
							<?php echo $minuta; ?>
						</span>:<span class="c2">
							<?php echo $seconda; ?>
						</span>
						<br/>
						<a href="echeviewer.php"><img src="/Images/title/eche.png" align="center" title="Echeladder"></a>
						<?php echo strval($ecchi); ?>
						<br/>
						<a href="porkhollow.php"><img src="/Images/title/<?php echo strval($booni); ?>" align="center" title="Boondollars"></a>
						<?php echo strval($booned); ?>
					</div>

				</div>

				<div class="intermix">
					&nbsp;<span class="pined"><a href="overview.php">
							<?php echo $_SESSION['username']; ?>
						</a></span>
					<nobr><a href="overview.php"><img src="/Images/title/health.png" align="center" title="Health"></a>
						<?php echo $healthy; ?>%
					</nobr>
					<nobr><a href="strife.php"><img src="/Images/title/sl.png" align="center" title="Number of Encounters"></a><span class="d3">
							<?php echo strval($encounters); ?>
						</span></nobr>
					<nobr><a href="strife.php"><img src="/Images/title/enc.png" align="center"
								title="Time Until Next Encounter"></a>&nbsp;<span class="d1">
							<?php echo $minuta; ?>
						</span>:<span class="d2">
							<?php echo $seconda; ?>
						</span></nobr>
					<nobr><a href="portfolio.php"><img src="/Images/title/power.png" align="center"
								title="Strife Power"></a>
						<?php echo $powerLevel; ?>
					</nobr>
					<nobr><a href="echeviewer.php"><img src="/Images/title/eche.png" align="center" title="Echeladder"></a>
						<?php echo strval($ecchi); ?>
					</nobr>
					<nobr><a href="grist.php"><img src="/Images/title/gristling.png" align="center" title="Grist Count"></a>
						<?php echo strval($gristed); ?>
					</nobr>
					<nobr><a href="porkhollow.php"><img src="/Images/title/<?php echo strval($booni); ?>" align="center"
								title="Boondollars"></a>
						<?php echo strval($booned); ?>
					</nobr>
				</div>
			<?php } ?>
		</div>

		<div id="spanner">
			<?php
			if (empty($_SESSION['username'])) { ?>
				<ul id="nav" class="drop">
					<li><a href="loginer.php"><span class="rhyme slam alogin">&gt;LOGIN</span></a></li>

					<li><span class="rhyme slam asessions">SESSIONS</span>
						<ul>
							<li><a href="sessionform.php"><span class="rhyme asessions bsessions">&gt;START
										SESSION</span></a></li>
							<li><a href="playerform.php"><span class="rhyme asessions bsessions">&gt;JOIN SESSION</span></a>
							</li>
							<li><a href="sessioninfo.php"><span class="rhyme asessions bsessions">&gt;VIEW
										SESSION</span></a></li>
							<li><a href="forgotpassword.php"><span class="rhyme asessions bsessions">&gt;PASSWORD
										RECOVERY</span></a></li>
						</ul>
					</li>

					<li><span class="rhyme slam aabout">ABOUT</span>
						<ul>
							<li><a href="credits.php"><span class="rhyme aabout babout">&gt;Credits</span></a></li>
							<li><a href="changelog.php"><span class="rhyme aabout babout">&gt;Change Log</span></a></li>
							<li><a href="news.php"><span class="rhyme aabout babout">&gt;Items/<wbr>Art Updates</span></a></li>
							<li><a href="randomizer.php"><span class="rhyme aabout babout">&gt;Random Combinations</span></a></li>
							<li><a href="https://patreon.com/OverseerReboot"><span class="rhyme aabout babout">&gt;Donate</span></a></li>
							<li><a href="https://github.com/jwright159/OverseerV1"><span class="rhyme aabout babout">&gt;GitHub</span></a></li>
						</ul>
					</li>

					<li><a href="https://the-overseer.wikia.com/wiki/Main_Page"><span
								class="rhyme slam awiki">&gt;WIKI</span></a></li>

					<li><a href="about.php"><span class="rhyme slam afaq">&gt;FAQ</span></a></li>
				</ul>

			<?php } else { ?>
				<form id="dreamsequence" action="dreamtransition.php" method="post"><input type="hidden" name="sleep"
						value="sleep" /></form>
				<ul id="anav" class="adrop">
					<li><span class="rhyme slam aplayer">PLAYER</span>
						<ul>
							<li><a href="overview.php"><span class="rhyme aplayer bplayer">&gt;Info</span></a></li>
							<li class="dream"><span class="rhyme aplayer bplayer">&gt;Sleep?</span></li>
							<li><a href="echeviewer.php"><span class="rhyme aplayer bplayer">&gt;Echeladder</span></a></li>
							<li><a href="inventory.php"><span
										class="rhyme aplayer bplayer">&gt;Inventory/<wbr>Alchemy</span></a></li>
							<li><a href="storage.php"><span class="rhyme aplayer bplayer">&gt;Item Storage</span></a></li>
							<li><a href="atheneum.php"><span class="rhyme aplayer bplayer">&gt;Atheneum</span></a></li>
							<li><a href="sburbdevices.php"><span class="rhyme aplayer bplayer">&gt;SBURB Devices</span></a>
							</li>
							<li><a href="sburbserver.php"><span class="rhyme aplayer bplayer">&gt;SBURB Server</span></a>
							</li>
							<li><a href="sessioninfo.php"><span class="rhyme aplayer bplayer">&gt;View Session</span></a>
							</li>
							<li><a href="playersettings.php"><span class="rhyme aplayer bplayer">&gt;Player
										Settings</span></a></li>
						</ul>
					</li>

					<li><span class="rhyme slam astrife">STRIFE</span>
						<ul>
							<li><a href="strife.php"><span class="rhyme astrife bstrife">&gt;Strife!</span></a></li>
							<li><a href="dungeons.php"><span class="rhyme astrife bstrife">&gt;Dungeon Diving</span></a>
							</li>
							<li><a href="portfolio.php"><span class="rhyme astrife bstrife">&gt;Strife Portfolio</span></a>
							</li>
							<li><a href="echeviewer.php"><span class="rhyme astrife bstrife">&gt;Echeladder</span></a></li>
							<li><a href="consumables.php"><span class="rhyme astrife bstrife">&gt;Consumables</span></a>
							</li>
							<li><a href="wardrobe.php"><span class="rhyme astrife bstrife">&gt;Wardrobifier</span></a></li>
							<?php if (!empty($_SESSION['adjective'])) { ?>
								<li><a href="aspectpowers.php"><span class="rhyme astrife bstrife">&gt;DO THE
											<?php echo $_SESSION['adjective']; ?> THING
										</span></a></li>
								<li><a href="roletech.php"><span class="rhyme astrife bstrife">&gt;Roletechs</span></a></li>
							<?php } ?>
						</ul>
					</li>

					<li><span class="rhyme slam aexplore">EXPLORE</span>
						<ul>
							<li><a href="dungeons.php"><span class="rhyme aexplore bexplore">&gt;Dungeon Diving</span></a>
							</li>
							<?php if ($userrow['dreamingstatus'] !== "Awake") { ?>
								<li><a href="explore.php"><span class="rhyme aexplore bexplore">&gt;Explore Your
											Surroundings</span></a></li>
							<?php } else { ?>
								<li><a href="consortquests.php"><span class="rhyme aexplore bexplore">&gt;Go Questing</span></a>
								</li>
								<li><a href="mercenaries.php"><span class="rhyme aexplore bexplore">&gt;Followers</span></a>
								</li>
							<?php } ?>
							<li class="dream"><span class="rhyme aexplore bexplore">&gt;Sleep?</span></li>
						</ul>
					</li>

					<li><span class="rhyme slam ashop">SHOP</span>
						<ul>
							<li><a href="catalogue.php"><span class="rhyme ashop bshop">&gt;Item Catalogue</span></a></li>
							<li><a href="grist.php"><span class="rhyme ashop bshop">&gt;Gristwire</span></a></li>
							<li><a href="porkhollow.php"><span class="rhyme ashop bshop">&gt;Virtual Porkhollow</span></a>
							</li>
							<?php if ($userrow['dreamingstatus'] == "Awake") { ?>
								<li><a href="shop.php"><span class="rhyme ashop bshop">&gt;Consort Shops</span></a></li>
								<li><a href="gristexchange.php"><span class="rhyme ashop bshop">&gt;Stock Exchange</span></a>
								</li>
							<?php } ?>
							<li><a href="fraymotifs.php"><span class="rhyme ashop bshop">&gt;Fraymotifs</span></a></li>
							<li><a href="resets.php"><span class="rhyme ashop bshop">&gt;Resetter</span></a></li>
							<li><a href="rewards.php"><span class="rhyme ashop bshop">&gt;Rewards</span></a></li>
						</ul>
					</li>

					<?php if ($userrow['admin'] == 1) { ?>
						<li><a href="admin.php"><span class="rhyme slam aadministration">&gt;ADMIN</span></a></li>
					<?php } ?>
					<li><a href="messages.php"><span class="rhyme slam amessages">&gt;MESSAGES
								<?php
								$msgcount = $userrow['newmessage'];
								if ($msgcount != 0) {
									echo "($msgcount)";
								}
								?>
							</span></a></li>

					<li><span class="rhyme slam aabout">ABOUT</span>
						<ul>
							<li><a href="credits.php"><span class="rhyme aabout babout">&gt;Credits</span></a></li>
							<li><a href="changelog.php"><span class="rhyme aabout babout">&gt;Change Log</span></a></li>
							<li><a href="news.php"><span class="rhyme aabout babout">&gt;Items/<wbr>Art Updates</span></a></li>
							<li><a href="captchalist.php"><span class="rhyme aabout babout">&gt;Item List</span></a></li>
							<li><a href="randomizer.php"><span class="rhyme aabout babout">&gt;Random Combinations</span></a></li>
							<li><a href="feedback.php"><span class="rhyme aabout babout">&gt;Feedback/<wbr>Submit</span></a></li>
							<li><a href="submissions.php"><span class="rhyme aabout babout">&gt;Submissions</span></a></li>
							<li><a href="https://patreon.com/OverseerReboot"><span class="rhyme aabout babout">&gt;Donate</span></a></li>
							<li><a href="https://github.com/jwright159/OverseerV1"><span class="rhyme aabout babout">&gt;GitHub</span></a></li>
						</ul>
					</li>

					<li><a href="https://the-overseer.wikia.com/wiki/Main_Page"><span
								class="rhyme slam awiki">&gt;WIKI</span></a></li>

					<li><a href="about.php"><span class="rhyme slam afaq">&gt;FAQ</span></a></li>

					<li><a href="logout.php"><span class="rhyme slam alogin">&gt;LOGOUT</span></a></li>
				</ul>
			<?php } ?>
			<div id="canner">
