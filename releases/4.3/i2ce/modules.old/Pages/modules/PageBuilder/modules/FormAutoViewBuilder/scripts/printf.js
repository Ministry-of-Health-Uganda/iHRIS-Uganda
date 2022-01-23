// Function for adding new printf argument selectors
function addPrintfArgSelector(targetElement, targetId) {
	
	// Get the table element
	var table = targetElement;
    while (table.tagName != "TABLE") {
    	table = table.parentNode;
      	if (!table) {
        	return false;
        }
    }
    
    // Find the cell that has the id passed into this function
	var tds = table.getElementsByTagName("td");
  	var cell;
    for (var i = 0; i < tds.length; i++) {
      	if (tds[i].id === targetId) {
        	cell = tds[i];
        }
    }
    
    // If the no arguments box is checked
  	var noArgsBox = cell.getElementById("noArgs");
    if (noArgsBox.checked) {
    	// Uncheck it and unhide every arg span instead
    	noArgsBox.checked = false;
    	hideArgs(noArgsBox);
    	
    	return false;
    }
  	
  	// Get all select elements from the specified cell
	var args = cell.getElementsByClassName("printf_arg_span");
	// Get the index of the new argument
	var newIndex = args.length;
	// Clone the first argument span
	var newArgSpan = args[0].cloneNode(true);
	
	// Append the argument span to the target element
  	cell.appendChild(newArgSpan);
  	
  	// Reorder the arguments
	reorderArgs(cell);
	
	return false;
}

// Function for removing printf argument selectors
function removePrintfArg(targetElement) {
	
	// Get the argument span that is to be deleted
    var delSpan = targetElement.parentNode
    // Get the index of the span being deleted
    var delIndex = delSpan.id.replace(/[^\d]/g, "");
    // Get the container that contains the argument span to be deleted
    var container = delSpan.parentNode;
    // Only delete the argument span if there is more than one argument span
    if (container.getElementsByClassName("printf_arg_span").length > 1) {
    	container.removeChild(delSpan);
    	// Reorder the arguments
    	reorderArgs(container);
    } else {
    	// Just check the no arguments box and hide the argument span if there is only one
    	var noArgsBox = delSpan.parentNode.getElementById("noArgs");
    	noArgsBox.checked = true;
    	hideArgs(noArgsBox);
    }
    
    return false;
}

// Function for renaming/reordering a list of argument ids
function reorderArgs(targetElement) {
	// Get all the argument spans
    var spans = targetElement.getElementsByClassName("printf_arg_span");
    
    // For each argument span
    for (var i = 0; i < spans.length; i++) {
    	// Rename the id to the correct index
    	spans[i].id = "arg[" + i + "]_span";
    	// Relabel to the correct index
    	var displayIndex = i + 1;
    	spans[i].firstChild.nodeValue = "Field " + displayIndex + ": ";
    	
    	// Get the argument select node
    	var select = spans[i].getElementsByTagName("select")[0];
    	// Change the selector's id and name to the correct index
    	select.id = "arg[" + i + "]";
    	select.name = select.name.replace(/arg\[\d+\]/g, "arg[" + i + "]");
    	
    	// Get the argument delete span
    	var delBtn = spans[i].getElementsByTagName("span")[0];
    	// Rename the id to the correct index
    	delBtn.id = "del_arg[" + i + "]";
    }
    
    return false;
}

/**
// Function for adding or removing formatter strings. This function is bugged
function reformatPrintf(targetElement, targetId, totalArgs, index) {
	
	// Get the table element
	var table = targetElement;
    while (table.tagName != "TABLE") {
    	table = table.parentNode;
      	if (!table) {
        	return false;
        }
    }
    
    // Find the cell that has the id passed into this function
	var tds = table.getElementsByTagName("td");
  	var cell;
    for (var i = 0; i < tds.length; i++) {
      	if (tds[i].id === targetId) {
        	cell = tds[i];
        }
    }
    
    // Get all input elements in the cell
    var inputs = cell.getElementsByTagName("input");
    // Take the first input as the format input
    if (inputs.length > 0) {
    	var format = inputs[0];
    	// Counter for amount of format specifiers that are in the printf_format
    	var count = 0;
    	// Index for saving the index of the format specifiers to removed
    	var rmvIndex = -1;
    	
    	// Get the index of the first format specifier
    	var formatRegex = /%s|%[1-9][\d]*s|%[\d]*d|%[\d]*\.[\d]+d/g;
    	var formatterIndex = format.value.search(formatRegex);
    	// Keep counting the number of format specifier substrings that appear
    	while (formatterIndex != -1) {
    		// Save the index of the specifier to remove if the there is a matching index
    		if (index == count) {
    			rmvIndex = formatterIndex;
    		}
    		// Get the index of the next format specifier. Increment the count of %s's found
    		formatterIndex = formatRegex.exec(format.value).index;
    		count++;
    	}
    	
    	// If there is a %s to be removed, remove it and decrement the %s count
    	if (rmvIndex != -1) {
    		format.value = format.value.substring(0, rmvIndex) + format.value.substring(rmvIndex + 2);
    		count--;
    	}
    	
    	// Add %s's to the end of the printf_format input
    	// until the total number of %s's is >= to the %s count
    	for (var i = count; i < totalArgs; i++) {
    		format.value = format.value + "%s";
    	}
    }
    
    return false;
}
*/

// Function for hiding argument selectors
function hideArgs(checkbox) {
	// Get all the argument spans
    var spans = checkbox.parentNode.getElementsByTagName("span");

	if (checkbox.checked) {
		// Set every argument span to hidden
    	for (var i = 0; i < spans.length; i++) {
    		spans[i].style.visibility = "hidden"
    	}
	} else {
		// Set every argument span to visible
    	for (var i = 0; i < spans.length; i++) {
    		spans[i].style.visibility = "visible"
    	}
	}
	
	return false;
}
