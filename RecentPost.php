<?php
/*
 * PMBlog plugin to show RecentPost
 * How To Use It?
 * After Add this plugin you can use {{RecentPost}} in the template to use it.
 * You can modify $recent_number in this plugin to change post's number.
 *
 * @author lizheming
 * @link http://github.com/lizheming
 * @version 0.1.0
 */
 
class RecentPost {
	public $recent_number = 10;
	private $recent_post;

	function after_get_contents(&$post) {
		$recent_post = array();
		for($i=0;$i<$this->recent_number;$i++) {
			if(!isset($post['post'][$i])) break;
			$recent_post[$i] = $post['post'][$i];
			unset($recent_post[$i]['abstract']);
			unset($recent_post[$i]['content']);
		}
		$this->recent_post = $recent_post;
	}

	function after_get_variables(&$variables) 
	{
		$variables['RecentPost'] = $this->recent_post;
	}
}