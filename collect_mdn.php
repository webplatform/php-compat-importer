<?php
//first load a list of items (by tag) to check

$urls = array(
	'https://developer.mozilla.org/en-US/docs/feeds/json/tag/CSS',
	'https://developer.mozilla.org/en-US/docs/feeds/json/tag/HTML5',
	'https://developer.mozilla.org/en-US/docs/feeds/json/tag/API',
	'https://developer.mozilla.org/en-US/docs/feeds/json/tag/WebAPI'
);
$links = array();
$to_parse = array();
$raw_data = array();

foreach ($urls as $url) {
	$data = json_decode(file_get_contents($url));
	if (!$data) continue; //unable to parse data
	foreach ($data as $d) {
		if (property_exists($d, 'link')) $links[$d->link] = $d->link; //make sure every link is only added once
	}
}


foreach ($links as $link) {
	$data = json_decode(file_get_contents($link.'$json'));
	if (!$data) continue; //unable to parse data;
	if (count($data->sections) && !array_key_exists($data->slug, $to_parse)) { //skip early for links that are already added
		foreach ($data->sections as $section) {
			if ($section->id == 'Browser_compatibility') {
				$to_parse[$data->slug] = $link;
			}
		}
	}
}

foreach($to_parse as $slug => $link) {
	if (array_key_exists($slug, $raw_data)) continue; //already collected entry
	$raw_data[$slug] = file_get_contents($link.'?raw&macros&section=Browser_compatibility');
}

file_put_contents('raw_data.json', json_encode($raw_data));