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
	private $tags;
	
	function after_get_post_meta(&$post) {
		if(isset($post['tags'])) {
			foreach($post['tags'] as $tag) {
				$this->tags[$tag][] = $post;
			}
		}
	}

	function sort($a, $b) {
		if($a['date'] == $b['date']) {
			return 0;
		}
		return ($a['date'] < $b['date']) ? 1 : -1;
	}
	
	function after_get_variables(&$variables) {
		$tagcloud = array();
		foreach(array_keys($this->tags) as $key) {
			usort($this->tags[$key], create_function('$a,$b', 'if ($a[\'date\'] == $b[\'date\']) return 0;return ($a[\'date\'] < $b[\'date\']) ? 1 : -1;'));
			$tagcloud[]['title'] = $key;
			$tagcloud[]['url'] = $variables['site']['url'].'/tag/'.$key;
			$tagcloud[]['length'] = count($this->tags[$key]);
		}
		$variables['site']['tags'] = $this->tags;
		$variables['TagCloud'] = $tagcloud;
	}

	function before_render(&$variables, &$twig) {
		foreach($this->tags as $key => $tag) {
			$key = urlencode($key);
			foreach($this->paginator($key, $tag, $variables['site']) as $paginator) {
				$template = 'index.html';
				$twig_vars = array_merge(array('posts'=>$paginator['object_list'], 'paginator' => $paginator), $variables);
				$html = $twig->render($template, $twig_vars);

				$index_path = "{$twig_vars['site']['config']['html']}tag/$key/page/{$paginator['page']}";
				Tags::mkdir($index_path);
				file_put_contents("$index_path/index.html", $html);
			}
			//copy("{$twig_vars['site']['config']['html']}tag/$key/page/1/index.html", "{$twig_vars['site']['config']['html']}tag/$key/index.html");
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
	
	public function mkdir($path)
	{
		return is_writeable($path) || mkdir($path, 0777, true);
	}
}
?>
