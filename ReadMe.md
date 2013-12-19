## PMBlog ##

PMBlog是一个类似Jekyll/Octopress的PHP版静态博客生成程序。  
具有文章/页面的生成，自定义主题，自定义插件等基本功能。

关于自定义主题，这里想要感谢Twig。之前采用的是自己写的模板引擎，不仅自己麻烦，而且做主题也很麻烦，因为又需要记一套语法。采用了开源的Twig模板引（语法来自Django和Jinjia，和Jekyll，Octopress的语法是类似的）擎之后，开发主题就变得非常的得心应手了。

关于自定义插件，这里想要感谢Pico。Pico是一款静态CMS程序，参考了它的代码，我才能将PMBlog的插件系统给开发出来，这里真是非常感谢！

## 必要条件 ##

安装前请确保你的PHP >= 5.2.4 

## 安装方法 ##

复制`config.example.php`为`config.php`并按照注释修改里面的配置变量，随后运行`index.php`即可实现博客生成。

由于有些插件也需要配置文件，所以你要看一下页面的提示信息，都会有相应的提示的。

[Installation](https://github.com/lizheming/PMBlog/wiki#installation)

## 站点示例 ##

[怡红院落 - http://blog.imnerd.org](http://blog.imnerd.org)

[Phenoland - http://Phenoland.com](http://Phenoland.com)

[PMBlog - http://lizheming.github.io/PMBlog](http://lizheming.github.io/PMBlog)

## WIKI ##

[写作规则](https://github.com/lizheming/PMBlog/wiki/%E5%86%99%E4%BD%9C%E8%A7%84%E5%88%99)

[如何自定义模板/主题](https://github.com/lizheming/PMBlog/wiki/%E5%A6%82%E4%BD%95%E8%87%AA%E5%AE%9A%E4%B9%89%E6%A8%A1%E6%9D%BF)

[默认变量](https://github.com/lizheming/PMBlog/wiki/%E9%BB%98%E8%AE%A4%E5%8F%98%E9%87%8F)

[数据类型及字段](https://github.com/lizheming/PMBlog/wiki/%E6%95%B0%E6%8D%AE%E7%B1%BB%E5%9E%8B%E5%8F%8A%E5%AD%97%E6%AE%B5)