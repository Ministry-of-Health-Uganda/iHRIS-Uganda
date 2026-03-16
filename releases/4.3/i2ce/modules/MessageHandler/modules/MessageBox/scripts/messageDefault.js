var MessageDefault= {
    message_box: null,
    height: null,
    fx: null,

    prep:function() {
	MessageDefault.message_box = document.id('message_box_default');
	if (!MessageDefault.message_box) {
	    return;
	}	
	MessageDefault.height = MessageDefault.message_box.getSize().y;
	MessageDefault.message_box.setStyles({'height':0});
	window.addEvent('load', MessageDefault.start);
    },

    start: function(){	       
	MessageDefault.fx = new Fx.Morph(MessageDefault.message_box, {duration: 'long'});
	MessageDefault.message_box.setStyles({'visibility':"visible"});
	MessageDefault.fx.start({	    
	    'height': [0,MessageDefault.height],
	    'opacity': [0,1],
	    'color': '#000000'
	});
    }

};
 
window.addEvent('domready', MessageDefault.prep);

