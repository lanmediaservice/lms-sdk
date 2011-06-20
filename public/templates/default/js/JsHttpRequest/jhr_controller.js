/**
 * @copyright 2006-2011 LanMediaService, Ltd.
 * @license    http://www.lanmediaservice.com/license/1_0.txt
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @version $Id: jhr_controller.js 700 2011-06-10 08:40:53Z macondos $
 */
 
 JHRController = function(){
    var _t = this;
    _t.loadings_counter = 0;
    _t.loadings = {};
    _t.time_quant = 2000;
    _t.default_timeout = 30000;
    _t.created = false;
    if ('undefined' != typeof LMS) {
        var s = LMS.i18n.translate('Loading...');
    } else {
        var s = 'Loading...';
    }
    _t.content = '<span style="background-color:rgb(205,68,65); color:white">' + s + '</span>';
    _t.parent_domid = null;
    
    _t.DebugMessenger = function(text){
        alert(text);
    }
    
    _t.SysMessenger = function(text){
        alert(text);
    }
    
    _t.UserMessenger = function(text){
        alert(text);
    }
    
    function _setAttribute(obj,attr,value){
        if (obj.setAttribute && (attr!='innerHTML')) obj.setAttribute(attr, value)
            else obj[attr] = value;
    }
    
    _t.create = function(){
        if (!_t.parent_domid){
            _t.parent_domid = 'JHRControllerLoaderBox';
            if (!document.getElementById(_t.parent_domid)){
                var loaderbox = document.createElement('DIV');
                _setAttribute(loaderbox,'innerHTML',_t.content);
                cssStyles= 'position:fixed; z-index:1100; top:0px; left:0px; display:none;';
                if (loaderbox.style) loaderbox.style.cssText = cssStyles;
                    else  _setAttribute(loaderbox,'style',cssStyles);
                _setAttribute(loaderbox,'id',_t.parent_domid);
                if (document.getElementsByTagName("body").length){
                    body = document.getElementsByTagName("body")[0]
                    body.appendChild(loaderbox);
                } else {
                    _t.parent_domid = null;
                    return false;
            }
            }
        } else {
            document.getElementById(_t.parent_domid).innerHTML = _t.content;
        }
        _t.created = true;
    }
    
    _t.refresh = function(){
        if (!_t.created) _t.create();
        el = document.getElementById(_t.parent_domid)
        if (el){
            if (_t.loadings_counter>0) {
                el.style.display = "block";
                //if klayers loaded
                if (typeof(getScrollY)=='function'){
                    el.style.top = getScrollY()+"px";
                }
            }
            else{
                el.style.display = "none";
            }
        }
    }
        
    _t.beginLoad = function(counter, timeout){
        _t.loadings_counter++;
        var date = new Date()
        _t.loadings[counter] = new Date().getTime() + timeout;
        _t.refresh();
    }
        
    _t.endLoad = function(counter){
        _t.loadings_counter--;
        delete _t.loadings[counter];
        _t.refresh();
    }
    
    _t.timer = function(){
        //
        if (_t.created){
            var cur_time = new Date().getTime();
            for (var i in _t.loadings){
                if (cur_time > _t.loadings[i]){
                    delete _t.loadings[i];
                    _t.SysMessenger((LMS && LMS.i18n)? LMS.i18n.translate('Timeout last request') : 'Timeout last request');
                    _t.loadings_counter--;
                    _t.refresh();
                }
            }
            
        }
        setTimeout(_t.timer,_t.time_quant);
    }
    _t.timer();
        
}

JsHttpRequest.JHRController = new JHRController();

JsHttpRequest.query = function(url, content, onready, nocache, timeout) {
    var gr = this;
    if (!timeout) timeout = gr.JHRController.default_timeout;
    var query_id = JsHttpRequest.COUNT;
    gr.JHRController.beginLoad(query_id, timeout);
    var req = new this();
    req.caching = !nocache;
    req.onreadystatechange = function() {
        if (req.readyState == 4) {
            gr.JHRController.endLoad(query_id);
            onready(req.responseJS, req.responseText);
        }
    }
    var method = null;
    if (url.match(/^((\w+)\.)?(GET|POST)\s+(.*)/i)) {
        req.loader = RegExp.$2? RegExp.$2 : null;
        method = RegExp.$3;
        url = RegExp.$4;
    }
    req.open(method, url, true);
    req.send(content);
}
