//<![CDATA[
var TypechoComment = {
    dom : function (id) {
        return document.getElementById(id);
    },
    
    create : function (tag, attr) {
        var el = document.createElement(tag);
        
        for (var key in attr) {
            el.setAttribute(key, attr[key]);
        }
        
        return el;
    },

    reply : function (cid, coid) {
        var comment = this.dom(cid), parent = comment.parentNode,
            response = this.dom(document.getElementById(cid).getElementsByClassName('reply')[0].getElementsByTagName('a')[0].href.split('#')[1]), input = this.dom('comment-parent'),
            form = 'form' == response.tagName ? response : response.getElementsByTagName('form')[0],
            textarea = response.getElementsByTagName('textarea')[0];

        if (null == input) {
            input = this.create('input', {
                'type' : 'hidden',
                'name' : 'parent',
                'id'   : 'comment-parent'
            });

            form.appendChild(input);
        }
        
        input.setAttribute('value', coid);

        if (null == this.dom('comment-form-place-holder')) {
            var holder = this.create('div', {
                'id' : 'comment-form-place-holder'
            });
            
            response.parentNode.insertBefore(holder, response);
        }

        comment.appendChild(response);
        this.dom('cancel-comment-reply-link').style.display = '';
        
        if (null != textarea && 'text' == textarea.name) {
            textarea.focus();
        }
        
        return false;
    },

    cancelReply : function () {
        var response = this.dom('respond-post-183'),
        holder = this.dom('comment-form-place-holder'), input = this.dom('comment-parent');

        if (null != input) {
            input.parentNode.removeChild(input);
        }

        if (null == holder) {
            return true;
        }

        this.dom('cancel-comment-reply-link').style.display = 'none';
        holder.parentNode.insertBefore(response, holder);
        return false;
    }
}
//]]>