function set_contact_group(node, person_id){
	var form_ids = node.get('form_ids');
	var group_id = node.get('name');
	
	if(!node.checked){
		var anchor = document.getElementById(person_id).getAttribute("contactgroup");		
		console.log('unchecked: the current value is '+anchor);
		var new_value = anchor.replace(node.get('value'), 'bad');
		document.getElementById(person_id).setAttribute('contactgroup', new_value);
		
	}
	else{
		var anchore = document.getElementById(person_id).getAttribute("contactgroup");		
		console.log('checked: the current value is '+anchore);
		if(!anchore.trim()){
			var new_checked = node.get('value');
		}else{
			var new_checked = anchore+'_'+node.get('value');
		}
		document.getElementById(person_id).setAttribute('contactgroup', new_checked);
	}
	
}

var update_contact_group = new Array();

function updateContactGroup(node) {
     node = $(node);
	if(!node.get('contactgroup').trim()){
		alert("Update only if you changed something!");
		return;
	}
     if (!node) {
	      return;
    }
    var color = 'white';
	if(node.get('form_ids')){
		var form_ids = node.get('form_ids');//record has work contact
	}
	else{
		var form_ids = node.get('id');//record has no work contact
		console.log("person id is "+form_ids);
	}
	var contact_groups = node.get('contactgroup');
	console.log("contact groups "+contact_groups);
    if ( !update_contact_group['contact_group'] ) {
        update_contact_group['contact_group'] = true;
	      var url = 'index.php/contact';
        var req = new Request.HTML({
            method: 'post',
            url: url,
            data: { 'parent':form_ids,
					'contact_type':'work',
					'source':'mhero',
					'contact_group':contact_groups,
					'submit_type':'save'
				  },        		
			onRequest: function() {
				node.set('text', 'Updating...');
			},
			onComplete: function(response) { update_contact_group['contact_group'] = false; node.set('text', 'Updated');}
		}).send();
	}
	else {
		alert('in progress');
		return false;
	}    
}

var isNumeric = function(node, person_id, monthly_salary_form_id, month){
	node = $(node);
	var type = node.get('id');
	if(!node){
		return;
	}
	var days = node.get('value');
	var regex=/^0{1}$|^([1-9]{1}[0-9]?)$/;
	if (days.match(regex) && days < 31)
	{
		if( type == "leave_days"){
			updateLeaveDays(node,person_id,monthly_salary_form_id, month);
			return true;
		}
		else if(type == "work_days"){
			updateWorkingDays(node,person_id,monthly_salary_form_id, month);
			return true;
		}
	}
	else{
		alert("This value has to be a number less than 30");
		return false;
	}
}
