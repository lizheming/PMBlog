/*
Author: mg12
Author URI: http://www.neoease.com/
*/
(function() {

function $(id) {
	return document.getElementById(id);
}

function AddAttr(){
  jQuery("a").bind("focus",function(){if(this.blur)this.blur();});

  jQuery(".menu > li:first-child").addClass("first_menu");
  jQuery(".menu > li:first-child.current_page_item, .menu > li:first-child.current-cat, .menu > li:first-child.current-menu-item").addClass("first_menu_active").removeClass("first_menu");
  jQuery(".menu > li:last-child").addClass("last_menu");
  jQuery(".menu > li:last-child.current_page_item, .menu > li:last-child.current-cat, .menu > li:last-child.current-menu-item").addClass("last_menu_active").removeClass("last_menu");
  jQuery(".menu li ul li:has(ul)").addClass("parent_menu");

  //jQuery("#right_col ul > li:last-child").css({marginBottom:"0"});
  
  
  jQuery("ol.comment-list li.admin-comment ul.children").parent().removeClass('admin-comment');
  jQuery("#comment_area ol > li:even").addClass("even_comment");
  jQuery("#comment_area ol > li:odd").addClass("odd_comment");
  jQuery(".even_comment > .children > li").addClass("even_comment_children");
  jQuery(".odd_comment > .children > li").addClass("odd_comment_children");
  jQuery(".even_comment_children > .children > li").addClass("odd_comment_children");
  jQuery(".odd_comment_children > .children > li").addClass("even_comment_children");
  jQuery(".even_comment_children > .children > li").addClass("odd_comment_children");
  jQuery(".odd_comment_children > .children > li").addClass("even_comment_children");

  jQuery("#guest_info input,#comment_textarea textarea")
    .focus( function () { jQuery(this).css({borderColor:"#33a8e5"}) } )
    .blur( function () { jQuery(this).css({borderColor:"#ccc"}) } );

  jQuery("#tag_list ul").hide();
  jQuery(".search_tag").toggle(function(){
   jQuery(this).addClass("active_search_tag"); 
    }, function () {
   jQuery(this).removeClass("active_search_tag");
    });
  jQuery(".search_tag").click(function(){
    jQuery(this).next("#tag_list ul").slideToggle("400");
   });

  jQuery("#pingback_switch").click(function(){
    jQuery("#comment_switch").removeClass("comment_switch_active");
    jQuery(this).addClass("comment_switch_active");
    jQuery("#comment_area").slideUp();
    jQuery("#pingback_area").slideDown();
    return false;
  });

  jQuery("#comment_switch").click(function(){
    jQuery("#pingback_switch").removeClass("comment_switch_active");
    jQuery(this).addClass("comment_switch_active");
    jQuery("#pingback_area").slideUp();
    jQuery("#comment_area").slideDown();
    return false;
  });

  jQuery("#guest_info input,#comment_textarea textarea")
    .focus( function () { jQuery(this).css({borderColor:"#33a8e5"}) } )
    .blur( function () { jQuery(this).css({borderColor:"#ccc"}) } );

  jQuery("blockquote").append('<div class="quote_bottom"></div>');

  jQuery(".side_box:first").addClass("first_side_box");

}

function scroll_top_init(){
	jQuery('a[href*=#wrapper],a[href*=#respond]').click(function() {
		if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname) {
			var $target = jQuery(this.hash);
			$target = $target.length && $target || jQuery('[name=' + this.hash.slice(1) +']');
			if ($target.length) {
				var targetOffset = $target.offset().top;
				
				/* Stop scroll */
				if(document.addEventListener)
                  document.addEventListener(jQuery.browser.mozilla ? 'DOMMouseScroll' : 'mousewheel',scroll_stop,false); //Firefox
				else if(document.attachEvent)
                  document.attachEvent('onmousewheel',scroll_stop); //IE/Opera/Chrome/Safari -- Not tested
				
				jQuery('html,body').animate({ scrollTop: targetOffset }, 1000, 'quart');
				return false;
			}
		}
	});
}

function scroll_stop(){
  jQuery('html,body').stop(true);
  if(document.removeEventListener)
    document.removeEventListener(jQuery.browser.mozilla ? 'DOMMouseScroll' : 'mousewheel',scroll_stop,false); //Firefox
  else if(document.removeEvent)
    document.removeEvent('onmousewheel',scroll_stop); //IE/Opera/Chrome/Safari -- Not tested
}

function custom_scroll(node,func){
  /* Stop scroll */
  if(document.addEventListener)
    document.addEventListener(jQuery.browser.mozilla ? 'DOMMouseScroll' : 'mousewheel',scroll_stop,false); //Firefox
  else if(document.attachEvent)
    document.attachEvent('onmousewheel',scroll_stop); //IE/Opera/Chrome/Safari -- Not tested
      
    return jQuery('html').animate({ scrollTop: node.offsetTop - window.outerHeight / 4}, 1000, 'quart',func);
}

function externalLinks() {
 if (!document.getElementsByTagName) return;
 var anchors = document.getElementsByTagName("a");
 for (var i=0; i<anchors.length; i++) {
 var anchor = anchors[i];
 if (
 anchor.getAttribute("href") && (
 anchor.getAttribute("rel") == "external" ||
 anchor.getAttribute("rel") == "external nofollow" ||
 anchor.getAttribute("rel") == "nofollow external" )
 )
 anchor.target = "_blank";
 }
}

function reply_hack(cid, coid) {
  var I = jQuery, comment = this.dom(cid), parent = comment.parentNode,
  response = I('.respond')[0]; //this.dom('respond-post-31')
  var input = this.dom('comment-parent'),
  form = 'form' == response.tagName ? response : response.getElementsByTagName('form')[0],
  textarea = response.getElementsByTagName('textarea')[0];
  if (null == input) {
  input = this.create('input', {
  'type' : 'hidden',
  'name' : 'parent',
  'id' : 'comment-parent'
  });
  form.appendChild(input);
  }
  input.setAttribute('value', coid);
  if (null != this.dom('comment-form-place-holder')) {
    if(I(comment).children("div.respond").length != 0) {
      MGJS_CMT.custom_scroll(response,function(){textarea.focus()});
      return false;
    }
  } else {
    var holder = this.create('div', {
    'id' : 'comment-form-place-holder'
    });
    response.parentNode.insertBefore(holder, response);
  }
  I('#cancel-comment-reply-link').show();
  I(response).hide();
  comment.appendChild(response);
  if (null != textarea && 'text' == textarea.name) {
    I(response).slideDown(300);
    MGJS_CMT.custom_scroll(response,function(){textarea.focus()});
  };
  return false;
}

function cancelReply_hack() {
var response = jQuery('.respond')[0]; //this.dom('respond-post-31'),
var holder = this.dom('comment-form-place-holder'), input = this.dom('comment-parent');
if (null != input) {
input.parentNode.removeChild(input);
}
if (null == holder) {
return false;
}
jQuery(response).slideUp(1000,function(){
  jQuery('#cancel-comment-reply-link').hide();
  holder.parentNode.insertBefore(this, holder);
  jQuery(holder).remove();
  jQuery(response).slideDown();
});
return false;
}

function comment_act_init(){
  var I=jQuery,D=MGJS.$;
  I('ul.comment-act').fadeOut();
  
  // if comment was not closed
  if (I(".comment_form_wrapper").length)
  {
    // comment shortcut
    D("comment").onkeydown = function (moz_ev){var ev = null;if (window.event){ev = window.event;}else{ev = moz_ev;}if (ev != null && ev.ctrlKey && ev.keyCode == 13){D("submit_comment").click();}};
    D("commentform").onsubmit = function(){D('submit_comment').value = "Waitting...";D('submit_comment').setAttribute('style','background-image:url("usr/themes/mono/img/loading.gif");');};
    
    // commment effect
    I('li.comment div').mouseenter(function(){
      I(this).children('div.comment-meta ul.comment-act').stop(true,true).fadeIn(100);
    });
    I('li.comment div').mouseleave(function(){
      I(this).children('div.comment-meta ul.comment-act').stop(true,true).fadeOut(1000);
    });
  }
}

function init(doc) {
  //even and odd style
  AddAttr();
  // add target blank
  jQuery("a[rel='external'],a[rel='external nofollow']").click(function(){window.open(this.href);return false});
  // fix PageNav
  jQuery("ol.page-navigator li.current a").removeAttr("href");
  // init comment-act
  comment_act_init();
  // init return top
  scroll_top_init();
  // init alert info
  //alert_info_init();
  // load finsh
  jQuery("#loading").fadeOut(1000);
}

window['MGJS'] = {};
window['MGJS']['$'] = $;
window['MGJS_CMT'] = {};
//window['MGJS_CMT']['custom_scroll'] = custom_scroll;
//window['MGJS_CMT']['externalLinks'] = externalLinks;
window['MGJS_CMT']['custom_scroll'] = custom_scroll;
window['MGJS_CMT']['init'] = init;

// Hack TypechoComment
if (null != window['TypechoComment']) {
  window['TypechoComment']['reply'] = reply_hack;
  window['TypechoComment']['cancelReply'] = cancelReply_hack;
}
})();

jQuery.easing.quart = function (x, t, b, c, d) {
	return -c * ((t=t/d-1)*t*t*t - 1) + b;
};

$(function(){MGJS_CMT.init(this)});
