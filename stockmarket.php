<?php
	define('PUN_ROOT', dirname(__FILE__).'/');
	require PUN_ROOT.'include/common.php';

	//if (!$pun_user['is_admmod'])
	//	message('Stock Market is now in maintenance, please come back later. All of your data is saved and will not be altered.', false, '403 Forbidden');

	define('PUN_ACTIVE_PAGE', 'index');
	$page_title = array(pun_htmlspecialchars($pun_config['o_board_title']), 'Stock Market');

	$page		= isset($_GET['p']) ? pun_trim($_GET['p']) : null;
	$stockid	= isset($_GET['id']) ? intval(pun_trim($_GET['id'])) : null;
	$action		= isset($_GET['action']) ? pun_trim($_GET['action']) : null;

	function ShowDiffInfo($price, $last_price, $name = null) {
		if (!isset($price) || !isset($last_price))
			return '';

		$extra_text = '';
		$difference = abs($last_price - $price);
		$calculation = round(($difference  / $price) * 100, 2);

		if (isset($name))
			$extra_text = $name;

		if ($last_price <= $price) {
			$color = 'green';
			if ($difference == 0)
				$color = 'black';
	
			return '<span style="color:'.$color.';">'.$extra_text.' +'.$difference.' ('.$calculation.'%)</span>';
		} else {
			return '<span style="color:red;">'.$extra_text.' -'.$difference.' ('.$calculation.'%)</span>';
		}
	}

	function ShowRangeInfo($price, $rate) {
		if (!isset($price) || !isset($rate))
			return '';

		$upper = ceil($rate / 2);
		$lower = floor($rate / 2);

		return forum_number_format($price - $lower) .' to '. forum_number_format($price + $upper);
	}

	require PUN_ROOT.'header.php';

	$stock_array = array();
	$result = $db->query('SELECT sh.*, SUM(sp.quantity) AS volume FROM '.$db->prefix.'stock_share AS sh LEFT JOIN '.$db->prefix.'stock_portfolio AS sp ON (sp.stockid=sh.id_name) GROUP BY sh.id ORDER BY sh.id_name ASC') or error('Unable to fetch stock share list', __FILE__, __LINE__, $db->error());

	if ($db->num_rows($result)) {
		while ($stock_info = $db->fetch_assoc($result)) {
			$stock_array[] = $stock_info;
		}
	} else {
		message('Failed to fetch stock market information, please try again later.');
	}

	if (!isset($_POST['form_stock']) && $action != 'sell') {
?>
	<style>
		p#info {
			margin-bottom: 12px;
		}
	</style>

	<div class="box" style="font-weight: bold; font-size: 18px; margin-bottom: 16px; padding: 2px 0px 2px 0px">
		<table>
			<tr>
				<td style="border: none;">
					<?php if (!$pun_user['is_guest']): ?>
					Rocks: <?php echo forum_number_format($pun_user['rocks']); ?><br />Net-Worth: <?php echo forum_number_format($pun_user['rocks']); ?>
					<?php endif; ?>
				</td>
				<td style="text-align: right; border: none;">
					<a href="stockmarket.php">Index</a>&nbsp;|&nbsp;<a href="stockmarket.php?p=portfolio">My Portfolio</a>&nbsp;|&nbsp;<a href="stockmarket.php?p=help">Help / Rules</a>
				</td>
			</tr>
		</table>

		<hr>

		<marquee>
		<?php
			foreach ($stock_array as $stock_info) {
				echo '<a href="stockmarket.php?p=view&id='.$stock_info['id'].'"'. ShowDiffInfo( $stock_info['price'], $stock_info['start_price'], $stock_info['id_name'] ). '</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			}
		?>
		</marquee>
	</div>
<?php
	}

	if ($page == 'view') {
		$quantity = isset($_POST['quantity']) ? intval(pun_trim($_POST['quantity'])) : '1';

		if (!isset($stockid) || !is_numeric($stockid))
			message('Invalid Stock ID!');

		foreach ($stock_array as $stock_check) {
			if ($stock_check['id'] == $stockid) {
				$stock_info = $stock_check;
				break;
			}
		}

		if (isset($_POST['form_stock'])) {
			confirm_referrer('stockmarket.php');

			if ($pun_user['is_guest'])
				message('You need to be logged-in in order to do that action!');

			if (!isset($quantity) || !is_numeric($quantity) || $quantity <= '0')
				message('Invalid Stock Quantity!');

			//if ($pun_user['stock_limit'] <= '0' || $pun_user['stock_limit'] < $quantity)
			//	message('You already reached the limit or is trying to overreach the limit.');

			$total_price = floor($quantity * $stock_info['price']);

			if ($pun_user['rocks'] >= $total_price) {
				$result = $db->query('SELECT id FROM '.$db->prefix.'stock_portfolio WHERE(userid='.$pun_user['id'].' AND stockid="'.$stock_info['id_name'].'" AND price='.$stock_info['price'].')') or error('Unable to fetch stock share list', __FILE__, __LINE__, $db->error());

				if ($db->num_rows($result)) {
					$stockid = $db->result($result);

					$db->query('UPDATE '.$db->prefix.'stock_portfolio AS sp INNER JOIN '.$db->prefix.'users AS u SET sp.quantity=quantity+'.$quantity.', u.rocks=u.rocks-'.$total_price.' WHERE(sp.id='.$stockid.' AND u.id=sp.userid)') or error('Unable to update stocks', __FILE__, __LINE__, $db->error());
				} else {
					$db->query('INSERT INTO '.$db->prefix.'stock_portfolio (userid, stockid, quantity, price) VALUES ('.$pun_user['id'].', "'.$stock_info['id_name'].'", '.$quantity.', '.$stock_info['price'].')') or error('Unable to insert stock share list', __FILE__, __LINE__, $db->error());
					$db->query('UPDATE '.$db->prefix.'users SET rocks=rocks-'.$total_price.' WHERE id='.$pun_user['id']) or error('Unable to update users', __FILE__, __LINE__, $db->error());
				}

				redirect('stockmarket.php', 'Successfully bought '.$stock_info['name'].'\'s '.$quantity.' stock for '.$total_price.'.');
			//} elseif ($quantity > $pun_user['stock_limit']) {\
			//	message('You already reached the daily stock buy limit! Sell-in order to return the limit.');
			} else {
				message('You do not have enough rocks to buy that many stocks!');
			}
		}
?>

	<div id="rules" class="blockform">
		<div class="hd"><h2><span>Rock Market Simulator v0.1 - View</span></h2></div>
		<div class="box">
			<script type="text/javascript" src="https://www.google.com/jsapi"></script>
			<script type="text/javascript">
			google.load("visualization", "1", {packages:["corechart"]});
				google.setOnLoadCallback(drawChart);
				function drawChart() {
					var data = google.visualization.arrayToDataTable([
					['Time', 'Price'],
					<?php
						$stock_graph_values = explode(';', $stock_info['graph_values1']);

						$i = 1;
						foreach ($stock_graph_values as $value) {
							if (!empty($value)) {
								echo '['.$i.','.$value.'],';
							}

							$i++;
						}
						echo '['.($i-1).','.$stock_info['price'].'],';
					?>
					]);

					var options = {
						title: '<?php echo $stock_info['name']; ?> Price Performance',
						titlePosition: 'none',
						chartArea: {width: '80%', height: '85%', left:'15%', top: '7.5%'},
						vAxis: {title: 'Price'},
						hAxis: {title: 'Time', textPosition: 'none', titleTextStyle: {color: '#FFF'}, minValue: 1},
						legend: {position: 'none'},
						is3D: true,
					};

					var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
					chart.draw(data, options);
				}
			</script>

			<table>
				<tr style="font-size: 18px;">
					<td style="width: 40%; text-bold: 1000; padding: 48px;">
						<div style="text-align: center; margin-bottom: 30px;">
							<img src="img/stocks/<?php echo $stock_info['id_name']; ?>.png" style="margin-bottom: 16px;" width="75" height="75">
							<br />
							Stock Information
						</div>

						<br />

						<p id="info">Stock Name: <?php echo $stock_info['name']; ?> (<?php echo $stock_info['id_name']; ?>)</p>
						<p id="info">Opening Price: <?php echo forum_number_format($stock_info['start_price']); ?> rocks</p>
						<p id="info">Current Price: <?php echo forum_number_format($stock_info['price']); ?> rocks</p>
						<p id="info">Change: <?php echo ShowDiffInfo($stock_info['price'], $stock_info['start_price']); ?></p>
						<p id="info">Next Range: <?php echo ShowRangeInfo($stock_info['price'], $stock_info['rate']); ?> rocks</p>
						<p id="info">Volume: <?php echo empty($stock_info['volume']) ? 0 : forum_number_format($stock_info['volume']); ?></p>

						<br />

						<form id="buy_stocks" style="text-align: center;" method="post" action="stockmarket.php?p=view&id=<?php echo $stockid; ?>" value="1">
							<p id="info">How many stocks are you buying?</p>
							<input type="text" name="quantity" size="15" maxlength="10" value="1" tabindex="1" />
							<input type="submit" name="form_stock" value="Buy!" tabindex="2" />
						</form>
					</th>

					<td style="width: 60%; height: 500px; text-align: center;">
						<div id="chart_div" style="width: 100%; height: 100%;"></div>
					</td>
				<tr>
			</table>
		</div>
	</div>

<?php
	} elseif ($page == 'portfolio') {

	if ($pun_user['is_guest'])
		message('You need to be logged-in in order to do that action!');

	if (isset($action) && $action == 'sell') {
		if (!isset($stockid) || !is_numeric($stockid))
			message('Invalid Stock ID!');

		$result = $db->query('SELECT sp.stockid, sp.quantity, sp.price AS cost, sk.price AS price FROM '.$db->prefix.'stock_portfolio AS sp INNER JOIN '.$db->prefix.'stock_share AS sk ON (sp.stockid=sk.id_name) WHERE (sp.userid='.$pun_user['id'].' AND sp.id='.$stockid.')') or error('Unable to fetch stock market data', __FILE__, __LINE__, $db->error());

		if ($db->num_rows($result))
			$stock_port_info = $db->fetch_assoc($result);
		else
			message('Invalid Portfolio Stock ID');

		$quantity = $stock_port_info['quantity'];
		$total_price = ceil($quantity * $stock_port_info['price'] * 0.99);

		$compare = $pun_user['rocks'] + $total_price + 1;

		if ($compare <= 9223372036854775807)
			$db->query('UPDATE '.$db->prefix.'users SET rocks=rocks+'.$total_price.' WHERE id='.$pun_user['id']) or error('Unable to update user', __FILE__, __LINE__, $db->error());
		else
			$db->query('UPDATE '.$db->prefix.'users SET rocks=9223372036854775807 WHERE id='.$pun_user['id']) or error('Unable to update user', __FILE__, __LINE__, $db->error());

		$db->query('DELETE FROM '.$db->prefix.'stock_portfolio WHERE id='.$stockid) or error('Unable to delete stock market data', __FILE__, __LINE__, $db->error());

		redirect('stockmarket.php?p=portfolio', 'Successfully sold '.$stock_port_info['stockid'].' for '.$total_price.' with 1% commission.');
	}
?>

	<div id="rules" class="blockform">
		<div class="hd"><h2><span>Rock Market Simulator v0.1 - Your Portfolio</span></h2></div>
		<div class="box">
			<table align="center">
				<tr style="font-weight: bold; font-size: 18px;">
					<th colspan="2" style="text-align: center;">Stock</th>
					<th colspan="3" style="text-align: center;">Overall</th>
					<th colspan="4" style="text-align: center;">Assets</th>
				</tr>
				<tr style="font-weight: bold; font-size: 18px;">
					<th style="width: 10%; text-align: center;">Logo</th>
					<th style="width: 5%; text-align: center;">Ticker</th>
					<th style="width: 10%; text-align: center;">Opening</th>
					<th style="width: 10%; text-align: center;">Paid</th>
					<th style="width: 10%; text-align: center;">Current</th>
					<th style="width: 10%; text-align: center;">Change</th>
					<th style="width: 10%; text-align: center;">Quantity</th>
					<th style="width: 10%; text-align: center;">Total</th>
					<th style="width: 5%; text-align: center;">Sell?</th>
				</tr>
			<?php
				$result = $db->query('SELECT sp.*, sk.start_price AS start_cost, sk.price AS cost, sk.id_name FROM '.$db->prefix.'stock_portfolio AS sp INNER JOIN '.$db->prefix.'stock_share AS sk ON sp.stockid=sk.id_name WHERE userid='.$pun_user['id'].' ORDER BY stockid ASC, quantity DESC, price DESC') or error('Unable to fetch stock market data', __FILE__, __LINE__, $db->error());
				while ($stock_info = $db->fetch_assoc($result)) {
			?>
				<tr style="font-weight: bold; font-size: 18px;">
					<td style="text-align: center;"><img src="img/stocks/<?php echo $stock_info['stockid']; ?>.png" width="75" height="75"></td>
					<td style="text-align: center;"><?php echo $stock_info['stockid']; ?></td>
					<td style="text-align: center;"><?php echo forum_number_format($stock_info['start_cost']); ?></td>
					<td style="text-align: center;"><?php echo forum_number_format($stock_info['price']); ?></td>
					<td style="text-align: center;"><?php echo forum_number_format($stock_info['cost']); ?></td>
					<td style="text-align: center;"><?php echo ShowDiffInfo($stock_info['cost'], $stock_info['price']); ?></td>
					<td style="text-align: center;"><?php echo forum_number_format($stock_info['quantity']); ?></td>
					<td style="text-align: center;"><?php echo forum_number_format($stock_info['quantity'] * $stock_info['cost']); ?></td>
					<td style="text-align: center;"><a href="stockmarket.php?p=portfolio&action=sell&id=<?php echo $stock_info['id']; ?>">Sell!</a></td>
				</tr>
			<?php
				}
			?>
			</table>
		</div>
	</div>

<?php 
	} elseif ($page == 'help') {
?>
	<div id="rules" class="blockform">
		<div class="hd"><h2><span>Rock Market Simulator v0.1 - Help</span></h2></div>
		<div class="box">
			<div style="padding: 12px">
				<h1>What are stocks?</h1>
				<p>Companies can divide themselves into "shares". Each share represents a bit of the company, so when you buy a share, you get a bit of the company.</p>
				<br />
				<h1>Why do prices go up and down?</h1>
				<p>People want to buy shares of companies that are making lots of money, so that they can profit. That means that the stock will go in demand, so the price increases because more people want it.</p>
				<p>Likewise, when the company isn't doing so well, the price will drop because everyone wants to sell their stocks and no one wants to buy.</p>
				<br />
				<h1>What am I supposed to do?</h1>
				<img src="http://robloxhq.com/app/webroot/img/help_graph.png" style="float: right; margin: 10px;" alt="">
				<p>Everyone starts with 50,000 Rocks. Rocks are the currency of the Rock Hard Quarry Exchange (RHQE). Your goal is to be the richest person trading in RHQE.</p>
				<p>You make money by buying and selling stocks. You make a profit when you buy a stock for a low price and sell it for a high price. For example, if you bought 50 shares of Crossroads (CROS) for 10 M (costing 500 M) and then sold them later for 15 M, you would make 250 M.</p>
				<p>There are some market regulations in effect:</p>
				<ol>
					<!--<li>You can't buy more than 2500 shares in a day.</li>-->
					<li>There is a 1% commission on all trades.</li>
				</ol>

				<p>Here are some tips:</p>
				<ol>
					<li>Diversify. Diversification means buying shares from more than one company. You could buy 1000 shares of one company, but if it goes down or bankrupts, you're screwed. It's better to get 250 shares of four companies so that if one goes down, three are hopefully still good.</li>
					<li>Buy the maximum shares every day. A bigger portfolio means more chances to get money. If you don't buy shares, you can't make money!</li>
				</ol>
				<br />
				<h1>How often do stocks update?</h1>
				<p>Stocks update every 30 minutes.</p>
				<br />
				<h1>How do I sell shares?</h1>
				<ol>
					<li>Go to <a href="stockmarket.php?p=portfolio">your portfolio</a>.</li>
					<li>Click Details on the stock you want to sell.</li>
					<li>Click Sell.</li>
				</ol>
				<br />
				<h1>What is net worth?</h1>
				<p>Net worth is the current market value of all your shares, plus your Rocks. So if I had ten shares of a stock trading at 5 and 10 Rocks, I would have 10 * 5 + 10 = 60 net worth.</p>
			</div>
		</div>
	</div>
<?php 
	} else {
?>

	<div id="rules" class="blockform">
		<div class="hd"><h2><span>Rock Market Simulator v0.1 - Index</span></h2></div>
		<div class="box">
			<table align="center">
				<tr style="font-weight: bold; font-size: 18px;">
					<th style="width: 10%; text-align: center;">Logo</th>
					<th style="width: 5%; text-align: center;">Ticker</th>
					<th style="width: 25%; text-align: center;">Name</th>
					<th style="width: 10%; text-align: center;">Opening</th>
					<th style="width: 10%; text-align: center;">Current</th>
					<th style="width: 10%; text-align: center;">Change</th>
					<th style="width: 15%; text-align: center;">Next Range</th>
					<th style="width: 10%; text-align: center;">Volume</th>
				</tr>
			<?php
				foreach ($stock_array as $stock_info) {
			?>
				<tr style="font-weight: bold; font-size: 18px;">
					<td style="text-align: center;"><img src="img/stocks/<?php echo $stock_info['id_name']; ?>.png" width="75" height="75"></td>
					<td style="text-align: center;"><a href="stockmarket.php?p=view&id=<?php echo $stock_info['id']; ?>"><?php echo $stock_info['id_name']; ?></a></td>
					<td><a href="stockmarket.php?p=view&id=<?php echo $stock_info['id']; ?>"><?php echo $stock_info['name']; ?></a></td>
					<td style="text-align: center;"><?php echo forum_number_format($stock_info['start_price']); ?></td>
					<td style="text-align: center;"><?php echo forum_number_format($stock_info['price']); ?></td>
					<td style="text-align: center;"><?php echo ShowDiffInfo( $stock_info['price'], $stock_info['start_price'] ); ?></td>
					<td style="text-align: center;"><?php echo ShowRangeInfo( $stock_info['price'], $stock_info['rate'] ); ?></td>
					<td style="text-align: center;"><?php echo empty($stock_info['volume']) ? 0 : forum_number_format($stock_info['volume']); ?></td>
				</tr>
			<?php
				}
			?>
			</table>
		</div>
	</div>

<?php
	}

require PUN_ROOT.'footer.php'; 
?>