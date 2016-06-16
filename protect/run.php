<?php
	//curl http://rockhardquarry.net/protect/run.php?action=%action% -u stock:XXXXXXXX >/dev/null 2>&1

	define('PUN_ROOT', '../');
	require PUN_ROOT.'include/common.php';

	define('MAX_DISPLAY', 336);
	//define('1_DAY' MAX_DISPLAY);
	//define('7_DAY', MAX_DISPLAY);
	//define('1_MTH', MAX_DISPLAY);

	$action = isset($_GET['action']) ? pun_trim($_GET['action']) : null;

	echo "\n".'Hello, it must have worked! '."<br />";

	if ($action == 'newprice') {
		$db->query('UPDATE '.$db->prefix.'stock_share SET start_price=price') or error('Unable to update ALL stock shares', __FILE__, __LINE__, $db->error());
		echo 'Success, set start stock prices action ran!';
	} elseif ($action == 'resetstock') {
		$db->query('UPDATE '.$db->prefix.'stock_share SET start_price=def_price, price=def_price, graph_values1="", graph_values7="", graph_values30=""') or error('Unable to update ALL stock shares', __FILE__, __LINE__, $db->error());
		echo 'Success, reset stock data action ran!';
	} elseif ($action == 'updateprice') {
		$result = $db->query('SELECT * FROM '.$db->prefix.'stock_share ORDER BY id_name ASC') or error('Unable to fetch stock share list', __FILE__, __LINE__, $db->error());

		$id_names = array();
		$stock_update = array(
			'last_price' => array(),
			'price' => array(),
			'graph_values1' => array(),
			'graph_values7' => array(),
			'graph_values30' => array(),
		);

		while ($stock_info = $db->fetch_assoc($result)) {
			$id_name = $stock_info['id_name'];
			$id_names[ $id_name ] = $stock_info['id'];

			$stock_update['last_price'][ $id_name ] = $stock_info['price'];
			$stock_update['price'][ $id_name ] = max($stock_info['price'] + mt_rand(-floor($stock_info['rate'] / 2), ceil($stock_info['rate'] / 2)), 10);
			$stock_update['graph_values1'][ $id_name ] = $stock_info['graph_values1'] .''. $stock_info['price'] .';';

			$stock_graph_values = explode(';', $stock_update['graph_values1'][ $id_name ]);

			$dummyarr = array();
			if (count($stock_graph_values) >= MAX_DISPLAY) {
				unset($stock_graph_values[0]); // remove item at index 0
				$dummyarr = array_values($stock_graph_values); // 'reindex' array
				$stock_update['graph_values1'][ $id_name ] = implode(';', $dummyarr); // set it as a huge fucking string because fuck you
			}
		}

		$ids = '"' .implode('","', array_keys($id_names)). '"';
		$sql_update = 'UPDATE '. $db->prefix .'stock_share SET ';

		foreach ($stock_update as $share_id => $array_data) {
			if (!empty($array_data)) {
				$sql_update .= "$share_id = CASE"."\n";

				foreach ($array_data as $id => $ordinal) {
					$sql_update .= sprintf('WHEN TRIM(id_name) = "%s" THEN "%s"'."\n", $id, $ordinal);
				}

				if ($share_id=='graph_values1')
					$sql_update .= "ELSE $share_id END"."\n";
				else
					$sql_update .= "ELSE $share_id END,"."\n";
			}
		}

		$sql_update .= "WHERE id_name IN ($ids)";
		//print($sql_update);

		$db->query($sql_update) or error('Unable to update ALL stock shares', __FILE__, __LINE__, $db->error());
		echo '<br/>Success, update stock prices set action ran!';
	}
?>