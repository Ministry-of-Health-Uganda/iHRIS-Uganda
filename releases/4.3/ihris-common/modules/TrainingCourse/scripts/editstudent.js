var editstudent_inprogress = new Array();

function updateExamList(node) {
    node = $(node);
    if (!node) {
	return;
    }
    var value = node.get('value');
    var disappear = [];
    var appear = false;
    $(document.body).getElements('div[name="exam_student_list"]').each(function(e) {	
	if (e.get('id') == value) {
	    appear = e;
	} else {
	    if (e.getStyle('display') != 'none') {
		disappear.push(e);
	    }
	}
    });
    disappear.each(function(e) {
	new Fx.Slide(e).slideOut();
    });
    if (appear) {
	appear.setStyle('display','block');
	new Fx.Slide(appear).slideIn();
    }
    return true;
}


function ajaxLoadStudents( node, scheduled_training_course ) {
    node = $(node);
    if (!node) {
        return false;
    }
    node.removeEvents('click');
    node = node.parentNode.parentNode.parentNode;

    var url = Stub.urlencode('view_list?id=' +  scheduled_training_course + '&type=scheduled_training_course&i2ce_template=participants');
    var req = new Request.HTML({
        method: 'get',
	url : 'index.php/stub/id?request=' + url +  '&content=participant_list' ,
	evalScripts:true,
        onRequest: function() { 
	    node.set('text', 'Loading students...');     
	    node.addClass('stub-ajax-loading');
	},
        update: node,
        onComplete: function(response) { 
	    node.removeClass('stub-ajax-loading'); 
	    node.replace(reposnse);     
	}
    }).send();
}   

function toggleStudent( node, scheduled_training_course, person ) {
    node = $(node);
    if (!node) {
	return false;
    }
    if ( !editstudent_inprogress[person] ) {
        editstudent_inprogress[person] = true;
	var url = 'index.php/actionstudents';
        var req = new Request.HTML({
            method: 'get',
            url: url,
            data: { 'scheduled_training_course' : scheduled_training_course, 'person' : person },
            onRequest: function() { node.set('text', 'Updating student...'); },
            update: node,
            onComplete: function(response) { editstudent_inprogress[person] = false; }
        }).send();
    } else {
        alert('in progress');
    }
}


function toggleStudentModule( node, person,person_scheduled_training_course, training_course_mod ) {
    node = $(node);
    if (!node) {
	return;
    }
    var color = node.getStyle('color');
    if ( !editstudent_inprogress[person_scheduled_training_course] ) {
        editstudent_inprogress[person_scheduled_training_course] = true;
        var req = new Request.HTML({
            method: 'post',
            url: "index.php/actionstudents",
	    data: {
		"action":"student_module",
		"person_scheduled_training_course":  person_scheduled_training_course,
		'training_course_mod': training_course_mod,
		'person':person
		},
            onRequest: function() {     node.setStyle('color','yellow');},
            onComplete: function(response) {      editstudent_inprogress[person_scheduled_training_course] = false;  node.setStyle('color',color);}
        }).send();
    } else {
        alert('in progress');
    }
    return false;
}


function setCertification(node,person,person_scheduled_training_course,certification_date) {
    node = $(node);
    if (!node) {
	return;
    }
    var color = node.getStyle('color');
    if ( !editstudent_inprogress[person_scheduled_training_course] ) {
        editstudent_inprogress[person_scheduled_training_course] = true;
        var req = new Request.HTML({
            method: 'post',
            url: "index.php/actionstudents",
	    data: {
		"action":"certify",
		"person_scheduled_training_course":  person_scheduled_training_course,
		'certification_date' : certification_date,
		'person':person
		},
            onRequest: function() {node.setStyle('color','yellow');},
            onComplete: function(response) { editstudent_inprogress[person_scheduled_training_course] = false;  node.setStyle('color',color);}
        }).send();
    } else {
        alert('in progress');
    }
    return false;


}


function changeFinalExamGrade(node,exam_type,person_scheduled_training_course,exam,passing_score) {
    node = $(node);
    if (!node) {
	return;
    }
    var color = 'white';
    var score = node.get('value');
    if (score >= passing_score) {
	color = 'green';
    } else {
	color = 'red';
    }
    if ( !editstudent_inprogress[exam] ) {
        editstudent_inprogress[exam] = true;
	var url = 'index.php/training-course/exam';
        var req = new Request.HTML({
            method: 'post',
            url: url,
            data: { 'id':exam, 'parent' : person_scheduled_training_course, 'exam_type':exam_type,'score': score, 'action':'updatescore','submit_type':'save'},
            onRequest: function() { node.set('value', 'Updating score...'); node.setStyle('color','black');},
            onComplete: function(response) { editstudent_inprogress[exam] = false;  node.set('value',score); node.setStyle('color',color);}
        }).send();
    } else {
        alert('in progress');
    }
    return false;
}


function removeStudentByInstance(node,person_scheduled_training_course,person,remnode) {
    node = $(node);
    remnode = $(remnode);
    if (!confirm("You are about to remove student from the course.  Are you sure?")) {
	return false;
    }
    if ( !person_scheduled_training_course || !person) {
	return false;
    }
    node.removeEvents('click');
    var url = 'index.php/actionstudents';
    if ( !editstudent_inprogress[person] ) {
        editstudent_inprogress[person] = true;
        var req = new Request.HTML({
            method: 'get',
            url: url,
            data: { 'person_scheduled_training_course' : person_scheduled_training_course, 'person' : person ,'action':'remove'},
            onRequest: function() {   if (node) {node.set('text', 'Updating student...');} return false;},
            onComplete: function(response) { editstudent_inprogress[person] = false; if (remnode) {remnode.destroy();}  return false;}
        }).send();
    } else {
        alert('in progress');
    }
    return false;
}


function changeStudentEvaluation(node,person_scheduled_training_course) {
    node = $(node);
    if (!node) {
	return;
    }
    var evaluation = node.get('value');
    if ( !editstudent_inprogress[person_scheduled_training_course] ) {
        editstudent_inprogress[person_scheduled_training_course] = true;
	var url = 'index.php/actionstudents';
        var req = new Request.HTML({
            method: 'post',
            url: url,
            data: { 'person_scheduled_training_course' : person_scheduled_training_course, 'evaluation': evaluation, 'action':'evaluation','submit_type':'save'},
            onRequest: function() { node.set('value', 'Updating evaluation...'); },
            onComplete: function(response) { editstudent_inprogress[person_scheduled_training_course] = false;  node.set('value',evaluation); }
        }).send();
    } else {
        alert('in progress');
    }
    return false;
}

