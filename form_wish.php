<?php session_start();
if (isset($_SESSION['USR_LOGIN'])=="") {
	header("Location:login.php");
}
include("mytcg/settings.php");
include("$header");

if(!$_SERVER['QUERY_STRING']) {
	$select = mysql_query("SELECT * FROM `$table_members` WHERE email='$_SESSION[USR_LOGIN]'");
	while($row=mysql_fetch_assoc($select)) {
		?>
		<h1>Make a Wish</h1>
<p>Make a wish &mdash; it just might come true! :)</p>
	
<h3>Guidelines</h3>
You can wish for just about anything, as long as it's not game breaking. Some ideas would be like:<br>
- A choice card for yourself or all members<br>
- Double deck releases<br>
- A particular deck to be released the following week<br>
- Game passes for yourself or all members<br>
- Double the number of cards members may take from new decks
<br><br>

Be creative! One wish will be granted each update!
<br><br>

		<h2>Make a wish</h2>
		<p>I save wishes for future use, so please do not resend the same wish. :)</p>
		<form method="post" action="4wish.php?thanks">
		<input type="hidden" name="id" value="<?php echo $row[id]; ?>" />
		<input type="hidden" name="name" value="<?php echo $row[name]; ?>" />
		<input type="hidden" name="email" value="<?php echo $row[email]; ?>" />
		<table width="100%">
		<tr><td>I wish for:</td><td><input type="text" size="60" name="wish" value="" /></td>
		<td>&nbsp;</td><td><input type="submit" name="submit" value=" Submit! " /></td></tr>
		</table>
		</form>
		<?php
	}
}

elseif($_SERVER['QUERY_STRING']=="thanks") {
	if (!isset($_POST['submit']) || $_SERVER['REQUEST_METHOD'] != "POST") {
		exit("<p>You did not press the submit button; this page should not be accessed directly.</p>");
	}
	else {
		$exploits = "/(content-type|bcc:|cc:|document.cookie|onclick|onload|javascript|alert)/i";
		$profanity = "/(beastial|bestial|blowjob|clit|cock|cum|cunilingus|cunillingus|cunnilingus|cunt|ejaculate|fag|felatio|fellatio|fuck|fuk|fuks|gangbang|gangbanged|gangbangs|hotsex|jism|jiz|kock|kondum|kum|kunilingus|orgasim|orgasims|orgasm|orgasms|phonesex|phuk|phuq|porn|pussies|pussy|spunk|xxx)/i";
		$spamwords = "/(viagra|phentermine|tramadol|adipex|advai|alprazolam|ambien|ambian|amoxicillin|antivert|blackjack|backgammon|texas|holdem|poker|carisoprodol|ciara|ciprofloxacin|debt|dating|porn)/i";
		$bots = "/(Indy|Blaiz|Java|libwww-perl|Python|OutfoxBot|User-Agent|PycURL|AlphaServer)/i";
		
		if (preg_match($bots, $_SERVER['HTTP_USER_AGENT'])) {
			exit("<h1>Error</h1>\nKnown spam bots are not allowed.<br /><br />");
			}
			foreach ($_POST as $key => $value) {
				$value = trim($value);
				if (empty($value)) {
					exit("<h1>Error</h1>\nEmpty fields are not allowed. Please go back and fill in the form properly.<br /><br />");
				}
				elseif (preg_match($exploits, $value)) {
					exit("<h1>Error</h1>\nExploits/malicious scripting attributes aren't allowed.<br /><br />");
				}
				elseif (preg_match($profanity, $value) || preg_match($spamwords, $value)) {
					exit("<h1>Error</h1>\nThat kind of language is not allowed through our form.<br /><br />");
				}
				
				$_POST[$key] = stripslashes(strip_tags($value));
			}
			
			if (!ereg("^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,6})$",strtolower($_POST['email']))) {
				exit("<h1>Error</h1>\nThat e-mail address is not valid, please use another.<br /><br />");
			}
			
			$recipient = "$tcgemail";
			$subject = "Make a Wish";
			
			$message = "The following member would like a member card: \n";
			$message .= "Name: {$_POST['name']} \n";
			$message .= "Email: {$_POST['email']} \n";
			$message .= "Wish: {$_POST['wish']} \n";
			
			$headers = "From: {$_POST['name']} <{$_POST['email']}> \n";
			$headers .= "Reply-To: <{$_POST['email']}>";
			
			if (mail($recipient,$subject,$message,$headers)) {
				?>
				<h1>Thanks!</h1>
				<p>You can take what you see below. :)</p>
				
				<p class="center"><?php 
$result=mysql_query("SELECT * FROM `$table_cards` WHERE `worth`='1'") or die("Unable to select from database.");
$min=1; 
$max=mysql_num_rows($result); 
for($i=0; $i<2; $i++) { 
mysql_data_seek($result,rand($min,$max)-1); 
$row=mysql_fetch_assoc($result); 
$digits = rand(01,$row['count']); 
if ($digits < 10) { $_digits = "0$digits"; } else { $_digits = $digits;} 
$card = "$row[filename]$_digits"; 
echo "<img src=\"$tcgcardurl$card.png\" border=\"0\" /> "; 
$rewards .= $card.", "; 
} 
$rewards = substr_replace($rewards,"",-2);
echo "<br /><b>Wish:</b> $rewards"; 
?></p>
				<?php
			}
			else {
				?>
				<h1>Error</h1>
				It looks like there was an error in processing your form. Send the information to $tcgemail and we will send you your member card ASAP. Thank you and sorry for the inconvenience.
				<?php
			}
	}
}
?>



<?php 
include "$footer"; ?>
