
var I2CE_KeyValParser= {
    val_parsers: {},
    val_types: [],


    addParser: function(signal_character, valParser) {
	if ($type(valParser) != 'function') {
	    return;
	}
	this.val_types.push(signal_character);
	this.val_parsers[signal_character] = valParser;
    },    
    
    loadKeyValPairs: function (input,obj) {	
	if (!$type(obj) == 'object') {
	    return null;
	}
	var hash = $H(obj);
	var i = 0; 
	var state = 0;	    
	var key = '';
	var val = '';
	var c;
	if ($type(input) != 'string' || input.length == 0) {
	    return null;
	}
	var max_index = input.length ;
	//STATES:
	//0: ground state
	//20: begin value
	//21: quoted value 
	//22: quoted escaped value
	//29: value
	//100:wait for white space
	var val_type =false;  //type 1 is equals(=), type 2 is json(:)
	do  {
	    c = input.charAt(i);
	    switch (state) {		
	    case 0: //GROUND STATE
		if (c == ' ' || c == "\t" || c == "\n") {
		    //skip whitespace
		    break;		    
		}
		if ( ('a' <= c && c <= 'z') || ('A' <= c && c <= 'Z') ) {
		    key = c;
		    state = 10; //KEY NAME STATE
		} else {
		    state = 100; //WAIT FOR WHITE SPACE
		}
		break;
	    case 10: //KEY NAME STATE
		if (c == ' ' || c == "\t" || c == "\n") {
		    key = '';
		    val = '';
		    state = 0;		    
		    break;		    
		}
		if (this.val_types.contains(c)) {
		    if (key.length > 0) {
			val_type = c;
			state = 20; //VALUE BEGIN STATE
		    } else {
			state = 100; //WAIT FOR WHITE SPACE
		    }
		    break;
		}
		key += c;
		break;
	    case 20: //VALUE BEGIN STATE
		if (c == ' ' || c == "\t" || c == "\n") {
		    if (hash.has(key)) {
			//val is empty here.  hope your parser handles it apporpriately
			obj[key] = this.val_parsers[val_type].attempt(val);
		    }
		    state =0;
		    key = '';
		    val = '';
		    break;		    		    
		}
		if ( c == "'") {
		    state = 21; // VALUE QUOTED BEGIN
		    break;
		} else {
		    val = c;
		    state = 29; // VALUE
		    break;
		}
	    case 21: //VALUE QUOTED 
		if (c == '\\') {
		    state = 22; // VALUE QUOTED ESCAPED
		    break;
		}
		if (c == '\'') {
		    //ending the quoteed string
		    if (hash.has(key)) {
			obj[key] = this.val_parsers[val_type].attempt(val);
		    }
		    state =0;
		    key = '';
		    val = '';			
		    break;
		}
		val += c;
		break;
	    case 22:  //VALUE QUOTED ESCAPED
		if ( c == '\'' || c == '\\') {
		    val += c;
		} else {
		    val += '\\' + c;
		}
		state = 21;  // VALUE QUOTED 
		break;
	    case 29: //VALUE 
		if (c == ' ' || c == "\t" || c == "\n") {
		    if (hash.has(key)) {
			obj[key] = this.val_parsers[val_type].attempt(val);
		    }
		    state =0;
		    key = '';
		    val = '';
		    break;		    		    
		}  else {
		    val += c;
		    break;
		}
	    case 100:  //WAIT FOR WHITE SPACE STATE
		if (c == ' ' || c == "\t" || c == "\n") {
		    state = 0;
		    key = '';
		    val = '';
		}
		break;
	    default:
		//should not be here.
		break;
	    }
	    i++;
	} while (i < max_index);
	if (state == 21 || state == 29) {//a null value or n unquoted value was terminated by end of string.  this is valid
	    if (hash.has(key)) {
		//val is empty here.  hope your parser handles it apporpriately
		obj[key] = this.val_parsers[val_type].attempt(val);
	    }
	}
	return null;
    }
};



I2CE_KeyValParser.addParser(
    '%',
    function ( val) {
	if ($type(val) != 'string') {
	    return null;
	}
	if (val.length == 0) {
	    return null;
	}	
	return JSON.decode(val);
    });

I2CE_KeyValParser.addParser(
    '=',
    function ( val) {
	if ($type(val) != 'string') {
	    return null;
	}
	if (val.length == 0) {
	    return null;
	}
	var parseVal = function(val) {
	    if (val.match(/^\s*true\s*$/i)) {
		return true;
	    }else if (val.match(/^\s*false\s*$/i)) {		
		return false;
	    } else if (val.match(/^\s*[0-9]+\s*$/)) {
		return val.toInt();
	    } else if (val.match(/^\s*[0-9]+\.[0-9]*\s*$/)) {
		return val.toFloat();
	    } else {	    
		return val;
	    }
	};
	if (val.length >= 2 && val.charAt(0) == '[' && val.charAt(val.length-1) == ']') {
	    return val.substr(1,val.length -2).split(/\s*,\s*/).map(parseVal);
	} else {
	    return parseVal(val);
	}
    });
    


Element.implement({    

    loadClassValues: function(obj) {	
	I2CE_KeyValParser.loadKeyValPairs(this.className, obj);
    },


    findClassValue: function(key) {
	var obj = {};
	obj[key] = null;
	I2CE_KeyValParser.loadKeyValPairs(this.className,obj);	
	return  obj[key];
    }


    
});




