/**
 * @copyright 2006-2011 LanMediaService, Ltd.
 * @license    http://www.lanmediaservice.com/license/1_0.txt
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @version $Id: default.js 700 2011-06-10 08:40:53Z macondos $
 */
 
 if (window.Node && window.XMLSerializer) {
    if (window.Node && window.XMLSerializer && !window.opera) {
        Node.prototype.__defineGetter__('outerHTML', function() {
            var outstr = new XMLSerializer().serializeToString(this);
            if ((this.tagName=='TEXTAREA') && !(this.innerHTML)) {
                outstr += '</TEXTAREA>'
            }
            return outstr;
        });
    }
} 

function setCookie (name, value, expires, path, domain, secure) 
{
      document.cookie = name + "=" + escape(value) +
        ((expires) ? "; expires=" + expires : "") +
        ((path) ? "; path=" + path : "") +
        ((domain) ? "; domain=" + domain : "") +
        ((secure) ? "; secure" : "");
}

function setCaretPosition(ctrl, pos){
    if(ctrl.setSelectionRange)
    {
        ctrl.focus();
        ctrl.setSelectionRange(pos,pos);
    }
    else if (ctrl.createTextRange) {
        var range = ctrl.createTextRange();
        range.collapse(true);
        range.moveEnd('character', pos);
        range.moveStart('character', pos);
        range.select();
    }
}