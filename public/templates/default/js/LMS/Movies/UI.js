/**
 * @copyright 2006-2011 LanMediaService, Ltd.
 * @license    http://www.lanmediaservice.com/license/1_0.txt
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @version $Id: UI.js 700 2011-06-10 08:40:53Z macondos $
 */
 
if (!LMS.Movies) {
    LMS.Movies = {};
}

LMS.Movies.UI = {
    frameWidthRule: null,
    sidebarWidth: 180,
    maxFrames: 8,
    urlsLog: [],
    getCssRule: function(rule)
    {
	for (var i=0; i<document.styleSheets.length; i++) {
            var styleSheet = document.styleSheets[i];
            var theRules = new Array();
            if (styleSheet.cssRules) {
                theRules = styleSheet.cssRules
            } else {
                theRules = styleSheet.rules
            }
            for (var j=0; j<theRules.length; j++) {
                if (theRules[j].selectorText==rule) {
                    return theRules[j];
                }
            }
        }
        return null;
    },
    
    init: function()
    {
        this.initHistory();
        this.urlsLog.push(window.location.href);
    },
        
    initHistory: function()
    {
        if (window.history && window.history.pushState) {
            window.history.replaceState({
                path: this.pathFromURL(location.pathname)
            }, "");
            this.setClickHandlers();
            if ($('body').hasClassName("catalog-view")) {
                this.initCatalogFrame();
            } else if($('body').hasClassName("movie-view")) {
                this.initMovieFrame();
            }
            document.observe("dom:loaded", function() {
                window.ui.initHistory();
            });
            window.onpopstate = this.popStateHandler.bind(this);
            
        }
    },
    popStateHandler: function (a) {
        a.state && this.slideTo(location.pathname)
    },
    frameForPath: function (path) {
        var f = $$('#content_frames .frame[data-path="' + path + '"]');
        return f.length? f[0] : false;
    },
    
    frameForURL: function (url) {
        return this.frameForPath(this.pathFromURL(url))
    },
    
    pathFromURL: function (url) {
        var r = new RegExp("^\\w+://[^/]+", "i");
        return url.replace(r, "");
    },

    setClickHandlers: function()
    {
        if (!this.clickHandlerBinded) {
            this.clickHandlerBinded = this.clickHandler.bind(this);
        }
        var a = $$(".catalog a", ".catalog-mini a", ".paginator a", ".person .movies a");
        for (var i=0; i<a.length; i++) {
            if (!a[i].onclick) {
                a[i].onclick = this.clickHandlerBinded;
            }
        }
        a = null;
    },
    
    _selectAfter: function(elements, el)
    {
        var result = [];
        var start = elements.length;
        for (var i=0; i<elements.length; i++) {
            if (elements[i]==el) {
                start = i + 1;
                break;
            }
        }
        for (var i=start; i<elements.length; i++) {
            result.push(elements[i]);
        }
        return result;
    },
    
    _selectBefore: function(elements, el)
    {
        var result = [];
        for (var i=0; i<elements.length; i++) {
            if (elements[i]==el) {
                break;
            }
            result.push(elements[i]);
        }
        return result;
    },
    
    _saveCatalogState: function(catalog, toolbar, navigator, paginator, catalogMimi)
    {
        if (catalog.stateSaved) {
            return true;
        }

        catalog.toolbar = toolbar;
        catalog.navigator = navigator;
        catalog.paginator = paginator;
        catalog.catalogMini = catalogMimi;

        catalog.popHandler = function(){
            var currToolbar = $('toolbar');
            var currNavigator = $('nav');
            var currCatalogMini = $$('.catalog-mini')[0];
            var currPaginator = $$('.paginator')[0];

            currToolbar.parentNode.replaceChild(this.toolbar, currToolbar);
            currNavigator.parentNode.replaceChild(this.navigator, currNavigator);
            currCatalogMini.parentNode.replaceChild(this.catalogMini, currCatalogMini);
            currPaginator.parentNode.replaceChild(this.paginator, currPaginator);

            $('body').removeClassName("movie-view").addClassName('catalog-view');
        }
        catalog.free = function(){
            this.paginator = null;
            this.catalogMini = null;
            this.navigator = null;
            this.toolbar = null;
            this.remove();
        }
        
        catalog.stateSaved = true;
        return true;
    },

    _saveMovieState: function(movie, toolbar, paginator)
    {
        if (movie.stateSaved) {
            return true;
        }

        movie.toolbar = toolbar;
        movie.paginator = paginator;


        movie.popHandler = function(){
            var movieId = this.attributes.mid.value;
            $$('.catalog-mini .item').invoke('removeClassName', 'active');
            $$('.catalog-mini .item[mid="' +  movieId + '"]').invoke('addClassName', 'active');

            var currToolbar = $('toolbar');
            var currPaginator = $$('.paginator')[0];
            currToolbar.parentNode.replaceChild(this.toolbar, currToolbar);
            currPaginator.parentNode.replaceChild(this.paginator, currPaginator);

            $('body').removeClassName("catalog-view").addClassName('movie-view');
        }
        movie.free = function(){
            this.toolbar = null;
            this.remove();
        }

        movie.stateSaved = true;
        return true;
    },
    
    initCatalogFrame: function()
    {
        var framesWrapper = $('content_frames');
        var currToolbar = $$('.toolbar')[0];
        var currNavigator = $('nav');
        var currCatalogMini = $$('.catalog-mini')[0];
        var currPaginator = $$('.paginator')[0];
        var currFrame = Element.select(framesWrapper, '.frame.active')[0];
        this._saveCatalogState(currFrame, currToolbar, currNavigator, currPaginator, currCatalogMini);
        
    },

    initMovieFrame: function()
    {
        var framesWrapper = $('content_frames');
        var currToolbar = $$('.toolbar')[0];
        var currPaginator = $$('.paginator')[0];
        var currFrame = Element.select(framesWrapper, '.frame.active')[0];
        this._saveMovieState(currFrame, currToolbar, currPaginator);
    },

    insertCatalogFrame: function (el)
    {
        var newToolbar = Element.select(el, '.toolbar')[0];
        var currNavigator = Element.select(el, '#nav')[0];
        var newCatalogMini = Element.select(el, '.catalog-mini')[0];
        var newPaginator = Element.select(el, '.paginator')[0];
        var newFrame = Element.select(el, '.catalog')[0];
        this._saveCatalogState(newFrame, newToolbar, currNavigator, newPaginator, newCatalogMini);
        
        this.insertFrame(newFrame);
    },

    insertMovieFrame: function (el)
    {
        var newToolbar = Element.select(el, '.toolbar')[0];
        var newPaginator = Element.select(el, '.paginator')[0];
        var newFrame = Element.select(el, '.movie')[0];
        this._saveMovieState(newFrame, newToolbar, newPaginator)
        
        this.insertFrame(newFrame);
    },

    insertFrame: function (frame)
    {
        var framesWrapper = $('content_frames');

        var currFrame = Element.select(framesWrapper, '.frame.active')[0];
        
        var notActiveFrames = Element.select(framesWrapper, '.frame:not(.active)');
        notActiveFrames.invoke('hide');

        currFrame.addClassName('left-frame')
                 .removeClassName('active');
                 
        var rightFrames = Element.select(framesWrapper, '.frame.right-frame');
        rightFrames.invoke('removeClassName', 'right-frame')
                   .invoke('addClassName', 'left-frame')
        framesWrapper.appendChild(frame);

        frame.popHandler();
        
        var frames = Element.select(framesWrapper, '.frame');
        if (frames.length>this.maxFrames) {
            frames[0].free();
        }
        this.setClickHandlers();
    },

    slideToFrame: function (frame)
    {
        var framesWrapper = $('content_frames');
        var frames = Element.select(framesWrapper, '.frame');
        var currFrame = Element.select(framesWrapper, '.frame.active')[0];
        
        if (frame==currFrame) {
            return true;
        }
        
        for (var i=0; i<frames.length; i++) {
            if (frames[i]!=currFrame && frames[i]!=frame) {
                frames[i].hide();
            }
        }
        frame.show();

        var bf = this._selectBefore(frames, frame);
        var af = this._selectAfter(frames, frame);
        setTimeout(function(){
            for (var i=0; i<bf.length; i++) {
                bf[i].removeClassName('right-frame')
                     .addClassName('left-frame');
            }

            for (var i=0; i<af.length; i++) {
                af[i].removeClassName('left-frame')
                     .addClassName('right-frame');
            }
            currFrame.removeClassName('active');

            frame.addClassName('active')
                 .removeClassName('left-frame')
                 .removeClassName('right-frame');
        }, 1);

        frame.popHandler();
        return true;
    },
  
    expandPerson: function (movieId, personId, element)
    {
        if (!element.hasClassName('more')) {
            window.action.getPerson(movieId, personId);
        } else {
            this.slidePersones(element.parentNode, 0) 
        }
    },

    showPerson: function (data, movieId, personId)
    {
        var movieElement = $$('.movie[mid="' +  movieId + '"]')[0];
        var targetElement = $$('.movie[mid="' +  movieId + '"] div.person-detail .ident')[0];
        targetElement.innerHTML = data.html;
        movieElement.addClassName('show-pesonal-detail');
        this.setClickHandlers();
    },

    hidePerson: function (movieId)
    {
        var movieElement = $$('.movie[mid="' +  movieId + '"]')[0];
        movieElement.removeClassName('show-pesonal-detail');
    },
        
    slidePersones: function(el, timeout) 
    {
        this.slidePersonesEnabled = 1;
        var self = this;
        setTimeout(function(){
            if (self.slidePersonesEnabled) {
                Element.select(el, '.more').invoke('removeClassName', 'more');
            }
        }, timeout);
    },
    
    cancelSlidePersones: function() 
    {
        this.slidePersonesEnabled = 0;
    },
    
    showTrailer: function()
    {
        if (!this.trailerBox) {
            this.trailerBox = LMS.Widgets.Factory('Overlay');
        }
        this.trailerBox.setContent($('videoplayer'), 720, 405);
        this.trailerBox.show();
    }, 
    
    closeTrailer: function()
    {
        //TODO: stop video
        this.trailerBox.close();
    },
    
    _isCatalogUrl: function(url)
    {
        var path = this.pathFromURL(url);
        var relativePath = path.replace(new RegExp("^" + ROOT_URL, "i"), "");
        if (relativePath=="" || relativePath=="/") {
            return true;
        }
        var r = /^\/?movies\/movie\//;
        return !r.test(relativePath);
    },
    
    onBackToCatalog: function(el)
    {
        var url;
        //1. search in URL's log
        for (var i=this.urlsLog.length-1; i>=0; i--) {
            if (this._isCatalogUrl(this.urlsLog[i])) {
                url = this.urlsLog[i];
                break;
            }
        }
        //2. search in history
        //TODO
        
        //3. search in referrer
        if (!url) {
            if (document.referrer && this._isCatalogUrl(document.referrer)) {
                url = document.referrer;
            }
        }
        //4. default url
        if (!url) {
            url = el.href;
        }
        var success = this.pajaxUrl(url);
        return !success;
    },
    
    navOnClick: function(element)
    {
        var url = element.href;
        switch (true) {
            case this.ctrlPressed:
                url = element.attributes['data-href-plus'].value;
                break;
            case this.altPressed:
                url = element.attributes['data-href-minus'].value;
                break;
        }
        var success = this.pajaxUrl(url);
        return !success;
    },
    
    clickHandler: function(e)
    {
        if (!e) e = window.event;
        if (e.which == 2 || e.metaKey || e.ctrlKey) return true;
        
        var a = e.target;
        while (a.tagName.toUpperCase()!='A') {
            a = a.parentNode;
        }
        var url = a.href? a.href : window.location.href;
        var success = this.pajaxUrl(url);
        return !success;
    },
    
    pajaxUrl: function(url)
    {
        if (window.history && window.history.pushState) {
            console.log("pajax load: " + url);
            var path = this.pathFromURL(url);
            window.history.pushState({
                path: path
            }, "", url);
            this.slideTo(url);
            return true;
        } else {
            return false;
        }
    },
    
    slideTo: function(url)
    {
        var matchFrame = this.frameForURL(url);
        
        if (matchFrame) {
            this.slideToFrame(matchFrame);
        } else {
            var self = this;
            new Ajax.Request(url + '?frame=1', {
                onSuccess: function(response) {
                    //console.log(response.responseText);
                    var tmpElement = new Element('TEMP');
                    tmpElement.innerHTML = response.responseText;
                    var type = Element.select(tmpElement, 'type')[0].innerHTML;
                    switch (type) {
                        case "catalog":
                            self.insertCatalogFrame(tmpElement);
                            break;
                        case "movie":
                            self.insertMovieFrame(tmpElement);
                            break;
                    }
                    tmpElement = null;
                    type = null;
                }
            });
        }
        this.urlsLog.push(url);
    },
    
    _toRandomPage: function()
    {
        var a = $$('.paginator a');
        var n = Math.floor(Math.random() * a.length);
        var a = a[n];
        if (a.href) {
            this.slideTo(a.href);
        }
    }
};
