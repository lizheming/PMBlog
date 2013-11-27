<?php
/**
 * PMBlog's plugin to Provides categories functionality for it.
 * Add site.categories.category and CategoryCloud for the template.
 * site.categories.CATEGORY : It stores all post include CATEOGRY, its structor is same as posts.
 * CategoryCloud : It stores all categorynames. How to use it?
 * Example: 
 * 	{% for category in CategoryCloud %}
 *		{{category.title}}		{# Show categoryname #}
 *		{{category.url}}			{# show categoryURL #}
 *		{{category.length}}			{# show categoryPostLength #}
 *	{% endfor %}
 *
 *	And this plugin add category index page for your blog. All index pages save in the directory named 'category'
 *
 * @author lizheming
 * @link http://github.com/lizheming
 * @version 0.1.0
 */
class Categories {
	private $Categories;

	function after_get_post_meta(&$post) {
		if(isset($post['categories'])) {
			foreach($post['categories'] as $category) {
				$this->Categories[$category][] = $post;
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
		$categorycloud = array();
		foreach(array_keys($this->Categories) as $key) {
			usort($this->Categories[$key], create_function('$a,$b', 'if ($a[\'date\'] == $b[\'date\']) return 0;return ($a[\'date\'] < $b[\'date\']) ? 1 : -1;'));
			$categorycloud[]['title'] = $key;
			$categorycloud[]['url'] = $variables['site']['url'].'/category/'.$key;
			$categorycloud[]['length'] = count($this->Categories[$key]);
		}
		$variables['site']['Categories'] = $this->Categories;
		$variables['categoryCloud'] = $categorycloud;
	}

	function twig_loaded(&$variables, &$twig) {
		foreach($this->Categories as $key => $category) {
			$key = urlencode($key);
			foreach($this->paginator($key, $category, $variables['site']) as $paginator) {
				$template = 'index.html';
				$twig_vars = array_merge(array('posts'=>$paginator['object_list'], 'paginator' => $paginator), $variables);
				$html = $twig->render($template, $twig_vars);
				
				$index_path = "{$twig_vars['site']['config']['html']}/category/$key/page/{$paginator['page']}";
				Categories::mkdir($index_path);
				file_put_contents("$index_path/index.html", $html);
			}
			copy("{$twig_vars['site']['config']['html']}/category/$key/page/1/index.html", "{$twig_vars['site']['config']['html']}/category/$key/index.html");
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
	
	public function mkdir($path) {
		return is_writeable($path) || mkdir($path, 0777, true);
	}
}
?>
