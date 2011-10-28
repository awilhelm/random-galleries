<?php

$p = new HTMLPrinter;

# Incrémente un compteur pour l'élément donné.
function inc(&$counter, $key) {
	if(!isset($counter['hits'][$key])) $counter['hits'][$key] = 0;
	++$counter['hits'][$key];
}

# Calcule le quantile d'une série à la position donnée.
function quantile($counter, $x) {
	$values = array_values($counter['hits']);
	return round($values[floor((count($values) - 1) * $x)] * (1 - $x) + $values[floor(count($values) * $x)] * $x);
}

# Récupère les journaux.
$dir = dirname($config['stats-path']);
$base = basename($config['stats-path']);

# Compte les pages vues dans le journal.
$hits = $tops = array();
foreach(scandir($dir) as $file) {
	if(!preg_match("/^$base/", $file)) continue;
	foreach(file("$dir/$file") as $line) {
		list($time, $user, $page, $referer) = preg_split("/[\t\n]/", $line);

		$date = date('M&#160;Y', $time);
		inc($hits['per-user'][$date], $user);
		inc($hits['per-page'][$date], $page);

		$date = date('l', $time);
		inc($hits['per-user-per-day'][$date], $user);
		inc($hits['per-page-per-day'][$date], $page);

		inc($tops['users'], $user);
		inc($tops['pages'], $page);
		inc($tops['referers'], $referer);
	}
}

# Calcule des statistiques à propos des pages vues.
foreach($hits as &$populations) {
	foreach($populations as &$population) {
		sort($population['hits']);
		$population['sum'] = array_sum($population['hits']);
		$population['count'] = count($population['hits']);
		$population['median'] = quantile($population, .5);
		$population['lower-quartile'] = quantile($population, .25);
		$population['upper-quartile'] = quantile($population, .75);
	} unset($population);
} unset($populations);

# Construit les palmarès.
foreach($tops as &$top) {
	$threshold = array_sum($top['hits']) / 100;
	foreach($top['hits'] as $key => $value)
		if($value < $threshold)
			unset($top['hits'][$key]);
	arsort($top['hits']);
} unset($top);

# Le nom des sections.
$captions = array(
	'per-user' => 'Pages vues par client',
	'per-page' => 'Pages vues par requête',
	'per-user-per-day' => 'Pages vues par client selon le jour de la semaine',
	'per-page-per-day' => 'Pages vues par requête selon le jour de la semaine',
	'users' => 'Clients les plus fidèles',
	'pages' => 'Pages les plus vues',
	'referers' => 'D’où ils viennent');

?>



<!doctype html public "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<title>Statistiques</title>
		<link href="<?=$config['install-path']?>/style.css" rel="stylesheet" type="text/css">
	</head>
	<body>
		<? foreach($hits as $name => $populations) { ?>
			<table class="stats">
				<caption><?=$captions[$name]?></caption>
				<tr>
					<th>Date</th>
					<td>Total</td>
					<td>Effectif</td>
					<td>Premier quartile</td>
					<td>Médiane</td>
					<td>Troisième quartile</td>
				</tr>
				<? foreach($populations as $date => $population) { ?>
					<tr>
						<th><?=$date?></th>
						<td><?=$population['sum']?></td>
						<td><?=$population['count']?></td>
						<td><?=$population['lower-quartile']?></td>
						<td><?=$population['median']?></td>
						<td><?=$population['upper-quartile']?></td>
					</tr>
				<? } ?>
			</table>
		<? } ?>
		<? foreach($tops as $name => $top) { ?>
			<table class="stats">
				<caption><?=$captions[$name]?></caption>
				<? foreach($top['hits'] as $key => $value) { ?>
					<tr>
						<th colspan="4"><?=$key?></th>
						<td><?=$value?></td>
					</tr>
				<? } ?>
			</table>
		<? } ?>
	</body>
</html>

