<?php

	// Delete user's warnings
	$db->query('DELETE FROM '.$db->prefix.'warnings WHERE user_id='.$id) or error('Unable to delete user\'s warnings', __FILE__, __LINE__, $db->error());

?>