<?php

set_time_limit(0);

function dump($v, $exit = true) {
	echo('<pre>');var_dump($v);echo('</pre>');
	if($exit) exit;
}

$agents = array('desktop' => array(), 'mobile' => array());
$ciudata = json_decode(file_get_contents('http://caniuse.com/data.json'));
foreach($ciudata->agents as $agent) {
	$versions = array();
	foreach ($agent->versions as $version) {
		if (is_null($version)) continue;
		$versions[] = $version;
	}
	if ($agent->browser == 'IE') $agent->browser = 'Internet Explorer';
	$agents[$agent->browser] = $versions;
}


$data = json_decode(file_get_contents('raw_data.json'));
$json = array();
foreach ($data as $slug => $compat) {
	$property = array_pop(explode('/', $slug));

	$dom = new DomDocument();
	$dom->loadHTML($compat);

	$desktop = $dom->getElementById('compat-desktop');
	if ($desktop) {
		$rows = $desktop->getElementsByTagName('tr');
		//first row gives us 'feature' and all the browser names
		$header = $rows->item(0);
		$headercells = $header->getElementsByTagName('th');
		$browsers = array();
		$compat = array();
		for ($i = 1, $l = $headercells->length; $i < $l; $i++) {
			$cell = $headercells->item($i);
			$browser = $cell->nodeValue;
			$browsers[$i] = $browser;
		}
		for ($i = 1, $l = $rows->length; $i < $l; $i++) {
			$compat[$i] = array();
			$row = $rows->item($i);
			$cells = $row->getElementsByTagName('td');
			for ($x = 0, $y = $cells->length; $x < $y; $x++) {
				$cell = $cells->item($x);
				$compat[$i][$x] = $cell;
			}
		}
		foreach ($compat as $c) {
			$name = $c[0]->nodeValue;
			$json[$name] = array();
			for ($i = 1, $l = count($c); $i < $l; $i++) {
				$browser = $browsers[$i];
				$prefix = false;
				if (substr($browser, -1) == '*') $browser = substr($browser, 0, -1);
				if ($browser == 'Firefox (Gecko)') $browser = 'Firefox';
				if ($browser == 'Safari (Webkit)') $browser = 'Safari';
				if ($browser == 'Safari (WebKit)') $browser = 'Safari';
				if ($browser == 'Chrome (Webkit)') $browser = 'Chrome';
				if ($browser == 'Chrome (WebKit)') $browser = 'Chrome';
				if ($browser == 'Opera (Presto)') $browser = 'Opera';
				$json[$name][$browser] = array();
				//check if there is a prefix-span
				$spans = $c[$i]->getElementsByTagName('span');
				for ($x = 0, $y = $spans->length; $x < $y; $x++) {
					$span = $spans->item($x);
					if ($span->hasAttribute('title') && $span->getAttribute('title') == 'prefix') {
						//we're having prefix data as well.
						//replace the <br> so we can actually split the value
						$prefix = $span->nodeValue;
						$brs = $c[$i]->getElementsByTagName('br');
						for ($a = $brs->length - 1; $a >= 0; $a--) {
							$br = $brs->item($a);
							try {
								if ($c[$i]->isSameNode($br->parentNode)) $c[$i]->replaceChild($dom->createTextNode('SPLIT'), $br);
							} catch (Exception $e) {
								dump($brs);
							}
						}
						$versions = array_map('trim', explode("SPLIT", $c[$i]->nodeValue));
						foreach ($versions as $version) {
							if (strpos($version, $prefix) === false) {
								$json[$name][$browser][(float)$version] = array(
									'supported' 	=> $supported,
									'extrapolated'	=> $extrapolated,
									'prefixed'		=> false
								);
							} else {
								$json[$name][$browser][(float)$version] = array(
									'supported' 	=> $supported,
									'extrapolated'	=> $extrapolated,
									'prefixed'		=> $prefix
								);
							}
						}
					}
				}
				if (!$prefix) {
					$startversion = (float) $c[$i]->nodeValue;
					$supported = false;
					$extrapolated = false;
					foreach ($agents[$browser] as $version) {
						$version = (float) $version;
						if ($supported) $extrapolated = true;
						if ($version >= $startversion || substr($c[$i]->nodeValue, 0, 3) == 'yes') $supported = true;
						$json[$name][$browser][$version] = array(
							'supported' 	=> $supported,
							'extrapolated'	=> $extrapolated,
							'prefixed'		=> false
						);
					}
				}
			}
		}
	}

	$mobile = $dom->getElementById('compat-mobile');
	if ($mobile) {
		$rows = $desktop->getElementsByTagName('tr');
		//first row gives us 'feature' and all the browser names
		$header = $rows->item(0);
		$headercells = $header->getElementsByTagName('th');
		$browsers = array();
		$compat = array();
		for ($i = 1, $l = $headercells->length; $i < $l; $i++) {
			$cell = $headercells->item($i);
			$browser = $cell->nodeValue;
			$browsers[$i] = $browser;
		}
		for ($i = 1, $l = $rows->length; $i < $l; $i++) {
			$compat[$i] = array();
			$row = $rows->item($i);
			$cells = $row->getElementsByTagName('td');
			for ($x = 0, $y = $cells->length; $x < $y; $x++) {
				$cell = $cells->item($x);
				$compat[$i][$x] = $cell;
			}
		}
		foreach ($compat as $c) {
			$name = $c[0]->nodeValue;
			if (!is_array($json[$name])) $json[$name] = array();
			for ($i = 1, $l = count($c); $i < $l; $i++) {
				$browser = $browsers[$i];
				$prefix = false;
				if (substr($browser, -1) == '*') $browser = substr($browser, 0, -1);
				if ($browser == 'Opera') $browser = 'Opera Mobile';
				if ($browser == 'Opera (Presto)') $browser = 'Opera Mobile';
				if ($browser == 'Firefox') $browser = 'Firefox for Android';
				if ($browser == 'Firefox (Gecko)') $browser = 'Firefox for Android';
				if ($browser == 'Chrome') $browser = 'Chrome for Android';
				if ($browser == 'Chrome (Webkit)') $browser = 'Chrome for Android';
				if ($browser == 'Chrome (WebKit)') $browser = 'Chrome for Android';
				if ($browser == 'Internet Explorer') $browser = 'IE Mobile';
				if ($browser == 'Safari') $browser = 'iOS Safari';
				if ($browser == 'Safari (Webkit)') $browser = 'iOS Safari';
				if ($browser == 'Safari (WebKit)') $browser = 'iOS Safari';
				$json[$name][$browser] = array();
				//check if there is a prefix-span
				$spans = $c[$i]->getElementsByTagName('span');
				for ($x = 0, $y = $spans->length; $x < $y; $x++) {
					$span = $spans->item($x);
					if ($span->hasAttribute('title') && $span->getAttribute('title') == 'prefix') {
						//we're having prefix data as well.
						//replace the <br> so we can actually split the value
						$prefix = $span->nodeValue;
						$brs = $c[$i]->getElementsByTagName('br');
						for ($a = 0, $b = $brs->length; $a < $b; $a++) {
							$br = $brs->item($a);
							try {
								if ($c[$i]->isSameNode($br->parentNode)) $c[$i]->replaceChild($dom->createTextNode('SPLIT'), $br);
							} catch (Exception $e) {
								dump($brs);
							}
						}
						$versions = array_map('trim', explode("SPLIT", $c[$i]->nodeValue));
						foreach ($versions as $version) {
							if (strpos($version, $prefix) === false) {
								$json[$name][$browser][(float)$version] = array(
									'supported' 	=> $supported,
									'extrapolated'	=> $extrapolated,
									'prefixed'		=> false
								);
							} else {
								$json[$name][$browser][(float)$version] = array(
									'supported' 	=> $supported,
									'extrapolated'	=> $extrapolated,
									'prefixed'		=> $prefix
								);
							}
						}
					}
				}
				if (!$prefix) {
					$startversion = (float) $c[$i]->nodeValue;
					$supported = false;
					$extrapolated = false;
					foreach ($agents[$browser] as $version) {
						$version = (float) $version;
						if ($supported) $extrapolated = true;
						if ($version >= $startversion || substr($c[$i]->nodeValue, 0, 3) == 'yes') $supported = true;
						$json[$name][$browser][$version] = array(
							'supported' 	=> $supported,
							'extrapolated'	=> $extrapolated,
							'prefixed'		=> false
						);
					}
				}
			}
		}
	}

	while (in_array(substr($property, 0, 1), array(':'))) $property = substr($property, 1);

	file_put_contents('MDN/'.$property.'.compat.json', json_encode($json));
	//dump($json, false);
}