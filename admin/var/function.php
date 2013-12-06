<?php

function daily($dir) {
	$doc = array();
	foreach(glob($dir.'*') as $item) {
		if(!is_file($item)) $doc = array_merge($doc, daily($item.'/*'));
		else $doc[] = $item;
	}
	return $doc;
}
?>
