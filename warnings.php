<?php

define('PUN_ROOT', dirname(__FILE__).'/');
require PUN_ROOT.'include/common.php';

// Load the warnings mod functions script
require PUN_ROOT.'include/warnings/warning_functions.php';

if (!$pun_config['o_warnings_enabled'])
	message($lang_warnings['Warning system disabled']);

// Load the warnings.php/post.php language files
require PUN_ROOT.'lang/'.$pun_user['language'].'/warnings.php';
require PUN_ROOT.'lang/'.$pun_user['language'].'/post.php';

// Check which private messaging system is available
$pm_pms = ($db->field_exists('messages', 'smileys') && file_exists(PUN_ROOT.'message_send.php')) ? 1 : 0;	// original Private Messaging System (by Connorhd & Smartys)
$pm_another = ($db->field_exists('messages', 'shared_id') && file_exists(PUN_ROOT.'pms_send.php')) ? 1 : 0;	// Another Private Messaging / Topic System (by adaur)
$pm_new = ($db->field_exists('pms_new_posts', 'topic_id') && file_exists(PUN_ROOT.'pmsnew.php')) ? 1 : 0;	// New Private Messaging System (by Visman)

$page_title = array(pun_htmlspecialchars($pun_config['o_board_title']), pun_htmlspecialchars($lang_warnings['Warning system']));
define('PUN_ACTIVE_PAGE', 'index');
require PUN_ROOT.'header.php';

$cur_index = 1;



if (isset($_POST['form_sent']))
{
	// Are we allowed to issue warnings?
	if (!($pun_user['g_id'] == PUN_ADMIN || ($pun_user['is_admmod'] && $pun_config['o_warnings_mod_add'] == '1')))
		message($lang_common['No permission']);

	// Check if there is such a user
	$user_id = intval($_POST['user_id']);
	$result = $db->query('SELECT username FROM '.$db->prefix.'users WHERE id='.$user_id) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
	if ($db->num_rows($result))
		$username = $db->result($result);
	else
		message($lang_common['Bad request']);

	// Check post ID
	$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
	if ($post_id < 0)
			message($lang_common['Bad request']);
	else if ($post_id > 0)
	{
		$result = $db->query('SELECT poster_id, message FROM '.$db->prefix.'posts WHERE id='.$post_id) or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
		list($poster_id, $note_post) = $db->fetch_row($result);

		if ($poster_id != $user_id || $note_post == '')
				message($lang_common['Bad request']);
	}
	else
	{
		$post_id = 0;
		$note_post = '';
	}

	// Check whether user has been warned already for this post (users can only receive one warning per post)
	if ($post_id)
	{
		$result = $db->query('SELECT id FROM '.$db->prefix.'warnings WHERE post_id='.$post_id) or error('Unable to fetch warnings info', __FILE__, __LINE__, $db->error());
		if ($db->num_rows($result))
		{
			$warning_id = $db->result($result);

			$warning_link = $pun_config['o_base_url'].'/warnings.php?details='.$warning_id;
			$warning_link = '<a href="warnings.php?details='.$warning_id.'">'.$warning_link.'</a>';

			message(sprintf($lang_warnings['Already warned'], $warning_link));
		}
	}

	// Check warning type
	if (!isset($_POST['warning_type']))
		message($lang_warnings['No warning type']);
	$warning_type = intval($_POST['warning_type']);

	// If not custom warning, check if warning type exists
	if ($warning_type != '0')
	{
		$result = $db->query('SELECT id, title, description, points, expiration_time FROM '.$db->prefix.'warning_types WHERE id='.$warning_type) or error('Unable to fetch warning type info', __FILE__, __LINE__, $db->error());
		if ($db->num_rows($result) != 1)
			message($lang_common['Bad request']);
	}

	// First make sure custom warnings are allowed.
	if ($warning_type == '0' && $pun_config['o_warnings_custom'] != '1')
		message($lang_warnings['Custom warnings disabled']);

	// Check custom warning title
	$custom_title = pun_trim($_POST['custom_title']);
	if ($warning_type == '0' && $custom_title == '')
		$errors[] = $lang_warnings['No warning reason'];
	else if ($warning_type == '0' && pun_strlen($custom_title) > 120)
		$errors[] = $lang_warnings['Too long warning reason'];

	// Check custom warning points
	$custom_points = isset($_POST['custom_points']) ? intval($_POST['custom_points']) : 0;
	if ($warning_type == '0' && $custom_points < 0)
		$errors[] = $lang_warnings['No points'];

	// Check custom warning expiration
	$custom_expiration_time = isset($_POST['custom_expiration_time']) ? intval($_POST['custom_expiration_time']) : 0;
	if ($warning_type == '0' && $custom_expiration_time < 1 && $_POST['custom_expiration_unit'] != 'never')
		$errors[] = $lang_warnings['No expiration time'];

	// Check admin note
	$note_admin = pun_linebreaks(pun_trim($_POST['note_admin'])); // Clean admin note from POST
	if (strlen($note_admin) > 65535)
		$errors[] = $lang_warnings['Too long admin note'];

	// If private messaging system is enabled
	if ($pun_config['o_pms_enabled'] == '1' && ($pm_pms || $pm_another || $pm_new))
	{
		// Determine warnings link
		$warnings_link = $pun_config['o_base_url'].'/warnings.php?view='.$user_id;
		$warnings_link = '[url]'.$warnings_link.'[/url]';

		// Get "warning type" title
		if ($warning_type == '0')
			$warning_title = $custom_title;
		else
		{
			$result = $db->query('SELECT title FROM '.$db->prefix.'warning_types WHERE id='.$warning_type) or error('Unable to fetch warning type title', __FILE__, __LINE__, $db->error());
			$warning_title = $db->result($result);
		}


		// Check message subject
		$subject = pun_trim($_POST['req_subject']);
		$subject = str_replace('<warning_type>', $warning_title, $subject);
		$subject = str_replace('<warnings_url>', $warnings_link, $subject);
		if ($subject == '')
			$errors[] = $lang_warnings['No subject'];
		else if (pun_strlen($subject) > 70)
			$errors[] = $lang_post['Too long subject'];

		//  Check message
		$message = pun_linebreaks(pun_trim($_POST['req_message'])); // Clean up message from POST
		$message = str_replace('<warning_type>', $warning_title, $message);
		$message = str_replace('<warnings_url>', $warnings_link, $message);
		if ($message == '')
			$errors[] = $lang_post['No message'];

		// Check note_pm
		$note_pm = 'Subject: '.$subject."\n\n".'Message:'."\n\n".$message;
		if (strlen($note_pm) > 65535)
			$errors[] = $lang_warnings['Too long message'];
	}
	else
		$note_pm = '';

	// If there are errors, we display them
	if (!empty($errors))
	{

	?>
	<div id="posterror" class="block">
		<h2><span><?php echo $lang_post['Post errors'] ?></span></h2>
		<div class="box">
			<div class="inbox">
				<p><?php echo $lang_warnings['Post errors info'] ?></p>
				<ul>
	<?php
	
		while (list(, $cur_error) = each($errors))
			echo "\t\t\t\t".'<li><strong>'.$cur_error.'</strong></li>'."\n";
	?>
				</ul>
				<p><a href="javascript: history.go(-1)"><?php echo $lang_common['Go back'] ?></a></p>
			</div>
		</div>
	</div>
	
	<?php
	
	}
	else
	{
		$now = time();

		// If it is a custom warning
		if ($warning_type == '0')
		{
			$warning_points = $custom_points;

			// Determine expiration time
			$expiration_time = get_expiration_time($_POST['custom_expiration_time'], $_POST['custom_expiration_unit']);

			if ($expiration_time == '0')
				$expiration_date = 0;
			else
				$expiration_date = $now + $expiration_time;

			//Insert warning
			$db->query('INSERT INTO '.$db->prefix.'warnings (user_id, type_id, post_id, title, points, date_issued, date_expire, issued_by, expired, note_admin, note_post, note_pm) VALUES('.$user_id.', '.$warning_type.', '.$post_id.', \''.$db->escape($custom_title).'\', '.$warning_points.', '.$now.', '.$expiration_date.', '.$pun_user['id'].', \'0\', \''.$db->escape($note_admin).'\', \''.$db->escape($note_post).'\', \''.$db->escape($note_pm).'\')') or error('Unable to insert warning', __FILE__, __LINE__, $db->error());
		}
		else
		{
			// Get warning info
			$result = $db->query('SELECT points, expiration_time FROM '.$db->prefix.'warning_types WHERE id='.$warning_type) or error('Unable to fetch warning points', __FILE__, __LINE__, $db->error());
			list($warning_points, $expiration_time) = $db->fetch_row($result);

			if ($expiration_time == '0')
				$expiration_date = 0;
			else
				$expiration_date = $now + $expiration_time;

			//Insert warning
			$db->query('INSERT INTO '.$db->prefix.'warnings (user_id, type_id, post_id, title, points, date_issued, date_expire, issued_by, expired, note_admin, note_post, note_pm) VALUES('.$user_id.', '.$warning_type.', '.$post_id.', \'\', '.$warning_points.', '.$now.', '.$expiration_date.', '.$pun_user['id'].', \'0\', \''.$db->escape($note_admin).'\', \''.$db->escape($note_post).'\', \''.$db->escape($note_pm).'\')') or error('Unable to insert warning', __FILE__, __LINE__, $db->error());
		}


		// If private messaging system is enabled
		if ($pun_config['o_pms_enabled'] == '1' && ($pm_pms || $pm_another || $pm_new))
		{
			// "Send" message if the original Private Messaging System (by Connorhd & Smartys) is installed
			if ($pm_pms)
			{
				// Should we send out a notification
				if ($pun_config['o_pms_email'] == '1')
				{
					// Get the post time of receiver's previous message received
					$result = $db->query('SELECT max(posted) as max_posted from '.$db->prefix.'messages WHERE owner='.$user_id.' AND status=0 LIMIT 1') or error('Unable to fetch pm info', __FILE__, __LINE__, $db->error());
					$previous_pm_time = $db->result($result);

					if (empty($previous_pm_time))
						$previous_pm_time = 0;

					// Check whether the user should be notified
					$result = $db->query('SELECT u.username, u.email, u.language FROM '.$db->prefix.'users AS u LEFT JOIN '.$db->prefix.'online AS o ON u.id=o.user_id WHERE u.id='.$user_id.' AND COALESCE(o.logged, u.last_visit)>'.$previous_pm_time.' AND u.id!='.intval($pun_user['id']).' AND u.email_pm=1') or error('Unable to fetch pm info', __FILE__, __LINE__, $db->error());

					if ($db->num_rows($result))
					{
						list($pms_username, $pms_email, $pms_language) = $db->fetch_row($result);

						require_once PUN_ROOT.'include/email.php';

						$notification_emails = array();

							if (file_exists(PUN_ROOT.'lang/'.$pms_language.'/mail_templates/new_message.tpl'))
							{
								// Load the "new message" template
								$mail_tpl = trim(file_get_contents(PUN_ROOT.'lang/'.$pms_language.'/mail_templates/new_message.tpl'));

								// The first row contains the subject (it also starts with "Subject:")
								$first_crlf = strpos($mail_tpl, "\n");
								$mail_subject = trim(substr($mail_tpl, 8, $first_crlf-8));
								$mail_message = trim(substr($mail_tpl, $first_crlf));

								$mail_subject = str_replace('<board_title>', $pun_config['o_board_title'], $mail_subject);
								$mail_message = str_replace('<pm_receiver>', $pms_username, $mail_message);
								$mail_message = str_replace('<board_title>', $pun_config['o_board_title'], $mail_message);
								$mail_message = str_replace('<pm_sender>', $pun_user['username'], $mail_message);
								$mail_message = str_replace('<pm_title>', $subject, $mail_message);
								$mail_message = str_replace('<pm_url>', $pun_config['o_base_url'].'/message_list.php', $mail_message);
								$mail_message = str_replace('<disable_email_pm_url>', $pun_config['o_base_url'].'/message_list.php?email_pm=0', $mail_message);
								$mail_message = str_replace('<board_mailer>', $pun_config['o_board_title'], $mail_message);

								$notification_emails[$pms_language][0] = $mail_subject;
								$notification_emails[$pms_language][1] = $mail_message;

								$mail_subject = $mail_message = $mail_subject_full = $mail_message_full = null;
							}

							// We have to double check here because the templates could be missing
							if (isset($notification_emails[$pms_language]))
								pun_mail($pms_email, $notification_emails[$pms_language][0], $notification_emails[$pms_language][1]);
					}
				}

				$db->query('INSERT INTO '.$db->prefix.'messages (owner, subject, message, sender, sender_id, sender_ip, smileys, showed, status, posted) VALUES(
					'.$user_id.',
					\''.$db->escape($subject).'\',
					\''.$db->escape($message).'\',
					\''.$db->escape($pun_user['username']).'\',
					'.$pun_user['id'].',
					\''.get_remote_address().'\',
					\'1\',
					\'0\',
					\'0\',
					\''.$now.'\'
				)') or error('Unable to send message', __FILE__, __LINE__, $db->error());
			}
			// "Send" message if Another Private Messaging / Topic System (by adaur) is installed
			else if ($pm_another)
			{
				// Fetch some user info
				$result = $db->query('SELECT username, email, notify_pm FROM '.$db->prefix.'users WHERE id='.$user_id) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
				if (!$db->num_rows($result))
					message($lang_common['Bad request']);

				list($user_username, $user_email, $user_notify_pm) = $db->fetch_row($result);

				// Build IDs' & usernames' list : the end
				$list_ids = array();
				$list_ids[] = $user_id;
				$list_ids[] = $pun_user['id'];
				$list_usernames = array();
				$list_usernames[] = $user_username;
				$list_usernames[] = $pun_user['username'];
				$ids_list = implode(', ', $list_ids);
				$usernames_list = implode(', ', $list_usernames);

				$result_shared = $db->query('SELECT last_shared_id FROM '.$db->prefix.'messages ORDER BY last_shared_id DESC LIMIT 1') or error('Unable to fetch last_shared_id', __FILE__, __LINE__, $db->error());
				if (!$db->num_rows($result_shared))
					$shared_id = '1';
				else
				{
					$shared_result = $db->result($result_shared);
					$shared_id = $shared_result + 1;
				}

				$val_showed = ($user_id == $pun_user['id']) ? '1' : '0';

				$db->query('INSERT INTO '.$db->prefix.'messages (shared_id, last_shared_id, owner, subject, message, sender, receiver, sender_id, receiver_id, sender_ip, hide_smilies, posted, show_message, showed) VALUES(\''.$shared_id.'\', \''.$shared_id.'\', \''.$user_id.'\', \''.$db->escape($subject).'\', \''.$db->escape($message).'\', \''.$db->escape($pun_user['username']).'\', \''.$db->escape($usernames_list).'\', \''.$pun_user['id'].'\', \''.$db->escape($ids_list).'\', \''.get_remote_address().'\', \'1\',  \''.$now.'\', \'1\', \''.$val_showed.'\')') or error('Unable to send the message.', __FILE__, __LINE__, $db->error());
				$new_mp = $db->insert_id();
				$db->query('UPDATE '.$db->prefix.'messages SET last_post_id='.$new_mp.', last_post='.$now.', last_poster=\''.$db->escape($pun_user['username']).'\' WHERE shared_id='.$shared_id.' AND show_message=1 AND owner='.$user_id) or error('Unable to update the message.', __FILE__, __LINE__, $db->error());
				$db->query('UPDATE '.$db->prefix.'users SET num_pms=num_pms+1 WHERE id='.$user_id) or error('Unable to update user', __FILE__, __LINE__, $db->error());
				// E-mail notification
				if ($pun_config['o_pms_notification'] == '1' && $user_notify_pm == '1' && $user_id != $pun_user['id'])
				{
					require_once PUN_ROOT.'include/email.php';

					// Load the "new_pm" template
					if (file_exists(PUN_ROOT.'lang/'.$pun_user['language'].'/mail_templates/new_pm.tpl'))
						$mail_tpl = trim(file_get_contents(PUN_ROOT.'lang/'.$pun_user['language'].'/mail_templates/new_pm.tpl'));
					else
						$mail_tpl = trim(file_get_contents(PUN_ROOT.'lang/English/mail_templates/new_pm.tpl'));

					// The first row contains the subject
					$first_crlf = strpos($mail_tpl, "\n");
					$mail_subject = trim(substr($mail_tpl, 8, $first_crlf-8));
					$mail_message = trim(substr($mail_tpl, $first_crlf));

					$mail_subject = str_replace('<board_title>', $pun_config['o_board_title'], $mail_subject);
					$mail_message = str_replace('<sender>', $pun_user['username'], $mail_message);
					$mail_message = str_replace('<board_mailer>', $pun_config['o_board_title'].' '.$lang_common['Mailer'], $mail_message);

					$mail_message = str_replace('<pm_url>', $pun_config['o_base_url'].'/pms_view.php?tid='.$shared_id.'&mid='.$new_mp.'&box=inbox', $mail_message);
					pun_mail($user_email, $mail_subject, $mail_message);
				}

				$db->query('UPDATE '.$db->prefix.'users SET last_post='.$now.' WHERE id='.$pun_user['id']) or error('Unable to update user', __FILE__, __LINE__, $db->error());
			}
			// "Send" message if New Private Messaging System (by Visman) is installed
			else if ($pm_new)
			{
				require PUN_ROOT.'include/pms_new/common_pmsn.php';

				// Fetch some user info
				$result = $db->query('SELECT username, email, language, messages_email FROM '.$db->prefix.'users WHERE id='.$user_id) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
				if (!$db->num_rows($result))
					message($lang_common['Bad request']);

				list($user_username, $user_email, $user_language, $user_messages_email) = $db->fetch_row($result);

				// Create a new topic [translated]
				$db->query('INSERT INTO '.$db->prefix.'pms_new_topics (topic, starter, starter_id, to_user, to_id, replies, last_posted, last_poster, see_st, see_to, topic_st, topic_to) VALUES(\''.$db->escape($subject).'\', \''.$db->escape($pun_user['username']).'\', '.$pun_user['id'].', \''.$db->escape($user_username).'\', '.$user_id.', 0, '.$now.', 0, '.$now.', 0, '.'\'0\''.', '.'\'1\''.')') or error('Unable to create pms_new_topics', __FILE__, __LINE__, $db->error());
				$new_tid = $db->insert_id();

				// Create a new message [translated]
				$db->query('INSERT INTO '.$db->prefix.'pms_new_posts (poster, poster_id, poster_ip, message, hide_smilies, posted, post_seen, post_new, topic_id) VALUES(\''.$db->escape($pun_user['username']).'\', '.$pun_user['id'].', \''.$db->escape(get_remote_address()).'\',  \''.$db->escape($message).'\', '.'\'1\''.', '.$now.', 0, 1, '.$new_tid.')') or error('Unable to create pms_new_posts', __FILE__, __LINE__, $db->error());
				$new_pid = $db->insert_id();

				// Update users
				$db->query('UPDATE '.$db->prefix.'users SET messages_all=messages_all+1, pmsn_last_post='.$now.' WHERE id='.$pun_user['id']) or error('Unable to update user', __FILE__, __LINE__, $db->error());

				// Update information from the recipient [translated]
				pmsn_user_update($user_id, true);

				if ($user_messages_email == 1)
				{
					if (file_exists(PUN_ROOT.'lang/'.$user_language.'/mail_templates/form_pmsn.tpl'))
						$mail_tpl = trim(file_get_contents(PUN_ROOT.'lang/'.$user_language.'/mail_templates/form_pmsn.tpl'));
					else
						$mail_tpl = trim(file_get_contents(PUN_ROOT.'lang/English/mail_templates/form_pmsn.tpl'));

					$first_crlf = strpos($mail_tpl, "\n");
					$mail_subject = pun_trim(substr($mail_tpl, 8, $first_crlf-8));
					$mail_message = pun_trim(substr($mail_tpl, $first_crlf));

					$mail_subject = str_replace('<mail_subject>', $subject, $mail_subject);
					$mail_message = str_replace('<sender>', $pun_user['username'], $mail_message);
					$mail_message = str_replace('<user>', $user_username, $mail_message);
					$mail_message = str_replace('<board_title>', $pun_config['o_board_title'], $mail_message);
					$mail_message = str_replace('<board_mailer>', $pun_config['o_board_title'], $mail_message);
					$mail_message = str_replace('<message_url>', get_base_url().'/pmsnew.php'.( $new_pid ? '?mdl=topic&pid='.$new_pid.'#p'.$new_pid : ''), $mail_message);

					require_once PUN_ROOT.'include/email.php';

					pun_mail($user_email, $mail_subject, $mail_message); // , $pun_user['email'], $pun_user['username']);
				}
			}
		}


		// Check whether user should be banned according to warning levels
		if ($warning_points > 0)
		{
			// First get user's total points of active warnings
			$result = $db->query('SELECT SUM(points) FROM '.$db->prefix.'warnings WHERE user_id='.$user_id.' AND (date_expire > '.$now.' OR date_expire=0)') or error('Unable to count active warnings', __FILE__, __LINE__, $db->error());
			$points_active = $db->result($result);

			$result = $db->query('SELECT message, period FROM '.$db->prefix.'warning_levels WHERE points <= '.$points_active.' ORDER BY points DESC LIMIT 1') or error('Unable to fetch warning levels info', __FILE__, __LINE__, $db->error());
			if ($db->num_rows($result))
			{
				list($ban_message, $ban_period) = $db->fetch_row($result);

				// Check whether user is already banned
				$result = $db->query('SELECT expire FROM '.$db->prefix.'bans WHERE username=\''.$db->escape($username).'\' ORDER BY expire IS NULL DESC, expire DESC LIMIT 1') or error('Unable to fetch ban info', __FILE__, __LINE__, $db->error());
				if ($db->num_rows($result))
				{
					$ban_current_expire = $db->result($result);

					// Only delete user's current bans if new ban is greater than curent ban and current ban is not a permanent ban
					if ((($now + $ban_period) > $ban_current_expire || $ban_period == '0') && $ban_current_expire != null)
					{
						$db->query('DELETE FROM '.$db->prefix.'bans WHERE username=\''.$db->escape($username).'\'') or error('Unable to delete user\'s bans', __FILE__, __LINE__, $db->error());

						// Insert ban
						if ($ban_period == '0')
							$db->query('INSERT INTO '.$db->prefix.'bans (username, message) VALUES(\''.$db->escape($username).'\', \''.$db->escape($ban_message).'\')') or error('Unable to insert ban', __FILE__, __LINE__, $db->error());
						else
							$db->query('INSERT INTO '.$db->prefix.'bans (username, message, expire) VALUES(\''.$db->escape($username).'\', \''.$db->escape($ban_message).'\', '.($now + $ban_period).')') or error('Unable to insert ban', __FILE__, __LINE__, $db->error());
					}
				}
				else
				{
					// Insert ban
					if ($ban_period == '0')
						$db->query('INSERT INTO '.$db->prefix.'bans (username, message) VALUES(\''.$db->escape($username).'\', \''.$db->escape($ban_message).'\')') or error('Unable to insert ban', __FILE__, __LINE__, $db->error());
					else
						$db->query('INSERT INTO '.$db->prefix.'bans (username, message, expire) VALUES(\''.$db->escape($username).'\', \''.$db->escape($ban_message).'\', '.($now + $ban_period).')') or error('Unable to insert ban', __FILE__, __LINE__, $db->error());
				}

				// Regenerate the bans cache
				require_once PUN_ROOT.'include/cache.php';
				generate_bans_cache();
			}
		}


		// Redirect back to where we came from
		if ($post_id)
			redirect('viewtopic.php?pid='.$post_id.'#p'.$post_id, $lang_warnings['Warning added redirect']);
		else
			redirect('profile.php?id='.$user_id, $lang_warnings['Warning added redirect']);
	}
}

else if (isset($_GET['warn']))
{
	// Are we allowed to issue warnings?
	if (!($pun_user['g_id'] == PUN_ADMIN || ($pun_user['is_admmod'] && $pun_config['o_warnings_mod_add'] == '1')))
		message($lang_common['No permission']);

	$user_id = isset($_GET['warn']) ? intval($_GET['warn']) : 0;
	if ($user_id < 1)
		message($lang_common['Bad request']);

	$post_id = isset($_GET['pid']) ? intval($_GET['pid']) : 0;
	if ($post_id < 0)
		message($lang_common['Bad request']);

	// Check whether user has been warned already for this post (users can only receive one warning per post)
	if ($post_id)
	{
		$result = $db->query('SELECT id FROM '.$db->prefix.'warnings WHERE post_id='.$post_id) or error('Unable to fetch warnings info', __FILE__, __LINE__, $db->error());
		if ($db->num_rows($result))
		{
			$warning_id = $db->result($result);

			$warning_link = $pun_config['o_base_url'].'/warnings.php?details='.$warning_id;
			$warning_link = '<a href="warnings.php?details='.$warning_id.'">'.$warning_link.'</a>';

			message(sprintf($lang_warnings['Already warned'], $warning_link));
		}
	}

	// Get the username of the user who is receiving the warning
	$result = $db->query('SELECT username FROM '.$db->prefix.'users WHERE id='.$user_id) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
	$username = $db->result($result);

	// Collect some stats
	$now = time();

	// Count user's active warnings
	$result = $db->query('SELECT COUNT(id) FROM '.$db->prefix.'warnings WHERE user_id='.$user_id.' AND (date_expire > '.$now.' OR date_expire=0)') or error('Unable to count active warnings', __FILE__, __LINE__, $db->error());
	$num_active = $db->result($result);
	
	// Count user's expired warnings
	$result = $db->query('SELECT COUNT(id) FROM '.$db->prefix.'warnings WHERE user_id='.$user_id.' AND date_expire <= '.$now.' AND date_expire!=0') or error('Unable to count expired warnings', __FILE__, __LINE__, $db->error());
	$num_expired = $db->result($result);
	
	// Total points of active warnings
	$result = $db->query('SELECT SUM(points) FROM '.$db->prefix.'warnings WHERE user_id='.$user_id.' AND (date_expire > '.$now.' OR date_expire=0)') or error('Unable to count active warnings', __FILE__, __LINE__, $db->error());
	$points_active = $db->result($result);
	
	// Total points of expired warnings
	$result = $db->query('SELECT SUM(points) FROM '.$db->prefix.'warnings WHERE user_id='.$user_id.' AND date_expire <= '.$now.' AND date_expire!=0') or error('Unable to count expired warnings', __FILE__, __LINE__, $db->error());
	$points_expired = $db->result($result);

?>

<div class="blockform">
	<h2><span><?php echo $lang_warnings['Issue warning'] ?></span></h2>
	<div class="box">
	<form method="post" id="post" action="warnings.php?action=send" onsubmit="return process_form(this)">


		<div class="inform">
			<fieldset>
				<legend><?php echo $lang_warnings['User details'] ?></legend>
				<div class="infldset">
					<p><?php echo $lang_warnings['Username'	] ?>: <a href="profile.php?id=<?php echo $user_id ?>"><?php echo $username ?></a></p>
<?php
/*
//Take this part out for now: don't want the moderator to be influenced by the user's previous warnings when issuing the current warning.
					<p>Active warnings: <?php echo $num_active ?> (<?php echo $points_active ?> points)</p>
					<p>Expired warnings: <?php echo $num_expired?> (<?php echo $points_expired ?> points)</p>
*/
?>
				</div>
			</fieldset>
		</div>

		<div class="inform">
		<fieldset>
			<legend><?php echo $lang_warnings['Enter warning details'] ?></legend>
			<div class="infldset txtarea">
				<input type="hidden" name="form_sent" value="1" />
				<input type="hidden" name="user_id" value="<?php echo $user_id ?>" />
				<input type="hidden" name="post_id" value="<?php echo $post_id ?>" />
				<input type="hidden" name="topic_redirect" value="<?php echo isset($_GET['tid']) ? $_GET['tid'] : '' ?>" />
				<input type="hidden" name="topic_redirect" value="<?php echo isset($_POST['from_profile']) ? $_POST['from_profile'] : '' ?>" />
				<input type="hidden" name="form_user" value="<?php echo (!$pun_user['is_guest']) ? pun_htmlspecialchars($pun_user['username']) : 'Guest'; ?>" />
				
				<label><strong><?php echo $lang_warnings['Warning type'] ?></strong><br /></label>
<?php
$result = $db->query('SELECT id, title, points, expiration_time FROM '.$db->prefix.'warning_types ORDER BY points') or error('Unable to fetch warning types list', __FILE__, __LINE__, $db->error());

$warning_count = 1;
$warning_checked = '';

// If there are topics in this forum.
if ($db->num_rows($result))
{
	while ($cur_warning = $db->fetch_assoc($result))
	{
		//if ($warning_count == '1')
		//	$warning_checked = ' checked="checked"';

		echo "\n\t\t\t\t".'<input id="wt'.$warning_count++.'" type="radio" name="warning_type" value="'.$cur_warning['id'].'"'.$warning_checked.' tabindex="'.$cur_index++.'" /> '.$cur_warning['title'].' ('.sprintf($lang_warnings['No of points'], $cur_warning['points']).' / '.sprintf($lang_warnings['Expires after period'], format_expiration_time($cur_warning['expiration_time'])).')<br />';
	}
}
else
	echo "\n\t\t\t\t".$lang_warnings['No warning types'].'<br />';


if ($pun_config['o_warnings_custom'] == '1')
{

$warning_unit = '
							<select name="custom_expiration_unit">
								<option value="hours">'.$lang_warnings['Hours'].'</option>
								<option value="days" selected="selected">'.$lang_warnings['Days'].'</option>
								<option value="months">'.$lang_warnings['Months'].'</option>
								<option value="never">'.$lang_warnings['Never'].'</option>
							</select>';


	echo "\n\t\t\t\t".'<label><strong>'.$lang_warnings['Custom warning type'].'</strong><br /></label>';

	echo "\n\t\t\t\t".'<input id="wt'.$warning_count++.'" type="radio" name="warning_type" value="0" tabindex="'.$cur_index++.'" /> '.'<input type="text" name="custom_title" size="20" maxlength="120" tabindex="'.$cur_index++.'" />'.' ('.sprintf($lang_warnings['No of points'], '<input type="text" name="custom_points" size="3" maxlength="3" tabindex="'.$cur_index++.'" />').' / '.sprintf($lang_warnings['Expires after period'], '<input type="text" name="custom_expiration_time" size="3" maxlength="3" value="10" tabindex="'.$cur_index++.'" />'.' '.$warning_unit).')<br />';

}

?>

				<label><strong><?php echo $lang_warnings['Admin note'] ?></strong><br /><textarea name="note_admin" rows="5" cols="60" tabindex="<?php echo $cur_index++ ?>"></textarea><br /></label>
			</div>
		</fieldset>
		</div>

<?php
if ($pun_config['o_pms_enabled'] == '1' && ($pm_pms || $pm_another || $pm_new))
{
?>
		<div class="inform">
		<fieldset>
			<legend><?php echo $lang_warnings['Enter private message'] ?></legend>
			<div class="infldset txtarea">
<?php
// Load the "warning pm" template
$pm_tpl = trim(file_get_contents(PUN_ROOT.'lang/'.$pun_user['language'].'/mail_templates/warning_pm.tpl'));

// The first row contains the subject
$first_crlf = strpos($pm_tpl, "\n");
$pm_subject = trim(substr($pm_tpl, 8, $first_crlf-8));
$pm_message = trim(substr($pm_tpl, $first_crlf));
$pm_message = str_replace('<username>', $username, $pm_message);
$pm_message = str_replace('<board_title>', $pun_config['o_board_title'], $pm_message);

?>
				<label><strong><?php echo $lang_warnings['Subject'] ?></strong><br /><input class="longinput" type="text" name="req_subject" value="<?php echo $pm_subject ?>" size="80" maxlength="70" tabindex="<?php echo $cur_index++ ?>" /><br /></label>
				<label><strong><?php echo $lang_warnings['Message'] ?></strong><br /><textarea name="req_message" rows="10" cols="95" tabindex="<?php echo $cur_index++ ?>"><?php echo $pm_message ?></textarea><br /></label>
			</div>
		</fieldset>
		</div>
<?php
}
?>

			<p class="buttons"><input type="submit" name="submit" value="<?php echo $lang_common['Submit'] ?>" tabindex="<?php echo $cur_index++ ?>" accesskey="s" /> <a href="javascript:history.go(-1)"><?php echo $lang_common['Go back'] ?></a></p>
		</form>
	</div>
</div>

<?php
}

else if (isset($_GET['view']))
{
	$user_id = isset($_GET['view']) ? intval($_GET['view']) : 0;
	if ($user_id < 1)
		message($lang_common['Bad request']);

	// Normal users can only view their own warnings - and only if they have permission
	if ($pun_user['g_id'] == PUN_GUEST || (!$pun_user['is_admmod'] && $pun_user['id'] != $user_id) || (!$pun_user['is_admmod'] && $pun_config['o_warnings_see_status'] == 'mods'))
		message($lang_common['No permission']);

	// Get the username of the user
	$result = $db->query('SELECT username FROM '.$db->prefix.'users WHERE id='.$user_id) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
	$username = $db->result($result);

	$now = time();

	// Collect some stats

	// Count user's active warnings
	$result = $db->query('SELECT COUNT(id) FROM '.$db->prefix.'warnings WHERE user_id='.$user_id.' AND (date_expire > '.$now.' OR date_expire=0)') or error('Unable to count active warnings', __FILE__, __LINE__, $db->error());
	$num_active = $db->result($result);

	// Count user's expired warnings
	$result = $db->query('SELECT COUNT(id) FROM '.$db->prefix.'warnings WHERE user_id='.$user_id.' AND date_expire <= '.$now.' AND date_expire!=0') or error('Unable to count expired warnings', __FILE__, __LINE__, $db->error());
	$num_expired = $db->result($result);

	// Total points of active warnings
	$result = $db->query('SELECT SUM(points) FROM '.$db->prefix.'warnings WHERE user_id='.$user_id.' AND (date_expire > '.$now.' OR date_expire=0)') or error('Unable to count active warnings', __FILE__, __LINE__, $db->error());
	$points_active = $db->result($result);

	// Total points of expired warnings
	$result = $db->query('SELECT SUM(points) FROM '.$db->prefix.'warnings WHERE user_id='.$user_id.' AND date_expire <= '.$now.' AND date_expire!=0') or error('Unable to count expired warnings', __FILE__, __LINE__, $db->error());
	$points_expired = $db->result($result);


?>

<div class="linkst">
	<div class="inbox crumbsplus">
		<ul class="crumbs">
			<li><a href="profile.php?id=<?php echo $user_id ?>"><?php echo $username ?></a></li>
			<li><span>»&#160;</span><a href="warnings.php?view=<?php echo $user_id ?>"><strong><?php echo pun_htmlspecialchars($lang_warnings['Warnings']) ?></strong></a></li>
		</ul>
		<div class="clearer"></div>
	</div>
</div>

<div id="warnings_active" class="blocktable">

	<h2><span><?php echo $username ?> - <?php echo $lang_warnings['Active warnings'] ?></span></h2>
	<div class="box">
		<div class="inbox">
		<table cellspacing="0">
		<thead>
			<tr>
				<th style="width: 35%" align="left" scope="col"><?php echo $lang_warnings['Warning'] ?></th>
				<th style="width: 18%" align="left" scope="col"><?php echo $lang_warnings['Date issued'] ?></th>
				<th class="tc3" scope="col"><?php echo $lang_warnings['Points'] ?></th>
				<th style="width: 18%" align="left" scope="col"><?php echo $lang_warnings['Expires'] ?></th>
				<th class="tcr" scope="col"><?php echo $lang_warnings['Issued by'] ?></th>
				<th class="tc3" scope="col"><?php echo $lang_warnings['Details'] ?></th>
			</tr>
		</thead>
		<tbody>
<?php

// If there are active warnings
$result = $db->query('SELECT w.id, w.type_id, w.post_id, w.title AS custom_title, w.points, w.date_issued, w.date_expire, w.issued_by, t.title, u.username AS issued_by_username FROM '.$db->prefix.'warnings as w LEFT join '.$db->prefix.'warning_types AS t ON t.id=w.type_id LEFT JOIN '.$db->prefix.'users AS u ON u.id=w.issued_by WHERE w.user_id='.$user_id.' AND (w.date_expire > '.$now.' OR w.date_expire=0) ORDER BY w.date_issued DESC') or error('Unable to fetch active warnings', __FILE__, __LINE__, $db->error());
if ($db->num_rows($result))
{
	while ($warnings_active = $db->fetch_assoc($result))
	{
		// Determine warning type
		if ($warnings_active['custom_title'] != '')
			$warning_title = $lang_warnings['Custom warning'].': '.$warnings_active['custom_title'];
		else if ($warnings_active['title'] != '')
			//$warning_title = '<a href="jaap.php">'.$warnings_active['title'].'</a>';
			$warning_title = $warnings_active['title'];
		else
			$warning_title = ''; // Means the warning type has been deleted by the administrator
?>
				<tr>
					<td style="width: 35%" align="left"><?php echo $warning_title ?></td>
					<td style="width: 18%" align="left"><?php echo format_time($warnings_active['date_issued']) ?></td>
					<td class="tc3"><?php echo $warnings_active['points'] ?></td>
					<td style="width: 18%" align="left"><?php echo ($warnings_active['date_expire'] == '0') ? 'Never' : format_time($warnings_active['date_expire']) ?></td>
					<td class="tcr"><?php echo ($warnings_active['issued_by_username'] != '') ? '<a href="profile.php?id='.$warnings_active['issued_by'].'">'.$warnings_active['issued_by_username'].'</a>' : '' ?></td>
					<td class="tc3"><a href="warnings.php?details=<?php echo  $warnings_active['id'] ?>"><?php echo $lang_warnings['Details'] ?></a></td>
				</tr>
<?php
	}
?>
				<tr>
					<th align="left" scope="col"><?php printf($lang_warnings['No of warnings'], $num_active) ?></th>
					<th align="left" scope="col">&nbsp;</th>
					<th class="tc3" scope="col"><?php echo $points_active ?></th>
					<th align="left" colspan="3" scope="col">&nbsp;</th>
				</tr>
<?php
}
else
{
?>
				<tr>
					<td class="tcl" colspan="6"><?php echo $lang_warnings['No active warnings'] ?></td>
				</tr>
<?php
}
?>

			</tbody>
			</table>
		</div>
	</div>
</div>


<div id="warnings_expired" class="blocktable">

	<h2><span><?php echo $username ?> - <?php echo $lang_warnings['Expired warnings'] ?></span></h2>
	<div class="box">
		<div class="inbox">
		<table cellspacing="0">
		<thead>
			<tr>
				<th style="width: 35%" align="left" scope="col"><?php echo $lang_warnings['Warning'] ?></th>
				<th style="width: 18%" align="left" scope="col"><?php echo $lang_warnings['Date issued'] ?></th>
				<th class="tc3" scope="col"><?php echo $lang_warnings['Points'] ?></th>
				<th style="width: 18%" align="left" scope="col"><?php echo $lang_warnings['Expired'] ?></th>
				<th class="tcr" scope="col"><?php echo $lang_warnings['Issued by'] ?></th>
				<th class="tc3" scope="col"><?php echo $lang_warnings['Details'] ?></th>
			</tr>
		</thead>
		<tbody>
<?php

// If there are expired warnings
$result = $db->query('SELECT w.id, w.type_id, w.post_id, w.title AS custom_title, w.points, w.date_issued, w.date_expire, w.issued_by, t.title, u.username AS issued_by_username FROM '.$db->prefix.'warnings as w LEFT join '.$db->prefix.'warning_types AS t ON t.id=w.type_id LEFT JOIN '.$db->prefix.'users AS u ON u.id=w.issued_by WHERE w.user_id='.$user_id.' AND w.date_expire <= '.$now.' AND w.date_expire!=0 ORDER BY w.date_issued DESC') or error('Unable to fetch active warnings', __FILE__, __LINE__, $db->error());
if ($db->num_rows($result))
{
	while ($warnings_expired = $db->fetch_assoc($result))
	{
		// Determine warning type
		if ($warnings_expired['custom_title'] != '')
			$warning_title = $lang_warnings['Custom warning'].': '.$warnings_expired['custom_title'];
		else if ($warnings_expired['title'] != '')
			//$warning_title = '<a href="jaap.php">'.$warnings_expired['title'].'</a>';
			$warning_title = $warnings_expired['title'];
		else
			$warning_title = ''; // Means the warning type has been deleted by the administrator
?>
				<tr>
					<td style="width: 35%" align="left"><?php echo $warning_title ?></td>
					<td style="width: 18%" align="left"><?php echo format_time($warnings_expired['date_issued']) ?></td>
					<td class="tc3"><?php echo $warnings_expired['points'] ?></td>
					<td style="width: 18%" align="left"><?php echo format_time($warnings_expired['date_expire']) ?></td>
					<td class="tcr"><?php echo ($warnings_expired['issued_by_username'] != '') ? '<a href="profile.php?id='.$warnings_expired['issued_by'].'">'.$warnings_expired['issued_by_username'].'</a>' : '' ?></td>
					<td class="tc3"><a href="warnings.php?details=<?php echo  $warnings_expired['id'] ?>"><?php echo $lang_warnings['Details'] ?></a></td>
				</tr>
<?php
	}
?>
				<tr>
					<th align="left" scope="col"><?php printf($lang_warnings['No of warnings'], $num_expired) ?></th>
					<th align="left" scope="col">&nbsp;</th>
					<th class="tc3" scope="col"><?php echo $points_expired ?></th>
					<th align="left" colspan="3" scope="col">&nbsp;</th>
				</tr>
<?php
}
else
{
?>
				<tr>
					<td class="tcl" colspan="6"><?php echo $lang_warnings['No expired warnings'] ?></td>
				</tr>
<?php
}
?>

			</tbody>
			</table>
		</div>
	</div>
</div>

<div class="linksb">
	<div class="inbox crumbsplus">
		<ul class="crumbs">
			<li><a href="profile.php?id=<?php echo $user_id ?>"><?php echo $username ?></a></li>
			<li><span>»&#160;</span><a href="warnings.php?view=<?php echo $user_id ?>"><strong><?php echo pun_htmlspecialchars($lang_warnings['Warnings']) ?></strong></a></li>
		</ul>
		<div class="clearer"></div>
	</div>
</div>

<?php
}

else if (isset($_GET['details']))
{
	$warning_id = isset($_GET['details']) ? intval($_GET['details']) : 0;
	if ($warning_id < 1)
		message($lang_common['Bad request']);

	// Get the warning details
	$result = $db->query('SELECT w.id, w.user_id, w.type_id, w.post_id, w.title AS custom_title, w.points, w.date_issued, w.date_expire, w.issued_by, w.note_admin, w.note_post, w.note_pm, t.title, u.username AS issued_by_username FROM '.$db->prefix.'warnings as w LEFT join '.$db->prefix.'warning_types AS t ON t.id=w.type_id LEFT JOIN '.$db->prefix.'users AS u ON u.id=w.issued_by WHERE w.id='.$warning_id) or error('Unable to fetch warning details', __FILE__, __LINE__, $db->error());
	
	// Check if such a warning exists
	if (!$db->num_rows($result))
		message($lang_common['Bad request']);
	else
		$warning_details = $db->fetch_assoc($result);

	// Normal users can only view their own warnings - and only if they have permission
	if ($pun_user['g_id'] == PUN_GUEST || (!$pun_user['is_admmod'] && $pun_user['id'] != $warning_details['user_id']) || (!$pun_user['is_admmod'] && $pun_config['o_warnings_see_status'] == 'mods'))
		message($lang_common['No permission']);

	// Determine warning type
	if ($warning_details['custom_title'] != '')
		$warning_title = $lang_warnings['Custom warning'].': '.$warning_details['custom_title'].' ('.sprintf($lang_warnings['No of points'], $warning_details['points']).')';
	else if ($warning_details['title'] != '')
		//$warning_title = '<a href="jaap.php">'.$warning_details['title'].' ('.$warning_details['points'].' points)'.'</a>';
		$warning_title = $warning_details['title'].' ('.sprintf($lang_warnings['No of points'], $warning_details['points']).')';
	else
		$warning_title = ''; // Means the warning type has been deleted by the administrator
	
	// Get the username of the user who is receiving the warning
	$result = $db->query('SELECT username FROM '.$db->prefix.'users WHERE id='.$warning_details['user_id']) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
	$username = $db->result($result);

	// Determine date expired
	if ($warning_details['date_expire'] == '0')
		$warning_expires = $lang_warnings['Expires'].': '.$lang_warnings['Never'];
	else if ($warning_details['date_expire'] > time ())
		$warning_expires = $lang_warnings['Expires'].': '.format_time($warning_details['date_expire']);
	else
		$warning_expires = $lang_warnings['Expired'].': '.format_time($warning_details['date_expire']);
	
	require PUN_ROOT.'include/parser.php';

	// Perform the main parsing of the message (BBCode, smilies, censor words etc)
	$note_admin = parse_message($warning_details['note_admin'], 0);

	// Perform the main parsing of the message (BBCode, smilies, censor words etc)
	$note_pm = parse_message($warning_details['note_pm'], 0);

	// Perform the main parsing of the message (BBCode, smilies, censor words etc)
	$note_post = parse_message($warning_details['note_post'], 0);

?>

<div class="linkst">
	<div class="inbox crumbsplus">
		<ul class="crumbs">
			<li><a href="profile.php?id=<?php echo $warning_details['user_id'] ?>"><?php echo $username ?></a></li>
			<li><span>»&#160;</span><a href="warnings.php?view=<?php echo $warning_details['user_id'] ?>"><strong><?php echo pun_htmlspecialchars($lang_warnings['Warnings']) ?></strong></a></li>
			<li><span>»&#160;</span><a href="warnings.php?details=<?php echo $warning_id ?>"><strong><?php echo pun_htmlspecialchars($lang_warnings['Details']) ?></strong></a></li>
		</ul>
		<div class="clearer"></div>
	</div>
</div>

<div class="blockform">
	<h2><span><?php echo $lang_warnings['Warning details'] ?></span></h2>
	<div class="box">

	<form method="post" id="post" action="warnings.php?action=send" onsubmit="return process_form(this)">

		<div class="inform">
			<fieldset>
				<legend><?php echo $lang_warnings['Warning info'] ?></legend>
				<div class="infldset">
					<p><?php echo $lang_warnings['Username'] ?>: <a href="profile.php?id=<?php echo $warning_details['user_id'] ?>"><?php echo $username ?></a></p>
					<p><?php echo $lang_warnings['Warning'] ?>: <?php echo $warning_title ?></p>
					<p><?php echo $lang_warnings['Date issued'] ?>: <?php echo format_time($warning_details['date_issued']) ?></p>
					<p><?php echo $warning_expires ?></p>
					<p><?php echo $lang_warnings['Issued by'] ?>: <a href="profile.php?id=<?php echo $warning_details['issued_by'] ?>"><?php echo $warning_details['issued_by_username'] ?></a></p>
			</div>
			</fieldset>
		</div>

<?php
if ($pun_user['is_admmod'])
{
?>
		<div class="inform">
			<fieldset>
				<legend><?php echo $lang_warnings['Admin note'] ?></legend>
				<div class="infldset">
					<?php echo ($note_admin  == '') ? $lang_warnings['No admin note'] : $note_admin  ?>
			</div>
			</fieldset>
		</div>
<?php
}
?>

<?php
// If private messaging system is enabled
if ($pun_config['o_pms_enabled'] == '1' && ($pm_pms || $pm_another || $pm_new))
{
?>
		<div class="inform">
			<fieldset>
				<legend><?php echo $lang_warnings['Private message sent'] ?></legend>
				<div class="infldset">
					<?php echo ($note_pm == '') ? $lang_warnings['No message'] : $note_pm ?>
			</div>
			</fieldset>
		</div>
<?php
}
?>

		<div class="inform">
			<fieldset>
				<legend><?php echo $lang_warnings['Copy of post'] ?></legend>
				<div class="infldset">
<?php
					if ($warning_details['post_id'])
						echo "\t\t".$note_post."\n\t\t".'<p><a href="viewtopic.php?pid='.$warning_details['post_id'].'#p'.$warning_details['post_id'].'">'.$lang_warnings['Link to post'].'</a></p>';
					else
						echo "\t\t".'<p>'.$lang_warnings['Issued from profile'].'</p>';
?>
			</div>
			</fieldset>
		</div>

<?php
if ($pun_user['g_id'] == PUN_ADMIN || ($pun_user['is_admmod'] && $pun_config['o_warnings_mod_remove'] == '1'))
{
?>

		<div class="inform">
		<input type="hidden" name="delete_id" value="<?php echo $warning_id ?>" />
		<input type="hidden" name="user_id" value="<?php echo $warning_details['user_id'] ?>" />
			<fieldset>
				<legend><?php echo $lang_warnings['Delete'] ?></legend>
				<div class="infldset">
					<input type="submit" name="delete_warning" value="<?php echo $lang_warnings['Delete warning'] ?>" />
				</div>
			</fieldset>
		</div>
<?php
}
?>


	</form>
	</div>
</div>

<div class="linksb">
	<div class="inbox crumbsplus">
		<ul class="crumbs">
			<li><a href="profile.php?id=<?php echo $warning_details['user_id'] ?>"><?php echo $username ?></a></li>
			<li><span>»&#160;</span><a href="warnings.php?view=<?php echo $warning_details['user_id'] ?>"><strong><?php echo pun_htmlspecialchars($lang_warnings['Warnings']) ?></strong></a></li>
			<li><span>»&#160;</span><a href="warnings.php?details=<?php echo $warning_id ?>"><strong><?php echo pun_htmlspecialchars($lang_warnings['Details']) ?></strong></a></li>
		</ul>
		<div class="clearer"></div>
	</div>
</div>

<?php

}

else if (isset($_POST['delete_id']))
{
	// Are we allowed to delete warnings?
	if (!($pun_user['g_id'] == PUN_ADMIN || ($pun_user['is_admmod'] && $pun_config['o_warnings_mod_remove'] == '1')))
		message($lang_common['No permission']);

	$warning_id = isset($_POST['delete_id']) ? intval($_POST['delete_id']) : 0;
	if ($warning_id < 1)
		message($lang_common['Bad request']);

	// Delete the warning
	$db->query('DELETE FROM '.$db->prefix.'warnings WHERE id='.$warning_id) or error('Unable to delete warning', __FILE__, __LINE__, $db->error());

	redirect('warnings.php?view='.intval($_POST['user_id']).'', $lang_warnings['Warning deleted redirect']);
}

else if ($_GET['action'] == 'show_recent')
{
	if (!$pun_user['is_admmod'])
		message($lang_common['No permission']);

	// Fetch warnings count
	$result = $db->query('SELECT COUNT(id) FROM '.$db->prefix.'warnings') or error('Unable to fetch warnings count', __FILE__, __LINE__, $db->error());
	$num_warnings = $db->result($result);

	// Determine the user offset (based on $_GET['p'])
	$num_pages = ceil($num_warnings / 50);

	$p = (!isset($_GET['p']) || !is_numeric($_GET['p']) || $_GET['p'] <= 1 || $_GET['p'] > $num_pages) ? 1 : $_GET['p'];
	$start_from = 50 * ($p - 1);

	// Generate paging links
	$paging_links = '<span class="pages-label">'.$lang_common['Pages'].' </span>'.paginate($num_pages, $p, 'warnings.php?action=show_recent');
?>

<div class="linkst">
	<div class="inbox crumbsplus">
		<div class="pagepost">
			<p class="pagelink conl"><?php echo $paging_links ?></p>
		</div>
		<div class="clearer"></div>
	</div>
</div>

<div id="recent_warnings" class="blocktable">

	<h2><span><?php echo $lang_warnings['Recent warnings'] ?></span></h2>
	<div class="box">
		<div class="inbox">
		<table cellspacing="0">
		<thead>
			<tr>
				<th style="width: 35%" align="left" scope="col"><?php echo $lang_warnings['Warning'] ?></th>
				<th style="width: 18%" align="left" scope="col"><?php echo $lang_warnings['Date issued'] ?></th>
				<th class="tc3" style="width: 8%" scope="col"><?php echo $lang_warnings['Points'] ?></th>
				<th class="tcr" style="width: 16%" scope="col"><?php echo $lang_warnings['Warned user'] ?></th>
				<th class="tcr" style="width: 16%" scope="col"><?php echo $lang_warnings['Issued by'] ?></th>
				<th class="tc3" style="width: 7%" scope="col"><?php echo $lang_warnings['Details'] ?></th>
			</tr>
		</thead>
		<tbody>
<?php

// If there are issued warnings
$result = $db->query('SELECT w.id, w.user_id, w.type_id, w.post_id, w.title AS custom_title, w.points, w.date_issued, w.date_expire, w.issued_by, t.title, u.username AS issued_by_username, v.username AS username FROM '.$db->prefix.'warnings as w LEFT join '.$db->prefix.'warning_types AS t ON t.id=w.type_id LEFT JOIN '.$db->prefix.'users AS u ON u.id=w.issued_by LEFT JOIN '.$db->prefix.'users AS v ON v.id=w.user_id ORDER BY w.date_issued DESC LIMIT '.$start_from.', 50') or error('Unable to fetch list of issued warnings', __FILE__, __LINE__, $db->error());
if ($db->num_rows($result))
{
	while ($warnings_active = $db->fetch_assoc($result))
	{
		// Determine warning type
		if ($warnings_active['custom_title'] != '')
			$warning_title = $lang_warnings['Custom warning'].': '.$warnings_active['custom_title'];
		else if ($warnings_active['title'] != '')
			//$warning_title = '<a href="jaap.php">'.$warnings_active['title'].'</a>';
			$warning_title = $warnings_active['title'];
		else
			$warning_title = ''; // Means the warning type has been deleted by the administrator
?>
				<tr>
					<td style="width: 35%" align="left"><?php echo $warning_title ?></td>
					<td style="width: 18%" align="left"><?php echo format_time($warnings_active['date_issued']) ?></td>
					<td class="tc3" style="width: 8%"><?php echo $warnings_active['points'] ?></td>
					<td class="tcr" style="width: 17%"><?php echo ($warnings_active['username'] != '') ? '<a href="profile.php?id='.$warnings_active['user_id'].'">'.$warnings_active['username'].'</a>' : '' ?></td>
					<td class="tcr" style="width: 17%"><?php echo ($warnings_active['issued_by_username'] != '') ? '<a href="profile.php?id='.$warnings_active['issued_by'].'">'.$warnings_active['issued_by_username'].'</a>' : '' ?></td>
					<td class="tc3" style="width: 5%"><a href="warnings.php?details=<?php echo  $warnings_active['id'] ?>">Details</a></td>
				</tr>
<?php
	}
}
else
{
?>
				<tr>
					<td class="tcl" colspan="6"><?php echo $lang_warnings['No warnings'] ?></td>
				</tr>
<?php
}
?>

			</tbody>
			</table>
		</div>
	</div>
</div>

<div class="postlinksb">
	<div class="inbox crumbsplus">
		<div class="pagepost">
			<p class="pagelink conl"><?php echo $paging_links ?></p>
		</div>
		<div class="clearer"></div>
	</div>
</div>
<?php

}

else
{
?>


<div id="warning_types" class="blocktable">

	<h2><span><?php echo $lang_warnings['Warning types'] ?></span></h2>
	<div class="box">
		<div class="inbox">
		<table cellspacing="0">
		<thead>
			<tr>
				<th class="tcr" style="width: 30%" scope="col"><?php echo $lang_warnings['Name'] ?></th>
				<th class="tcr" style="width: 60%" scope="col"><?php echo $lang_warnings['Description'] ?></th>
				<th class="tc3" style="width: 10%" scope="col"><?php echo $lang_warnings['Points'] ?></th>
			</tr>
		</thead>
		<tbody>
<?php				
$result = $db->query('SELECT id, title, description, points, expiration_time FROM '.$db->prefix.'warning_types ORDER BY points, id') or error('Unable to fetch warning types', __FILE__, __LINE__, $db->error());
while ($list_types = $db->fetch_assoc($result))
{
?>
				<tr>
					<td class="tcr" style="width: 30%"><strong><?php echo $list_types['title'] ?></strong></td>
					<td class="tcr" style="width: 60%"><?php echo $list_types['description'] ?></td>
					<td class="tc3" style="width: 10%"><?php echo $list_types['points'] ?></td>
				</tr>
<?php
}
?>
		</tbody>
		</table>
		</div>
	</div>
</div>





<div id="warning_levels" class="blocktable">

	<h2><span><?php echo $lang_warnings['Automatic bans'] ?></span></h2>
	<div class="box">
		<div class="inbox">
		<table cellspacing="0">
		<thead>
			<tr>
				<th class="tcr" style="width: 60%" scope="col"><?php echo $lang_warnings['Ban period'] ?></th>
				<th class="tcr" style="width: 40%" scope="col"><?php echo $lang_warnings['Reason'] ?></th>
			</tr>
		</thead>
		<tbody>
<?php				
$result = $db->query('SELECT id, points, method, period FROM '.$db->prefix.'warning_levels ORDER BY points, id') or error('Unable to fetch warning levels', __FILE__, __LINE__, $db->error());
while ($list_levels = $db->fetch_assoc($result))
{
	if ($list_levels['period'] == '0')
		$ban_title = $lang_warnings['Permanent ban'];
	else
		$ban_title = format_expiration_time($list_levels['period']);
?>
				<tr>
					<td class="tcr" style="width: 60%"><strong><?php echo $ban_title ?></strong></td>
					<td class="tcr" style="width: 40%"><?php printf($lang_warnings['No of points'], $list_levels['points']) ?></td>
				</tr>
<?php
}
?>
		</tbody>
		</table>
		</div>
	</div>
</div>

<?php
}


$footer_style = 'warnings';
require PUN_ROOT.'footer.php';
