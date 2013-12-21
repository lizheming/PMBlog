<?php
/**
 * PMBlog plugin to show a Archive in your blog
 * How To Use It? Here is an example:
 *
 *    	<div class="car-container car-collapse">
 *   		<ul class="car-list">
 *  		{% for year in get_archive_years() %}
 *   			{% for month in get_archive_months(year) %}
 *				<li>
 *					<span class="car-yearmonth">{{year}}年{{month}}月</span>
 *    				<ul class="car-monthlisting">
 *    				{% for day in get_archive_days(year, month) %}
 *    					{% for post in get_archive(year, month, day) %}
 *    					<li>{{day}}: <a href="{{post.url}}">{{post.title}}</a></li>
 *    					{% endfor %}
 *    				{% endfor %}
 *    				</ul>
 *    			</li>
 *    			{% endfor %}
 *    		{% endfor %}
 *    		</ul>
 *    	</div>
 *
 * @author lizheming
 * @link http://github.com/lizheming
 * @version 0.1.1
 */
 
class Archive {
	private $archive;
	public function after_get_contents(&$data) {
		$archive = array();
		foreach($data['post'] as $key => $post) {
			$date = strtotime($post['date']);
			$title = $post['title'];
			$url = $post['url'];
			$archive[date('Y',$date)][date('n', $date)][date('j', $date)][] = array('title' => $title, 'url' => $url);	
		}
		$this->archive = $archive;

	}
	public function twig_loaded(&$variables, &$twig) {
		/**
		 * 返回日志所有年数
		 *
		 * @return array()
		 */
		$years = new Twig_SimpleFunction("get_archive_years", function() {
			return array_keys($this->archive);
		});

		/**
		 * 返回某年的日志所有的月份
		 *
		 * @param string 具体某年
		 * @return array()
		 */
		$month = new Twig_SimpleFunction("get_archive_months", function($year) {
			$archive = $this->archive;
			return isset($archive[$year]) ? array_keys($archive[$year]) : array();
		});

		/**
		 * 返回某年某月的所有日志的天
		 *
		 * @param string 具体某年
		 * @param string 具体某月
		 * @return array()
		 */
		$days = new Twig_SimpleFunction("get_archive_days", function($year, $month) {
			$archive = $this->archive;
			return isset($archive[$year][$month]) ? array_keys($archive[$year][$month]) : array();
		});

		/**
		 * 返回某年某月某日的所有日志
		 * 
		 * @param string 具体某年
		 * @param string 具体某月
		 * @param string 具体某日
		 * @return array()
		 */
		$posts = new Twig_SimpleFunction("get_archive", function($year, $month, $day) {
			$archive = $this->archive;
			return isset($archive[$year][$month][$day]) ? $archive[$year][$month][$day] : array();
		});
		$twig->addFunction($years);
		$twig->addFunction($month);
		$twig->addFunction($days);
		$twig->addFunction($posts);
	}
}