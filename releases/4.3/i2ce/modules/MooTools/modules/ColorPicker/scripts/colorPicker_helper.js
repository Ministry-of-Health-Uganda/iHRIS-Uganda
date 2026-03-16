var ColorPickerHelper = {
    showColorBoxes: new Array(),
    colorPickers: new Array(),

    addColorTripleSelectorHex: function (attach,showColor,colorID,initColor) {
        var colorPickerBox;
        var showColorBox;
        colorPickerBox = document.id(attach);
        showColorBox = document.id(showColor);
	if (colorPickerBox && showColorBox) {
            ColorPickerHelper.showColorBoxes[attach] = showColorBox;
	    ColorPickerHelper.updateShowColorBoxHex(attach,initColor);
            ColorPickerHelper.colorPickers[attach] =  
		new ColorPicker({
		    attachTo: colorPickerBox,
		    initialState: 'Open',
		    initialColor: initColor,
		    showColorInfo: false,
		    showTitleBar: false, 
		    initColor : initColor,
		    hueBarWidth: 10,
		    hueBarHeight:112, 
		    svBoxWidth:60, 
		    svBoxHeight:112,
		    SVClickCallback: function () { 
			var colorInput;			
			if (ColorPickerHelper.colorPickers[attach]) {
			    ColorPickerHelper.updateShowColorBoxHex(attach,ColorPickerHelper.colorPickers[attach].CurColor);
			}			
			colorInputs  = document.id('body').getElements('input[id="' + colorID + '"]');
			var i = 0;
			colorInputs.each(function(colorInput) {
			    colorInput.setProperty(
				'value',
				ColorPickerHelper.dec2hex(ColorPickerHelper.colorPickers[attach].CurColor[i])
			    );
			    i++;
			});
		    }
		});
	}
    },


    addColorTripleSelectorDec: function (attach,showColor,colorID,initColor) {
        var colorPickerBox;
        var showColorBox;
        colorPickerBox = document.id(attach);
        showColorBox = document.id(showColor);
	if (colorPickerBox && showColorBox) {
            ColorPickerHelper.showColorBoxes[attach] = showColorBox;
	    ColorPickerHelper.updateShowColorBoxDec(attach,initColor);
            ColorPickerHelper.colorPickers[attach] =  
		new ColorPicker({
		    attachTo: colorPickerBox,
		    initialState: 'Open',
		    initialColor: initColor,
		    showColorInfo: false,
		    showTitleBar: false, 
		    initColor : initColor,
		    hueBarWidth: 10,
		    hueBarHeight:112, 
		    svBoxWidth:60, 
		    svBoxHeight:112,
		    SVClickCallback: function () { 
			var colorInput;			
			if (ColorPickerHelper.colorPickers[attach]) {
			    ColorPickerHelper.updateShowColorBoxDec(attach,ColorPickerHelper.colorPickers[attach].CurColor);
			}	
			colorInputs  = document.id('body').getElements('input[id="' + colorID + '"]');
			var i = 0;
			colorInputs.each(function(colorInput) {
			    colorInput.setProperty(
				'value',
				ColorPickerHelper.colorPickers[attach].CurColor[i]
			    );
			    i++;
			});		
		    }
		});
	}
    },

    updateShowColorBoxDec: function (id,color) {
	color = ColorPickerHelper.ensureDecColor(color);		
	if (ColorPickerHelper.showColorBoxes[id]) {
	    ColorPickerHelper.showColorBoxes[id].setStyle('background-color',color);
	    switch ($type(color)) {
	    case 'array':
		ColorPickerHelper.showColorBoxes[id].setHTML( color[0] + ',' + color[1] + ',' + color[2]);
		break;
	    case 'string':
		ColorPickerHelper.showColorBoxes[id].setHTML( color);
		break;
	    }
	}	
    },


    updateShowColorBoxHex: function (id,color) {
	color = ColorPickerHelper.ensureHexColor(color);		
	if (ColorPickerHelper.showColorBoxes[id]) {
	    ColorPickerHelper.showColorBoxes[id].setStyle('background-color',color);
	    switch ($type(color)) {
	    case 'array':
		ColorPickerHelper.showColorBoxes[id].setHTML( color[0] + ',' + color[1] + ',' + color[2]);
		break;
	    case 'string':
		ColorPickerHelper.showColorBoxes[id].setHTML( color);
		break;
	    }
	}	
    },


    updateShowColorBoxDecOnEvent: function (event,action_id,update_id,color) {
        var updateFunc;
	var showColorBox;
        var updateColorBox = document.id(action_id);
        showColorBox = document.id(update_id);
	if (updateColorBox && showColorBox ) {
            ColorPickerHelper.showColorBoxes[action_id]= showColorBox;
            updateFunc = function(e) { 
		e=new Event(e); 
		e.stop(); 
		ColorPickerHelper.updateShowColorBoxDec(action_id,color);
	    };
	    updateColorBox.addEvent(event,updateFunc);
	}
    },


    updateShowColorBoxHexOnEvent: function (event,action_id,update_id,color) {
        var updateFunc;
	var showColorBox;
        var updateColorBox  = document.id(action_id);
        showColorBox = document.id(update_id);
	if (updateColorBox && showColorBox ) {
            ColorPickerHelper.showColorBoxes[action_id]= showColorBox;
            updateFunc = function(e) { 
		e=new Event(e); 
		e.stop(); 
		ColorPickerHelper.updateShowColorBoxHex(action_id,color);
	    };
	    updateColorBox.addEvent(event,updateFunc);
	}
    },


    ensureHexColor: function (color) {
	var ret = false;
	switch ($type(color)) {
	case 'string':
	    ret = color;
	    break;
	case 'array':
	    ret = '#' + ColorPickerHelper.dec2hex(color[0]) + ColorPickerHelper.dec2hex(color[1]) + ColorPickerHelper.dec2hex(color[2]);
	}
	return ret;
	
    },

    ensureDecColor: function(color) {
	var ret=new Array();
	switch($type(color)) {
	case 'string':
	    ret = color.hexToRgb(true);
	    break;
	case 'array':
	    ret = color; //we don't know if this is a triple of hex or decimal.  assume it is decimal
	    break;
	}
	return ret;	
    },
    
    dec2hex: function(n) {
	n = parseInt(n); 
	var c = 'ABCDEF';
	var b = n / 16; 
	var r = n % 16; 
	b = b-(r/16); 
	b = ((b>=0) && (b<=9)) ? b : c.charAt(b-10);    
	return ((r>=0) && (r<=9)) ? b+''+r : b+''+c.charAt(r-10);
    }


};