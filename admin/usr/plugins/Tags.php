<?php
/**
 * PMBlog's plugin to Provides tags functionality for it.
 * Add site.tags.TAG and TagCloud for the template.
 * site.tags.TAG : It stores all post include TAG, its structor is same as posts.
 * TagCloud : It stores all tagnames. How to use it?
 * Example: 
 * 	{% for tag in TagCloud %}
 *		{{tag.title}}		{# Show Tagname #}
 *		{{tag.url}}			{# show TagURL #}
 *		{{tag.length}}			{# show TagPostLength #}
 *	{% endfor %}
 *
 *	And this plugin add tag index page for your blog. All index pages save in the directory named 'tag'
 *
 * @author lizheming
 * @link http://github.com/lizheming
 * @version 0.1.0
 */
class Tags {
	private $tags = array();
	
	function after_get_post_meta(&$post) {
		if($post['type'] == 'post' && isset($post['tags'])) {
			foreach($post['tags'] as $tag) {
				$tag = strtolower($tag);
				$this->tags[$tag][] = $post;
			}
		}
	}

	function after_get_variables(&$variables) {
		$tagclouds = array();
		foreach($this->tags as $tag => $posts) {
			usort($posts, 'tag_sort');
			$tagcloud = array('title'=> $tag, 'url'=> $variables['site']['url'].'/tag/'.urlencode($tag), 'length'=> count($posts));
			$tagclouds[] = $tagcloud;
			$this->tags[$tag] = $posts;
		}
		$variables['site']['tags'] = $this->tags;
		$variables['TagCloud'] = $tagclouds;
	}

	function twig_loaded($variables, $twig) {	
		foreach($this->tags as $tag => $posts) {
			$tag = urlencode($tag);
			foreach($this->paginator($tag, $posts, $variables['site']) as $paginator) {
				$template = 'index.html';
				$vars = $variables;
				$vars['posts'] = $paginator['object_list'];
				$vars['paginator'] = $paginator;
				$html = $twig->render($template, $vars);
				Tags::file_put_contents("{$variables['site']['config']['html']}tag/$tag/page/{$paginator['page']}/index.html", $html);
			}
			copy("{$variables['site']['config']['html']}tag/$tag/page/1/index.html", "{$variables['site']['config']['html']}tag/$tag/index.html");
		}
	}

	function paginator($key, $post, $site) {
		$perpage = $site['config']['posts_per_page'];
		$total_pages = ceil(count($post)/$perpage);
		$paginator = array();
		for($i=0;$i<$total_pages;$i++) 
		{
			$has_next = $has_previous = true;
			if(($i+2) > $total_pages) $has_next = false;
			if($i<1) $has_previous = false; 
			$pagpost = array_slice($post, $i*$perpage, $perpage);
			$paginator[$i+1] =  array(
				'per_page' => $perpage,
				'object_list'=>$pagpost, 
				'total_pages'=> $total_pages, 
				'page' => $i+1, 
				'page_url' => $site['url'].'/'.$key.'/page/'.($i+1),
				'previous_page' => $i,
				'pre_page' => $i, 
				'previous_page_url' => $site['url'].'/'.$key.'/page/'.$i,
				'pre_page_url' => $site['url'].'/'.$key.'/page/'.$i, 
				'next_page' => $i+2, 
				'next_page_url' => $site['url'].'/'.$key.'/page/'.($i+2), 
				'has_next' => $has_next, 
				'has_previous' => $has_previous,
				'has_pre' => $has_previous
			);
		}
		return $paginator;
	}

	public function file_put_contents($file, $data)
	{
		$path = dirname($file);
		is_writeable($path) || mkdir($path, 0777, true);
		return file_put_contents($file, $data);
	}
}

function tag_sort($a, $b) 
{
	if ($a['date'] == $b['date']) return 0;
	return ($a['date'] < $b['date']) ? 1 : -1;
}
?>
