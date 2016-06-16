<?php
	define('PUN_ROOT', dirname(__FILE__).'/');

	require PUN_ROOT.'include/common.php';
	require PUN_ROOT.'include/paypal_conf.php';


	$action = isset($_GET['action']) ? $_GET['action'] : null;
	$uid = isset($_GET['uid']) ? intval(pun_trim($_GET['uid'])) : null;

	if ($action == 'donate') {
		$querystring = array();
		
		// Firstly Append paypal account to querystring.
		$querystring[] = "?business=" .urlencode($paypal_email). "&";

		// Make sure the CMD is actually donations.
		$querystring[] = "cmd=_donations". "&";

		// Append amount & currency ($) to querystring so it cannot be edited in html.
		$querystring[] = "currency_code=USD&";

		// The item name and amount can be brought in dynamically by querying the $_POST['item_number'] variable.
		$querystring[] = "item_name=" .urlencode($item_name). "&";
		$querystring[] = "item_number=" .urlencode($item_number). "&";

		if(!$pun_user['is_guest'] && $uid != 'anon') {
			$uid = $pun_user['id'];
		}
		
		// Handle the FluxBB User here.
		$querystring[] = "custom=" .$uid. "&";

		// Loop for posted values and append to querystring.
		foreach ($_POST as $key => $value) {
			$value = urlencode(stripslashes($value));
			$querystring[] = "$key=$value&";
		}

		// Append paypal return addresses.
		$querystring[] = "return=" .urlencode(stripslashes($return_url)). "&";
		$querystring[] = "cancel_return=" .urlencode(stripslashes($cancel_url)). "&";
		$querystring[] = "notify_url=" .urlencode($notify_url);

		// Redirect to paypal IPN.
		header('Location: https://' .$paypal_url. '/cgi-bin/webscr' .implode('', $querystring));
		exit;
	}

	define('PUN_ACTIVE_PAGE', 'index');
	$page_title = array(pun_htmlspecialchars($pun_config['o_board_title']), 'Donations');

	require PUN_ROOT.'header.php';

	echo $uid;
?>

<div id="rules" class="blockform">
	<div class="hd"><h2><span>RHQ -Gold Mine- Donation Page</span></h2></div>
	<div class="box" style="text-align: center; padding: 24px 0px 20px 0px;">
		<div style="font-size: 24px; font-weight: bold; color: yellow; margin-bottom: 24px;">
			RHQ -Gold Mine- Donation Page
		</div>

		<center>
		<?php if ($action == "success") { ?>
			<div style="width: 75%; font-size: 18px; border: 2px green solid; padding: 12px; margin-bottom: 16px;">
					<b>Congratulations!</b> You have successfully donated to the website! Keep on supporting it to keep it longer as possible.
			</div>
		<?php } ?>
		<?php if ($pun_user['is_guest'] || $uid == 'anon') { ?>
			<div style="width: 75%; font-size: 18px; border: 2px red solid; padding: 12px; margin-bottom: 16px;">
				<b>WARNING!</b> You are donating anonymously/as a guest, this will not save any amount you donated to the RHQ Database.
			</div>
		<?php } elseif (is_numeric($uid)) { ?>
			<div style="width: 75%; font-size: 18px; border: 2px red solid; padding: 12px; margin-bottom: 16px;">
				<b>WARNING!</b> You are donating for someone else, please make sure the User ID you're using is the correct one!
			</div>
		<?php } ?>

		<?php if ($pun_user['donated'] >= $item_amount && !$pun_user['is_guest']) { ?>
			<div style="width: 75%; font-size: 18px; border: 2px yellow solid; padding: 12px;">
				A super THANK YOU for supporting the RHQ Forums, your high gratitude will help the forums live 
				longer. As for your reward... have the silly badge, access to our YSBST Section and extra 
				neat little features mentioned below. You can donate more to increase your donation total!
				</br></br>
				You have donated $<?php echo $pun_user['donated']; ?> in total! Thank you!
			</div>
		<?php } elseif ($pun_user['donated'] >= '0.01' && !$pun_user['is_guest']) { ?>
			<div style="width: 75%; font-size: 18px; border: 2px red solid; padding: 12px;">
				Thank you for supporting a little, it means a lot for me. Each dollar can help RHQ grow
				bigger and better. If you wish to donate more, you can donate $<?php echo ($item_amount - $pun_user['donated']); ?> to earn
				your donator status.
			</div>
		<?php } ?>

			<div style="width: 50%; text-align: left; font-size: 17px; margin: 42px 0px 24px 0px;">
				Benefits of being a donator:
				<ul style="list-style: square inside; padding-left: 24px; margin-top: 12px;">
					<li> Nifty donator badge! <img src="img/awards/donator.png" title="SHINY...">
					<li> Private Access to the YSBST Section (exceptions may apply!).
					<li> 75% decreased "Wait Time" for Posting, Searching and Reporting!
					<li> Increased PM Message Limit to 500! (Member limit is 250)
					<li> Increased avatar limitation! (double filesize, 200 x 400 avatars)
					<li> Privilege to use the <?php echo pun_htmlspecialchars('<sage>'); ?> feature!
					<li> Access to change your Custom Title anytime!
					<li> <b>MY ETERNAL LOVE!!! (Ft. Systema)</b>
				</ul>
				</br>
				<b>You can receive this perk if you donate $10 USD or more (IN TOTAL, NOT AT ONCE).</b>
				</br></br>
				<hr>
			</div>

			<div style="width: 50%; text-align: left; font-size: 14px; margin: 0px 0px 24px 0px;">
				<span style="font-size: 24px;"><b>Top 5 Donators:</b></span>
					</br></br>
					<?php
						$result = $db->query('SELECT id, username, donated FROM '.$db->prefix.'users ORDER BY donated DESC LIMIT 5') or error('Unable to fetch users', __FILE__, __LINE__, $db->error());
						$position = 1;

						if ($db->num_rows($result)) {
							while ($cur_donator = $db->fetch_assoc($result)) {
								echo '<p>#'. $position .' <a href="profile.php?id='. $cur_donator['id'] .'">'. $cur_donator['username']. '</a> has donated <b>$'. $cur_donator['donated'] .'</b> in total.</p>';

								$position++;
							}
						}
					?>
					</br>
				<hr>
			</div>

			<div style="width: 50%; text-align: left; font-size: 14px;">
				<span style="font-size: 24px;"><b>REFUND POLICY</b></span>
				</br></br>
				Here at RHQ there will be <b>NO REFUNDS ALLOWED</b>. Any donation received cannot be reversed and/or refunded; think of what you're doing before donating.
				However, exceptions can be made within a week of donating. Attempting to do a reversal/refund without proper autorization will lead your account to be <b style="color: red;">PERMANENTLY BANNED</b>.
				Please also notice that this is <b>NOT A PURCHASE</b> for privileges but rather a perk for donating that amount of money <b>IN TOTAL</b>.
				</br></br>
				We also have the rights to expel your donation perk if there's any signs of abuse (in terms of the bump feature glitches, PM limits overflow, whatever; this also includes any future donator features) at any given time.
				<b>BE RESPONSIBLE WITH YOUR ACCOUNT AT ALL COSTS.</b>
				</br></br>
				Any kind of donation will help keep the website longer than the expected timeframe. This might also include for any other kind of servers we provide too.
				</br></br>
				<hr>
			</div>

			<form id="paypal_form" class="paypal" action="?action=donate" method="post">
				<input type="hidden" name="no_note" value="1" />
				<input type="hidden" name="lc" value="US" />
				<input type="hidden" name="bn" value="PP-DonationsBF:btn_donate_LG.gif:NonHostedGuest" />

				<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" name="submit" alt="PayPal - The safer, easier way to pay online!" />
				<img src="https://www.paypal.com/en_US/i/scr/pixel.gif" alt="" width="1" height="1" />
			</form>
		</center>
	</div>
</div>

<?php require PUN_ROOT.'footer.php'; ?>