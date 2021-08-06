var update_person_attendance = new Array();


function updateDaysPresent(node,person_id,person_attendance_form_id, month_year) {
     node = $(node);

     if (!node) {
	      return;
    }
    var color = 'white';
    var working_days = node.get('value');

    if ( !update_person_attendance[days_present] ) {
        update_person_attendance[days_present] = true;
	      var url = 'index.php/person_attendance/';
        var req = new Request.HTML({
            method: 'post',
            url: url,
            data: { 'id':person_attendance_form_id,
					'parent' : person_id,
					'days_present':days_present,					
					'month_year':month_year,
					'action':'update_days_present',
					'submit_type':'save'
				  },
            onRequest: function() { node.set('value', 'Saving Record'); node.setStyle('color','black');},
            onComplete: function(response) { update_person_attendance[days_present] = false;  node.set('value',days_present); node.setStyle('color','green');}
        }).send();
    } else {
        alert('in progress');
    }
    return false;
}

function updateDaysAbsent(node,person_id,person_attendance_form_id, month_year) {
     node = $(node);

     if (!node) {
	      return;
    }
    var color = 'white';
    var working_days = node.get('value');

    if ( !update_person_attendance[days_absent] ) {
        update_person_attendance[days_absent] = true;
	      var url = 'index.php/person_attendance/';
        var req = new Request.HTML({
            method: 'post',
            url: url,
            data: { 'id':person_attendance_form_id,
					'parent' : person_id,
					'days_absent':days_absent,					
					'month_year':month_year,
					'action':'update_days_absent',
					'submit_type':'save'
				  },
            onRequest: function() { node.set('value', 'Saving Record'); node.setStyle('color','black');},
            onComplete: function(response) { update_person_attendance[days_absent] = false;  node.set('value',days_absent); node.setStyle('color','green');}
        }).send();
    } else {
        alert('in progress');
    }
    return false;
}

function updateDaysOR(node,person_id,person_attendance_form_id, month_year) {
     node = $(node);

     if (!node) {
	      return;
    }
    var color = 'white';
    var working_days = node.get('value');

    if ( !update_person_attendance[days_or] ) {
        update_person_attendance[days_or] = true;
	      var url = 'index.php/person_attendance/';
        var req = new Request.HTML({
            method: 'post',
            url: url,
            data: { 'id':person_attendance_form_id,
					'parent' : person_id,
					'days_or':days_or,					
					'month_year':month_year,
					'action':'update_days_or',
					'submit_type':'save'
				  },
            onRequest: function() { node.set('value', 'Saving Record'); node.setStyle('color','black');},
            onComplete: function(response) { update_person_attendance[days_or] = false;  node.set('value',days_or); node.setStyle('color','green');}
        }).send();
    } else {
        alert('in progress');
    }
    return false;
}
function updateDaysOD(node,person_id,person_attendance_form_id, month_year) {
     node = $(node);

     if (!node) {
	      return;
    }
    var color = 'white';
    var working_days = node.get('value');

    if ( !update_person_attendance[days_od] ) {
        update_person_attendance[days_od] = true;
	      var url = 'index.php/person_attendance/';
        var req = new Request.HTML({
            method: 'post',
            url: url,
            data: { 'id':person_attendance_form_id,
					'parent' : person_id,
					'days_od':days_od,					
					'month_year':month_year,
					'action':'update_days_od',
					'submit_type':'save'
				  },
            onRequest: function() { node.set('value', 'Saving Record'); node.setStyle('color','black');},
            onComplete: function(response) { update_person_attendance[days_od] = false;  node.set('value',days_od); node.setStyle('color','green');}
        }).send();
    } else {
        alert('in progress');
    }
    return false;
}
function updateDaysPH(node,person_id,person_attendance_form_id, month_year) {
     node = $(node);

     if (!node) {
	      return;
    }
    var color = 'white';
    var working_days = node.get('value');

    if ( !update_person_attendance[days_ph] ) {
        update_person_attendance[days_ph] = true;
	      var url = 'index.php/person_attendance/';
        var req = new Request.HTML({
            method: 'post',
            url: url,
            data: { 'id':person_attendance_form_id,
					'parent' : person_id,
					'days_ph':days_ph,					
					'month_year':month_year,
					'action':'update_days_ph',
					'submit_type':'save'
				  },
            onRequest: function() { node.set('value', 'Saving Record'); node.setStyle('color','black');},
            onComplete: function(response) { update_person_attendance[days_ph] = false;  node.set('value',days_ph); node.setStyle('color','green');}
        }).send();
    } else {
        alert('in progress');
    }
    return false;
}
function updateDaysLeave(node,person_id,person_attendance_form_id, month_year) {
     node = $(node);

     if (!node) {
	      return;
    }
    var color = 'white';
    var working_days = node.get('value');

    if ( !update_person_attendance[days_leave] ) {
        update_person_attendance[days_leave] = true;
	      var url = 'index.php/person_attendance/';
        var req = new Request.HTML({
            method: 'post',
            url: url,
            data: { 'id':person_attendance_form_id,
					'parent' : person_id,
					'days_leave':days_leave,					
					'month_year':month_year,
					'action':'update_days_leave',
					'submit_type':'save'
				  },
            onRequest: function() { node.set('value', 'Saving Record'); node.setStyle('color','black');},
            onComplete: function(response) { update_person_attendance[days_leave] = false;  node.set('value',days_leave); node.setStyle('color','green');}
        }).send();
    } else {
        alert('in progress');
    }
    return false;
}

var isNumeric = function(node, person_id, person_attendance_form_id, month_year){
	node = $(node);
	var type = node.get('id');
	if(!node){
		return;
	}
	var days = node.get('value');
	var regex=/^0{1}$|^([1-9]{1}[0-9]?)$/;
	if (days.match(regex) && days < 31)
	{
		if( type == "days_present"){
			updateDaysPresent(node,person_id,person_attendance_form_id, month_year);
			return true;
		}
		else if(type == "days_absent"){
			updateDaysAbsent(node,person_id,person_attendance_form_id, month_year);
			return true;
		}
		else if(type == "days_or"){
			updateDaysOR(node,person_id,person_attendance_form_id, month_year);
			return true;
		}
		else if(type == "days_od"){
			updateDaysOD(node,person_id,person_attendance_form_id, month_year);
			return true;
		}
		else if(type == "days_ph"){
			updateDaysPH(node,person_id,person_attendance_form_id, month_year);
			return true;
		}
		else if(type == "days_leave"){
			updateDaysLeave(node,person_id,person_attendance_form_id, month_year);
			return true;
		}	
	
	}
	else{
		alert("This value has to be a number less than 30");
		return false;
	}
}

