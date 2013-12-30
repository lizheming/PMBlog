<?php
/**
 * A backstage plugin for PMBlog
 *
 * @author perichr
 * @version 0.0.1
 * @link https://github.com/lizheming/PMBlog
 */
class Backstage {
	function config_loaded($config) {
		if(isset($_GET['config'])) {
			//接收保存配置
			if(isset($_POST['name'])){
				$name = $_POST['name'];
				$file = Backstage::GetConfigFilePath($name);
				if(!copy($file, $file.'.backup')){
					Backstage::DieInfo('备份失败！');
				}
				include( PLUGINS_DIR . '/Backstage/config.php' );
				if($needBackup && !file_put_contents($file, $_POST['source'])){
					Backstage::DieInfo('保存失败！');
				}
				Backstage::DieInfo('保存成功！<br\>点击<input type="button" value="这里" onclick="window.location=\'./\'"/>重生成站点，或者点击<input type="button" value="这里" onclick="window.location=\'./?config\'"/> 返回插件页面。');
			}
			
			$name =trim($_GET['config']);
			//配置站点信息
			$file = Backstage::GetConfigFilePath($name);
			if(!file_exists($file)){
				Backstage::GoBack();
			}
			$content = file_get_contents($file);
			include_once PLUGINS_DIR."/Backstage/Backstage.html";
			die();
		}
	}
	function GetList ()
	{
		$ps = scandir (PLUGINS_DIR);
		$Editor = file_exists(PLUGINS_DIR . '/Editor/Editor.php') ? '<li><a href="?show" rel="external">管理文章</a></li><li><a href="?edit" rel="external">新建文章</a></li>' : '';
		$Plugins = '';
		if($ps){
			foreach( $ps as $p )
			{
				if($p != '.' && $p != '..'){
					$f = PLUGINS_DIR.DIRECTORY_SEPARATOR.$p;
					if(is_dir($f) && file_exists($f.'/config.php')){
						$Plugins .= '<li><a href="?config='.$p.'">'.$p.'</a></li>';
					}
				}
			}
		}
		$links = "<ul><li><a href=\"?build\" rel=\"external\">静态重建</a></li>{$Editor}</ul><ul><li><a href=\"?config\">配置站点信息</a>{$Plugins}</li></ul>";
		return $links;
	}
	function GoBack()
	{
		echo '<script>window.location=\'?config\'</script>';
	}
	function GetConfigFilePath ( $name = '' )
	{
		if($name == ''){
			return ROOT_DIR.'config.php';
		}else
		{
			return PLUGINS_DIR.'/'.$name.'/config.php';
		}
		# code...
	}
	function DieInfo ( $value='')
	{
		$html = "<!DOCTYPE html><html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/></head><body><div>{$value}</div></body></html>";
		die($html);
	}
}
