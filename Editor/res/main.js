function submitCheck() {
    var slug = document.querySelector("#slug");
    if(!slug.value || /[\<\>\*\?]/gi.test(slug.value)) {
        alert('文件名留空或者使用了非法字符，请重新填写！');
        slug.focus();
        return false;
    }
    return true;
}
(function createSlug() {
    var slug = document.querySelector("#slug");
    if(slug.value=="") slug.value = new Date().getTime();
    var justifySlug = document.createElement("div");
    justifySlug.style.display = "none";
    justifySlug.style.padding = "2px";
    justifySlug.style.width = "auto";
    justifySlug.style.fontSize = "18px";
    slug.parentNode.appendChild(justifySlug);
    justifySlugWidth();
    slug.addEventListener("input", justifySlugWidth, false);
})();
function justifySlugWidth() {
    var slug = document.querySelector("#slug");
    var justifySlug = slug.nextElementSibling.nextElementSibling;
    var originalWidth = slug.clientWidth;
    var html = slug.value.replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/'/g, '&#039;')
            .replace(/"/g, '&quot;')
            .replace(/ /g, '&nbsp;')
            .replace(/((&nbsp;)*)&nbsp;/g, '$1 ')
            .replace(/\n/g, '<br />')
            .replace(/<br \/>[ ]*$/, '<br />-')
            .replace(/<br \/> /g, '<br />&nbsp;');
    justifySlug.style.minWidth = html.length > 0 ? 'inherit' : originalWidth;
    justifySlug.innerHTML = html;
    justifySlug.style.display = "inline";
    slug.style.width = justifySlug.offsetWidth+'px';
    justifySlug.style.display = "none";
}
justifySlugWidth();
function addTag(inp) {
    if(inp.value == "") return false;
    var o = inp.parentNode.outerHTML;
    var n = document.createElement("li");
    n.className = "attached";
    n.innerHTML = '<span>{{tag}}</span><a href="#" class="i-cancel" title="删除">✖</a><input type="hidden" name="tags[]" value="{{tag}}" role="form-tags">'.replace(/{{tag}}/g, inp.value);
    inp.parentNode.parentNode.insertBefore(n, inp.parentNode);
    inp.value = "";
    inp.focus();
    return false;
}
function addCategory(inp) {
    if(inp.value == "") return false;
    var o = inp.parentNode.outerHTML;
    var n = document.createElement("li");
    n.className = "attached";
    n.innerHTML = '<span>{{category}}</span><a href="#" class="i-cancel" title="删除">✖</a><input type="hidden" name="category[]" value="{{category}}" role="form-categories">'.replace(/{{category}}/g, inp.value);
    inp.parentNode.parentNode.insertBefore(n, inp.parentNode);
    inp.value = "";
    inp.focus();
    return false;    
}
(function() {
    var converter = Markdown.getSanitizingConverter();
    converter.hooks.chain("preBlockGamut", function (text, rbg) {
        return text.replace(/^ {0,3}""" *\n((?:.*?\n)+?) {0,3}""" *$/gm, function (whole, inner) {
            return "<blockquote>" + rbg(inner) + "</blockquote>\n"
        })
    });
    var editor = new Markdown.Editor(converter);
    editor.run()
})();
function autoSave() {
    localStorage.PMBLOG_POST = JSON.stringify([].slice.call(document.querySelectorAll('*[role^="form-"]')).map(function(item){return {role:item.getAttribute('role'),value:item.value}}));
    var now = new Date(), tt = "{y}/{m}/{d}-{h}:{i}";
    var text = "自动保存于" + tt.replace("{y}", now.getFullYear())
      .replace("{m}", now.getMonth()+1)
      .replace("{d}", now.getDate())
      .replace("{h}", now.getHours())
      .replace("{i}", now.getMinutes());
    var autosave = document.querySelector("#auto-save");
    autosave.innerHTML = text;
    autosave.style.display = "inline-block";
    setTimeout(function(){autosave.style.display="none"}, 1500);
}
(function() {
    var post = JSON.parse(localStorage.PMBLOG_POST || '[]');
    if(post.length == 0) return false;
    post.forEach(function(item) {
        if(item.role=="form-tags") {
            if(document.querySelectorAll(".tag-list .attached").length>0) return false;
            var inp = document.querySelector("#tags");
            inp.value = item.value;
            addTag(inp);
        } else if(item.role == "form-categories") {
            if(document.querySelectorAll(".category-list .attached").length>0) return false;
            var inp = document.querySelector("#categories");
            inp.value = item.value;
            addCategory(inp);
        } else {
            var obj = document.querySelector('*[role="'+item.role+'"]');
            if(obj.value == "") obj.value = item.value;
        }
    })
})();
setInterval(autoSave, 15000);
function prettify() {
    [].slice.call(document.querySelectorAll('#wmd-preview pre')).forEach(function(pre) {
        if(pre.className.indexOf("prettyprint")==-1)
            pre.className += " prettyprint";
    })
    prettyPrint();
}
setInterval(prettify, 10);
function slugSelect() {
    this.select();
    this.removeEventListener("focus", slugSelect, false);
}
document.querySelector("#slug").addEventListener("focus", slugSelect, false);
document.querySelector(".tag-list").addEventListener("click", function(e) {
    if(e.target.className.indexOf("i-cancel")!=-1) e.target.parentNode.remove();
}, false);
document.querySelector(".category-list").addEventListener("click", function(e) {
    if(e.target.className.indexOf("i-cancel")!=-1) e.target.parentNode.remove();
}, false);
document.querySelector("#tags").addEventListener("keypress", function(e) {
    if(e.keyCode == 13) return e.preventDefault() || addTag(this);
}, false);
document.querySelector(".tag-list .add-btn").addEventListener("click", function(e) {
    return e.preventDefault() || addTag(this.previousElementSibling);
})
document.querySelector("#categories").addEventListener("keypress", function(e) {
    if(e.keyCode == 13) return e.preventDefault() || addCategory(this);
}, false);
document.querySelector(".category-list .add-btn").addEventListener("click", function(e) {
    return e.preventDefault() || addCategory(this.previousElementSibling);
})
window.onresize = function() {
    var nh = window.innerHeight - [].slice.call(document.querySelectorAll('.row')).filter(function(item){return item.id.indexOf("writer-body")==-1}).reduce(function(a,b){return a + b.clientHeight}, 0);
    document.querySelector("#wmd-preview").style.height = nh+"px";
    document.querySelector(".write-content").style.height = nh+"px";
    document.querySelector("#writer-body textarea").style.height = nh - 70 +"px";
}
window.onresize();
// 粘贴上传图片
document.querySelector("#wmd-input").addEventListener('paste', function(e) {
    var clipboard = e.clipboardData;
    for(var i=0,len=clipboard.items.length; i<len; i++) {
        if(clipboard.items[i].kind == 'file' || clipboard.items[i].type.indexOf('image') > -1) {
            var imageFile = clipboard.items[i].getAsFile();
            var form = new FormData;
            form.append('mypic', imageFile);
            $.ajax({
                url: "//iphoto.sinaapp.com/upload.php",
                type: "POST",
                data: form,
                processData: false,
                contentType: false,
                success: function(d) {
                    var url = JSON.parse(d).url;
                    document.querySelector("#wmd-image-button").click();
                    setTimeout(function(){
                        document.querySelector(".wmd-prompt-dialog input[type=text]").value = url;
                        document.querySelector(".wmd-prompt-dialog input[type=button]").click();
                    }, 500);
                }
            })
            e.preventDefault();
        }
    }
});
// 拖拽上传
document.addEventListener("dragleave", function(e){e.preventDefault()});
document.addEventListener("drop", function(e){e.preventDefault()});
document.addEventListener("dragenter", function(e){e.preventDefault()});
document.addEventListener("dragover", function(e){e.preventDefault()});
document.querySelector("#wmd-input").addEventListener("drop", function(e) {
    e.preventDefault();
    var FileList = e.dataTransfer.files;
    if(FileList.length != 1) return false;
    var file = FileList[0];
    if(file.type.indexOf('image') === -1) return false;

    var form = new FormData;
    form.append('mypic', file);
    $.ajax({
        url: "//iphoto.sinaapp.com/upload.php",
        type: "POST",
        data: form,
        processData: false,
        contentType: false,
        success: function(d) {
            var url = JSON.parse(d).url;
            document.querySelector("#wmd-image-button").click();
            setTimeout(function(){
                document.querySelector(".wmd-prompt-dialog input[type=text]").value = url;
                document.querySelector(".wmd-prompt-dialog input[type=button]").click();
            }, 500);
        }
    })

})