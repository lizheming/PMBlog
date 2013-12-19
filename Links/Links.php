<?php
/**
 * use $links variable in template to show links collection
 * How To use it?
 * 1. Looking at Links/links.md.
 * 2. Add LinkItem in it. One line just contain only one LinkItem like [LinkTitle](Linklink "LinkDescription")
 * 3. Organize your groups by lines using #GroupName, LinkItems after the groupname line(and before the next one) will be organized in one group.
 * 4. LinkItems without a defined group will be pushed into a 'default' group.
 * 5. Use {{links}} in your template, for example:
 * {% for item in links.groupname %}
 * 		{{item.title}}	{# LinkItem's title #}
 *		{{item.url}}	{# LinkItem's url #}
 *		{{item.description}}	{# LinkItem's description #}
 * {% endfor %}
 *
 * @author perichr
 * @link http://perichr.org/
 * @version 0.1.0
 */
class Links {
	private $link = array();

    public function config_loaded(&$site) {
    	$file = $site['config']['plugins'].'/Links/links.md';
    	if(!file_exists($file)) die('You must create links.md first in the '.$site['config']['plugins'].'/Links. links.example.md is a example file.');

		$groupname = 'default';
		$group = array();
    	$fileline = file($file);
    	
    	foreach( $fileline as $line )
    	{
    		$line = trim($line);
    		if($line == ''){
	    		continue;
    		}
    		if(stripos($line,'#') !== false){
	    		$link[$groupname] = $group;
				$groupname = trim(preg_replace('/\#([^\#]+)\#{0,}/','$1',$line));
				$group = array();				
    		}
    		if(preg_match('/^\[([^\]]*)\]\(([^\s]*)(.*)?\)/m', $line, $match) == 1){
	    		$group[] = array('title' => $match[1],
		    		'href' => $match[2],
		    		'description' => $match[3]
	    		);
    		}
    		
    	}
	    $link[$groupname] = $group;		
		$this->link = $link;
		return $link;
    }

    public function after_get_variables(&$variables)
    {
    	$variables['links'] = $this->link;
    }
}
