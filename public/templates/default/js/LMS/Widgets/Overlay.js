/**
 * @copyright 2006-2011 LanMediaService, Ltd.
 * @license    http://www.lanmediaservice.com/license/1_0.txt
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @version $Id: Overlay.js 700 2011-06-10 08:40:53Z macondos $
 */
 
 JSAN.require('LMS.Widgets.Generic');

LMS.Widgets.Overlay = Class.create(LMS.Widgets.Generic, {
    // properties
    content: null,
    overlayId: '_overlay',
    width: 700,
    height: 400,
    onCreateElement: function() 
    {
        this.setVisible(false);
        
        JSAN.require('LMS.Widgets.Factory');
        
        var wrapper = LMS.Widgets.Factory('LayerBox');
        wrapper.setDOMId(this.overlayId);
        this.content.setStyle({
                margin: '-' + this.height/2 + 'px 0 0 -' + this.width/2 + 'px',
                width: this.width + 'px',
                height: this.height + 'px',
        });
        wrapper.addElement(this.content);
        
        this.wrapperElement = wrapper.createElement();
        this.applyDecorators();
        return this.wrapperElement;
    },
    
    setContent: function(content, width, height)
    {
        this.content = content;
        if (width) {
            this.width = width;
        }
        if (height) {
            this.height = height;
        }
        if ($(this.overlayId)) {
            this.content.setStyle({
                    margin: '-' + this.height/2 + 'px 0 0 -' + this.width/2 + 'px',
                    width: this.width + 'px',
                    height: this.height + 'px',
            });
            this.wrapperElement.appendChild(this.content);
        }
    },

    show: function(id)
    {
        if (!$(this.overlayId)) {
            this.createElement();
            document.body.appendChild(this.wrapperElement);
        }
        this.enableOverlay();
        this.setVisible(true);
    },
    enableOverlay: function()
    {
        this.wrapperElement.addClassName('overlay');
        var self = this;
        this.wrapperElement.onclick = function(e){self.overlayClick(e);};
    },
    
    disableOverlay: function()
    {
        if (this.wrapperElement) {
            this.wrapperElement.removeClassName('overlay');
        }
    },
    
    overlayClick: function(e)
    {
        if (!e) e = window.event;
        var element = (e.srcElement) ? e.srcElement : (e.target) ? e.target : null;;
        if (element == this.wrapperElement) {
            this.close();
        }
    },
    
    close: function()
    {
        this.hide();
    },
    
    hide: function(force)
    {
        this.setVisible(false);
        this.disableOverlay();
    }
    
});