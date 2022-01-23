/**
 * © Copyright 2007, 2008 IntraHealth International, Inc.
 * 
 * This File is part of I2CE 
 * 
 * I2CE is free software; you can redistribute it and/or modify 
 * it under the terms of the GNU General Public License as published by 
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful, 
 * but WITHOUT ANY WARRANTY; without even the implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the 
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License 
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * FormWorm.js -- a javascript mini-library to handle validation and submissions of forms with multiple
 * actions and options depending on the action.  
 * @package I2CE
 * @subpackage Core
 * @author Carl Leitner <litlfred@ibiblio.org>
 * @copyright Copyright &copy; 2007, 2008 IntraHealth International, Inc. 
 * This file is part of I2CE. I2CE is free software; you can redistribute it and/or modify it under 
 * the terms of the GNU General Public License as published by the Free Software Foundation; either 
 * version 3 of the License, or (at your option) any later version. I2CE is distributed in the hope 
 * that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY 
 * or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have 
 * received a copy of the GNU General Public License along with this program. If not, see <http://www.gnu.org/licenses/>.
 * @version 2.1
 * @access public
 *
 * This incorporates a few ideas/code of http://forum.mootools.net/topic.php?id=3300
 */



var SelectUpdate = new Class( {
    select: false,
    select_id: false,
    current: new Array(),
    valNodes: new Object(),
    options: {
	show_class : false,
	hide_class : false,
	show_function : false,
	hide_function : false,
	show_function_by_val : false,
	hide_function_by_val : false,
	show_on_init : true,
	hide_on_init : true,
	prefix : 'select_update'
    },

    initialize: function(select_id,options ) {
	this.select_id = select_id;
	this.setOptions(options);		
	if (Browser.loaded) {
	    this.setup();
	} else {
	    window.addEvent('load',function(e) {
		this.setup();
	    }.bind(this));
	}
    },

    setup: function() {
	this.select = document.id(this.select_id);
	if (!this.select) {
	    return;
	}
	if ($type(this.options.show_function) === 'string') {
	    this.options.show_function = eval(this.options.show_function);
	}
	if ($type(this.options.show_function) === 'function') {
	    this.options.show_function.bind(this);
	}
	if ($type(this.options.show_function_by_val) === 'string') {
	    this.options.show_function_by_val = eval(this.options.show_function_by_val);
	}
	if ($type(this.options.show_function_by_val) === 'function') {
	    this.options.show_function_by_val.bind(this);
	}
	if ($type(this.options.hide_function) === 'string') {
	    this.options.hide_function = eval(this.options.show_function);
	}
	if ($type(this.options.hide_function) === 'function') {
	    this.options.hide_function.bind(this);
	}
	if ($type(this.options.hide_function_by_val) === 'string') {
	    this.options.hide_function_by_val = eval(this.options.hide_function_by_val);
	}
	if ($type(this.options.hide_function_by_vall) === 'function') {
	    this.options.hide_function_by_val.bind(this);
	}

	this.select.addEvent('change', function(e) {
	    var new_vals = this.select.getSelected();
	    this.hide(this.current, new_vals);
	    this.show(this.current, new_vals);
	    this.current = new_vals;
	}.bind(this));
	var new_vals = this.select.getSelected();
	if (this.options.hide_on_init) {
	    this.hide(this.current, new_vals);
	}
	if (this.options.show_on_init) {
	    this.show(this.current, new_vals);
	}
	this.current = new_vals;
    },

    show: function(old_vals, new_vals, force) {
	if (!this.select) {
	    return;
	}
	if (! new_vals) {
	    new_vals = this.select.getSelected();
	}
	if (! old_vals) {
	    old_vals = this.current;
	}
	if (this.options.show_class || this.options.hide_class) {	    
	    this.select.getElements('option').each(function (option) {
		valNode = this.getValNode(option.getAttribute('value'));
		if (!valNode) {
		    return;
		}
		if (this.nodeWithin(option,new_vals)) {  
		    //th current options is among the newly selected options. we don't need to try to remove the show class
		    if (this.options.show_class) {
			valNode.addClass(this.options.show_class);
		    } 
		    if (this.options.hide_class) {
			valNode.removeClass(this.options.hide_class);
		    }
		} else {
		    if (this.options.show_class) {
			valNode.removeClass(this.options.show_class);
		    } 
		    if (this.options.hide_class) {
			valNode.addClass(this.options.hide_class);
		    }
		}
	    }, this);
	}
	if ($type(this.options.show_function) === 'function') {
	    this.options.show_function(this.select);
	}
	if (this.options.show_function_by_val || this.options.show_class) {
	    new_vals.each (function(option) {  //run through newly selected options
		if (force !== true && this.nodeWithin(option,old_vals)) {
		    //the newly selected option was already selected   we don't need to do the showingg stuff
		    return;
		}
		valNode = this.getValNode(option.getProperty('value'));
		if (!valNode) {
		    return;
		}
		if (this.options.show_class) {
		    valNode.addClass(this.options.show_class);
		}
		if (this.options.show_function_by_val) {
		    this.options.show_function_by_val(valNode);
		}
	    },this);
	}
    },

    nodeWithin: function (node,nodes) {
	return nodes.some(function(c_node) { return $type(c_node) === 'element' &&  c_node === node;});
    },



    hide: function(old_vals,new_vals, force) {
	if (!this.select) {
	    return;
	}
	if (! new_vals) {
	    new_vals = this.select.getSelected();
	}
	if (! old_vals) {
	    old_vals = this.current;
	}
	if ($type(this.options.hide_function) === 'function' ) {
	    this.options.hide_function(this.select);
	}
	if (this.options.hide_function_by_val || this.options.hide_class) {	    
	    old_vals.each (function(option) { //run through the old selected options,
		if (force !== true && this.nodeWithin(option,new_vals)) {
		    //old option remains  selected.   we don't need to do the hiding stuff
		    return;
		}
		valNode = this.getValNode(option.getProperty('value'));	
		if (!valNode) {
		    return;
		}
		if (this.options.hide_class) {		    
		    valNode.addClass(this.options.hide_class);
		}
		if (this.options.hide_function_by_val) {
		    this.options.hide_function_by_val(valNode);
		}
	    },this);
	}
    },

    getValId:function(val) {
	if (val) {
	    return this.options.prefix + ':' + this.select_id + ':'+ val;
	} else {
	    return this.options.prefix + ':' + this.select_id ;
	}
    },

    getValNode:function (val) {
	return document.id(this.getValId(val));
    }

    

});

SelectUpdate.implement(new Options);