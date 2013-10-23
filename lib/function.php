<?php
function mkfolder($path)	{
	return is_writeable($path) || mkdir($path, 0777, true);
}

function paginator($post) {
	global $site;
	$perpage = $site['config']['posts_per_page'];
	$total_pages = ceil(count($post)/$perpage);
	for($i=0;$i<$total_pages;$i++) {
		$has_next = $has_previous = true;
		if(($i+2) > $total_pages) $has_next = false;
		if($i<1) $has_previous = false; 
		$pagpost = array_slice($post, $i*$perpage, $perpage);
		$paginator[$i+1] =  array(
			'per_page' => $perpage,
			'object_list'=>$pagpost, 
			'total_pages'=> $total_pages, 
			'page' => $i+1, 
			'previous_page' => $i,
			'pre_page' => $i, 
			'previous_page_url' => $i.'.html',
			'pre_page_url' => $i.'.html', 
			'next_page' => $i+2, 
			'next_page_url' => ($i+2).'.html', 
			'has_next' => $has_next, 
			'has_previous' => $has_previous,
			'has_pre' => $has_previous
		);
	}
	return $paginator;
}
?>
