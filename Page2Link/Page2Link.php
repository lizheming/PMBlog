<?php
/**
 * use $link variable in template to show friend_link
 * How To use it?
 * 1. Add LinkItem in the Page2Link/links.html. One line just contain only one LinkItem like [LinkTitle](Linklink "LinkDescription")
 * 2. Use {{link}} in your template, for example:
 * {% for item in link %}
 * 		{{item.title}}	{# LinkItem's title #}
 *		{{item.url}}	{# LinkItem's url #}
 *		{{item.description}}	{# LinkItem's description #}
 * {% endfor %}
 *
 * @author lizheming
 * @link http://github.com/lizheming
 * @version 0.1.0
 */
class Page2Link {
	private $link;

    public function config_loaded(&$site) {
    	$file = $site['config']['plugins'].'/Page2Link/links.html';
    	if(!file_exists($file)) die('You must create links.html first in the '.$site['config']['plugins'].'/Page2Link. links.example.html is a example file.');
    	$text = file_get_contents($file);
    	$preg = '/^\[(.*?)\]\((.*?)\)/m';
    	preg_match_all($preg, $text, $match);
		$link = array();
		foreach($match[2] as $k => $v)
		{
			if(preg_match('/\'/', $v)) {
				$t = explode(' \'', $v);
				$t[1] = str_replace("'", "", $t[1]);
			} elseif(preg_match('/ "/', $v)) {
				$t = explode(' "', $v);
				$t[1] = str_replace("\"", "", $t[1]);
			} else {
				$t = array($v, '');
			}
			$link[] = array('title' => $match[1][$k], 'url' => $t[0], 'description' => $t[1]);
		}
		$this->link = $link;
		return $link;
    }

    public function after_get_variables(&$variables)
    {
    	$variables['link'] = $this->link;
    }
}
