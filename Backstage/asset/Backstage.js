'use strict';
(function(){
var source = byId('source').value,
D = /^define\('([^']+)',\s{0,}'([^']+)'\)[^\/\n]*(\/\/([^\n]*))*$/igm,
C = /^\$config\['([^']+)'\]\s{0,}=\s{0,}(\d+|true|false|('[^']*'))[^\/\n]*(\/\/([^\n]*))*$/igm,
P = /^\s{0,}\$([_a-zA-Z][A-Za-z0-9_]+)\s{0,}=\s{0,}(\d+|true|false|('[^']*'))[^\/\n]*(\/\/([^\n]*))*$/igm,
VI = /^\d+$/im,
VB = /^true|false$/im

createButtons()

addListener(byId('form'), 'submit',  function (ev) {
	if(byId('sourceGroup').style.display == 'none') save()
	/*
	ev = ev || window.event; // Event对象
	if (ev.preventDefault) { // 标准浏览器
		ev.preventDefault();
	} else { // IE浏览器
		window.event.returnValue = false;
	}
	*/
})

function createButtons(){
	var $i = newEl('input')
	setAttr($i, 'id', 'toggle')
	setAttr($i, 'type', 'button')
	prependEl(byId('buttons'), $i)
	$i.onclick = toggle
	toggle()
	var $fss = queryAll('fieldset')
	if($fss.length < 2) {
		toggle()
		$i.style.display = 'none'
	}
}
function toggle() {
	var sourceMode = byId('sourceGroup').style.display != 'none'
	sourceMode ? get() : save()
	setAttr(byId('toggle'), 'value', sourceMode ? '源码模式' : '常规模式' )
	each(queryAll('fieldset'), function ($fs) {
		if($fs.id == 'sourceGroup'){
			$fs.style.display = sourceMode ? 'none' : 'block'
		}else{
			$fs.style.display = sourceMode ? 'block' : 'none'
		}
	})
}
//获取fieldset
function setFieldset(id, data, text) {
	if(data.length == 0) return
	id += 'Group'
	var $f = byId(id), $l = newEl('legend')
	if(!$f){
		$f = newEl('fieldset')
		setAttr($f, 'id', id)
		beforeEl(byId('buttons'), $f)
	}
	$f.innerHTML =''
	addText($l, text)
	appendEl($f, $l)
	each(data, function (opt) {
		appendEl($f, createItem(opt))
	})
	return $f
}
//分析赋值
function getAssign() {
	var retval = [],source = byId('source').value, result
	P.lastIndex = 0
	while ((result = P.exec(source)) != null)  {
		var opt = {}, value = result[2], description = result[5]
		opt.key = result[1]
		opt.description = description
		if(VI.test(value)) opt.type = 'number'
		else if(VB.test(value)) opt.type = 'boolean'
		else {
			opt.type = 'string'
			value = value.replace(/(^')|('$)/g,'')
		}
		opt.value = value
		retval.push(opt)
	}
	setFieldset('assign', retval, '配置内容')
}
//分析define数据
function getDefine() {
	var retval = [],source = byId('source').value, result
	D.lastIndex = 0
	while ((result = D.exec(source)) != null)  {
		retval.push({'key' : result[1], 'value' : result[2], 'type' : 'string', 'description' :result[4]} )
	}
	setFieldset('define', retval, '程序定义')
}
//分析config数据
function getConfig() {
	var retval = [],source = byId('source').value, result
	C.lastIndex = 0
	while ((result = C.exec(source)) != null)  {
		var opt = {}, value = result[2], description = result[5]
		opt.key = result[1]
		opt.description = description
		if(VI.test(value)) opt.type = 'number'
		else if(VB.test(value)) opt.type = 'boolean'
		else {
			opt.type = 'string'
			value = value.replace(/(^')|('$)/g,'')
		}
		opt.value = value
		retval.push(opt)
	}
	setFieldset('config', retval, '站点信息')
}
function get() {
	getDefine()
	getConfig()
	getAssign()
	//return false
}
function save() {
	saveDefine()
	saveConfig()
	saveAssign()
	//return false
}
function saveDefine() {
	if(!byId('defineGroup')) return
	var defs = queryAll('#defineGroup input'), source = byId('source').value
	each(defs, function($i) {
		var name = $i.name.substring(7)
		var r = "^define\\('" + name + "',\\s{0,}'([^']+)'\\)"
		r = new RegExp(r, 'img')
		source = source.replace(r, "define('" + name + "', '" + $i.value +"')")
		
	})
	byId('source').value = source
}
function saveConfig() {
	if(!byId('configGroup')) return
	var defs = queryAll('#configGroup input'), source = byId('source').value
	each(defs, function($i) {
		var name = $i.name.substring(7)
		var r = "^\\$config\\['" + name + "'\\]\\s{0,}=\\s{0,}(\\d+|true|false|('[^']*'))"
		//console.log(r);
		r = new RegExp(r, 'img')
		//console.log(r.exec(source));
		var value = $i.value;
		if($i.getAttribute('data-type') == 'string') value = '\'' + value + '\''
		source = source.replace(r, "$config['" + name + "'] = " + value)
		
	})
	byId('source').value = source
}
function saveAssign() {
	if(!byId('assignGroup')) return
	var defs = queryAll('#assignGroup input'), source = byId('source').value
	each(defs, function($i) {
		var name = $i.name.substring(7)
		var r = "^\\s{0,}\\$" + name + "\\s{0,}=\\s{0,}(\\d+|true|false|('[^']*'))"
		//console.log(r);
		r = new RegExp(r, 'img')
		//console.log(r.exec(source));
		var value = $i.value;
		if($i.getAttribute('data-type') == 'string') value = '\'' + value + '\''
		source = source.replace(r, "$" + name + " = " + value)
	})
	byId('source').value = source
}

//构造选项编辑器
function createItem(key, value, type, description) {
	if(typeof(key) == 'object'){
		value = key.value
		type = key.type
		description = key.description
		key = key.key
	}
	var $p = newEl('p'), $l = newEl('label'), $i = newEl('input'), $e = newEl('em'),
		name = 'config_' + key
	setAttr($l, 'for', name)
	setAttr($l, 'title', type)
	addText($l, key)
	setAttr($i, 'type', 'text')
	setAttr($i, 'id', name)
	setAttr($i, 'name', name)
	setAttr($i, 'data-type', type)
	setAttr($i, 'value', value)
	if(type == 'number') setAttr($i, 'pattern', '\\d+')
	if(type == 'boolean') setAttr($i, 'pattern', 'true|false')
	addText($e, description || '')
	appendEl($p, $l)
	appendEl($p, $i)
	appendEl($p, $e)
	return $p
}




//sweet suger...
function query(name) {return document.querySelector(name)}
function queryAll(name) {return document.querySelectorAll(name)}
function byId(name) {return document.getElementById(name)}
function byName(name) {return document.getElementsByName(name)}
function byTag(name) {return document.getElementsByTagName(name)}
function newEl(name) {return document.createElement(name)}
function newText(text) {return document.createTextNode(text)}
function addText(target, text) {target.appendChild(newText(text))}
function setAttr(target, key, value) {target.setAttribute(key, value)}
function appendEl(target, child) {target.appendChild(child)}
function prependEl(target, child){
	if(target.hasChildNodes()) target.insertBefore(child,target.firstChild)
	else target.appendChild(child)
} 
function beforeEl(target, child) {target.parentNode.insertBefore(child, target)}
function afterEl(target, child) {
	var parent = target.parentNode
	if(parent.lastChild === target) parent.appendChild(child)
	else parent.insertBefore(child, target.nextSibling)
}
function addListener(element,e,fn) {
	if(element.addEventListener) element.addEventListener(e,fn,false)
	else element.attachEvent("on" + e,fn)
}
function each(obj, iterator, context) {
	var nativeForEach = Array.prototype.forEach, breaker = {}
	if (obj == null) return
	if (nativeForEach && obj.forEach === nativeForEach) {
		obj.forEach(iterator, context)
	} else if (obj.length === +obj.length) {
		for (var i = 0, length = obj.length; i < length; i++) {
			if (iterator.call(context, obj[i], i, obj) === breaker) return
		}
	} else {
		var keys = _.keys(obj);
		for (var i = 0, length = keys.length; i < length; i++) {
			if (iterator.call(context, obj[keys[i]], keys[i], obj) === breaker) return
		}
	}
}
function trim(str) {return str.replace(/(^[\s\t\n]*)|([\s\t\n]*$)/g,'')}

})();
