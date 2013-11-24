<?php

set_time_limit(0);

function dump($v, $exit = true) {
	echo('<pre>');var_dump($v);
	if($exit);exit;
}


$data = json_decode(file_get_contents('http://beta.html5test.com/api/exportResults'), true);
$json = array();

$browsers = array();
foreach ($data['browsers'] as $browser) {
	$name = $browser['name'];
	foreach ($browser['versions'] as $version) {
		$browsers[$version['id']] = array('name' => $name, 'version' => $version['version']);
	}
}

foreach ($data['results'] as $property => $results) {
	foreach ($results as $browserid => $result) {
		$browser = $browsers[$browserid];
		$json[$browser['name']][$browser['version']] = array(
			'supported'		=> ($result == 'yes'),
			'extrapolated'	=> false,
			'prefixed'		=> false
		);
	}

	file_put_contents('H5T/'.$property.'.compat.json', json_encode($json));
}