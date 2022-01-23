var Modules = {
    
    update: function(mod) { 
	var trs = $$('#sub_module_'+mod+' tr.option');
 	var chks = $$('#sub_module_'+mod+' div.check');
	var lis =  $$('#sub_module_'+mod+' li.module');
	trs.each(function(tr) {
	    Modules.trs[Modules.trs.length] = tr;
	    tr.chk=tr.getElement('div.check');
	});

	chks.each(function(chk) {
	    Modules.chks[Modules.chks.length] = chk;
	    chk.inputElement = chk.getElement('input');	    
	    if (chk.inputElement.checked) {
		chk.addClass('selected');
	    }
	});
	var possibles = document.id('module_possibles');
	var name;
	var input;
	if (possibles && possibles.getProperty) {
	    var poss = possibles.getProperty('value');
	    lis.each(function(li) {
		name = li.getProperty('name');
		input = document.id('input_enable_' + name);
		if (input) {
		    if (poss.length > 0) {
			poss = poss + ':' + name; 
		    } else {
			poss =  name;
		    }
		}
	    });
	    possibles.setProperty('value',poss);
	}
	var arrow = document.id('menu_link_arrow_'+mod);
	if (!arrow) {
	    return;
	}
	arrow.setStyle('display','block');

	arrow.setProperty('src','index.php/file/admin-arrow-up.gif');
	//slide.slideIn();
	//var arrow = $('menu_link_arrow_'+mod);
	if (arrow) {
	    if (arrow.removeEvents) { 
		arrow.removeEvents('click'); 
	    }	    
	    arrow.addEvent('click',function(e) {	
		e = new Event(e); 
		e.stop(); 
		var arrow = document.id('menu_link_arrow_'+mod);
		var src=arrow.getProperty('src');
		if (src.contains("up")) {
		    arrow.setProperty("src",'index.php/file/admin-arrow-down.gif');
		    document.id('sub_module_li_'+mod).setStyle("display","none");
		} else {
		    arrow.setProperty("src",'index.php/file/admin-arrow-up.gif');
		    document.id('sub_module_li_'+mod).setStyle("display","block");		    
		}	
	    });
	} 
	Modules.parse();
    }, 


    start: function(){	       
	Modules.trs = $$('tr.option');
	Modules.chks = $$('#modules div.check');
	Modules.button = document.id('module_enable_button');
	Modules.button.setProperty('disabled',true);
	Modules.button.setStyle('color','gray');
	Modules.fx = [];
	Modules.changed = [];

	Modules.trs.each(function(tr) {
	    tr.chk = tr.getElement('div.check');
	});

	Modules.chks.each(function(chk){
	    chk.inputElement = chk.getElement('input');	    
	    if (chk.inputElement.checked) {
		chk.addClass('selected');
	    }
	});
	Modules.parse();


    },
    
    
    change: function(chk) {
	if(Modules.changed.contains(chk.index)) {
	    Modules.changed.erase(chk.index);
	} else {
	    Modules.changed.include(chk.index); 
	}
    },

    select: function(chk){
	chk.inputElement.checked = 'checked';
	chk.addClass('selected');
	Modules.fx[chk.index].start({
	    'color': '#23ee23'
	});	
	Modules.change(chk);
	if (chk.deps){
	    chk.deps.each(function(id){
		var other = document.id(id);
		if (!other) {
		    return;
		}
		if (!other.hasClass('selected')) Modules.select(other);
	    });
	}
	if (chk.cons) {
	    chk.cons.each(function(id) {
		var other = document.id(id);
		if (!other) {
		    return;
		}
		if (other.inputElement.checked) Modules.deselect(other);
	    });
	}
	if (chk.opt) {
	    chk.opt.each(function(id){
		var other = document.id(id);
		if (!other) {
		    return;
		}
		if (!other.hasClass('selected')) Modules.select(other);
	    });
	}
    },
    

    deselect: function(chk){
	chk.inputElement.checked = false;
	chk.removeClass('selected');
	Modules.fx[chk.index].start({
	    'color': '#ff0000'
	});
	Modules.change(chk);
	Modules.chks.each(function(other){
	    if (other == chk) return;
	    if (other.deps) {
		if (other.deps.contains(chk.id) && other.hasClass('selected')) {
		    Modules.deselect(other);
		}
	    }	
	});
    },

    parse: function(){
	Modules.trs.each(function(tr, i){
	    if (!Modules.fx[i]) {
		Modules.fx[i] = new Fx.Morph(tr, {wait: false, duration: 300});
		tr.addEvent('mouseenter', function(){
		    if (tr.chk) {
			if (!Modules.changed.contains(tr.chk.index)) {
			    Modules.fx[i].start({
				'color': '#b3b3bb'
			    });
			}
		    } else {
			Modules.fx[i].start({
			    'color': '#b3b3bb'
			});
		    }
		});
		
		tr.addEvent('mouseleave', function(){
		    if (tr.chk) {
			if (!Modules.changed.contains(tr.chk.index)) {
			    Modules.fx[i].start({
				'color': '#595965'
			    });
			}
		    } else {
			Modules.fx[i].start({
			    'color': '#595965'
			});		    
		    }
		});
		var chk = tr.getElement('div.check');
		if (!chk) {
		    return;
		}

		chk.index = i;
		var dp = chk.getProperty('deps');
		if (dp) chk.deps = dp.split(',');
		
		var cons = chk.getProperty('cons');
		if (cons) chk.cons = cons.split(',');

		var opt = chk.getProperty('opt');
		if (opt) chk.opt = opt.split(',');
		
		chk.inputElement.addEvent('click', function(){		
		    if (chk.hasClass('selected')) {
			Modules.deselect(chk);
		    } else {
			Modules.select(chk);
		    }
		    if (Modules.changed.length > 0) {
			Modules.button.setProperty('disabled',false);
			Modules.button.setStyle('color','black');
		    } else {
			Modules.button.setProperty('disabled',true);
			Modules.button.setStyle('color','gray');
		    }
		});

	    }
	});
    }

};

if (window.addEvent) {
    window.addEvent('load', Modules.start);
}