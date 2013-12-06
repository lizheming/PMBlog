<?php
/**
 * PMBlog plugin to show a Archive in your blog
 * How To Use It?
 * After Add this plugin you can use {{Archive}} in the template to use it.
 * 
 * @author lizheming
 * @link http://github.com/lizheming
 * @version 0.1.0
 */
 
class Archive {
	private $archive;
	public function after_get_contents(&$data) {
		foreach($data['post'] as $key => $post) {
			$date = strtotime($post['date']);
			$title = $post['title'];
			$url = $post['url'];
			$arch[date('Y',$date)][date('n', $date)][date('j', $date)][] = array('title' => $title, 'url' => $url);	
		}
		$Archive = '<div class="car-container car-collapse"><a href="#" class="car-toggler">展开全部</a><ul class="car-list">';
		foreach($arch as $kyear => $year) {
			foreach($year as $kmonth => $month) {
				$Archive .= '<li><span class="car-yearmonth">'.$kmonth.'月 '.$kyear.' <span title="Post Count">('.$this->count($month).')</span></span>';
				$Archive .= '<ul class="car-monthlisting">';
				foreach($month as $kday => $day) {
					foreach($day as $item) {
						$Archive .= '<li>'.$kday.': <a href="'.$item['url'].'">'.$item['title'].'</a></li>';
					}
				}
				$Archive .= '</ul>';
				$Archive .= '</li>';
			}
		}
		$Archive .= "</ul></div>
		<style type=\"text/css\">.car-collapse .car-yearmonth { cursor: s-resize; }.car-monthlisting {overflow:hidden;} </style>
		<script type=\"text/javascript\">
		window.onload = function() {
			var collapse = document.getElementsByClassName('car-collapse')[0];
			var monthlisting = collapse.getElementsByClassName('car-monthlisting');
			var yearmonth = collapse.getElementsByClassName('car-yearmonth');
			for(i=1,l=monthlisting.length;i<l;i++)	monthlisting[i].style.display = 'none';
			monthlisting[0].style.display = 'block';
			for(i=0,l=yearmonth.length;i<l;i++) {
				yearmonth[i].onclick = function() {
					var obj = this.nextSibling, display = obj.style.display;
					if(display == 'block') {
						obj.style.height = obj.scrollHeight;
						var hide = setInterval(function() {
							obj.style.height = (parseInt(obj.style.height) - 10)+'px';
							if(parseInt(obj.style.height) <= 10) {
								obj.style.height = 'auto';
								obj.style.display = 'none';
								clearInterval(hide);
							}
						}, 10);
					} else {
						obj.style.height = '0';
						obj.style.display = 'block';
						var items = obj.getElementsByTagName('li'), Height = items.length*items[0].scrollHeight;
						var show = setInterval(function(){
							obj.style.height = (parseInt(obj.style.height) + 10)+'px';
							if(parseInt(obj.style.height)>Height) {
								clearInterval(show);
							}
						}, 10);
					}
				}
			}
			
			document.getElementsByClassName('car-toggler')[0].onclick = function() {
				if(this.innerText == '展开全部') {
					this.innerText = '折叠全部';
					for(i=0,l=monthlisting.length;i<l;i++)	monthlisting[i].style.display = 'block';
				} else {
					this.innerText = '展开全部';
					for(i=0,l=monthlisting.length;i<l;i++)	monthlisting[i].style.display = 'none';
				}
			}	
		}
		</script>";

		$this->archive = $Archive;
	}
	public function after_get_variables(&$variables) {
		$variables['Archive'] = $this->archive;
	}
	public function count($array) {
		$count = 0;
		foreach($array as $item)
			$count += count($item);
		return $count;
	}
}