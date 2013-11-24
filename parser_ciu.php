<?php

set_time_limit(0);

function dump($v, $exit = true) {
	echo('<pre>');var_dump($v);
	if($exit);exit;
}

$data = json_decode(file_get_contents('http://caniuse.com/data.json'), true);
$agents = $data['agents'];
$data = $data['data'];

foreach ($data as $property => $stats) {
	$json = array();
	$notes = $stats['notes'];
	$stats = $stats['stats'];

	foreach ($stats as $browser => $supportdata) {
		$json[$browser] = array();
		foreach ($supportdata as $version => $support) {
			$json[$browser][$version] = array(
				'supported'		=> (substr($support, 0, 1) == 'y'),
				'extrapolated'	=> false,
				'prefixed'		=> (substr($support, -1, 1) == 'x') ? $agents[$browser]['prefix'] : false
			);
		}
	}

	file_put_contents('CIU/'.$property.'.compat.json', json_encode($json));
}