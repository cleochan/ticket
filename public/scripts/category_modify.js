$(document).ready(function() {
	
	//Hide window and bind hide window to clicking on document
	initWindow();
	
	$('#view-content').bind("click", function(e) {
		displayWindow(false, e);
	})

	$(".categoryLink").click(function(e) {
		$('#current_working_id').attr('value', $(this).attr('value'));
		$('#edit_window').hide();
		$('.zend_inputs').hide(); 
		displayWindow(true, e);
		optionWindowConfig();
	});

	$('#edit_window').click(function(e) {
		e.stopPropagation();

	});
	
	$('.select_action_form').change(function(e) {
		e.stopPropagation();
		optionWindowConfig();
	});
	
	function initWindow(){
		$('#edit_window').hide();  
		$('#select_action').prepend('<tr class="category_table_static"><td><select class="select_action_form" name="select_action_menu">'+
  									'<option value="add" selected="selected">Add</option>'+
									'<option value="edit">Edit</option>'+
									'<option value="delete">Delete</option>'+
									'</select></td></tr>'
		);
		
		$('#select_action').prepend('<tr class="category_table_static"><td>Select Action to Perform: </td></tr>');	     
		$('#editWindow_buttons').before('<tr class="zend_row"><td><span id="deleteMessage"><h3>Category to be Deleted: </h3><p id="outputID"></p></span></td> </tr>'); 
		$('body').append('<input type="hidden" id="current_working_id" value="" />');
	}

	function displayWindow(showWindow, e) {
		if (!showWindow) {
			$('#edit_window').hide(100);
		} else {
			$('#edit_window').fadeIn(200);
		}
		e.stopPropagation();
	}
	
	function optionWindowConfig(){
		var id = $('#current_working_id').attr('value')
		var action = $(".select_action_form :selected").val();
		$('.zend_row').hide();
		switch(action){
			case "add":
				$("input#cname").parent().parent().show();
				$("input#parent_id").parent().parent().show();
				$("input#status").parent().parent().show();
				addCategoryWindowConfig(id);
			break;
			case "edit":
				$("input#cname").parent().parent().show();
				$("input#parent_id").parent().parent().show();
				$("input#status").parent().parent().show();
				editCategoryWindowConfig(id);
			break;
			case "delete":
				$('#deleteMessage').parent().parent().show();
				
				deleteCategoryWindowConfig(id);
			break;
			default:
			break;
		}
	}
	
	function addCategoryWindowConfig(id) {
		var inputs = [];
		inputs = $("input." + id).map(function() {
			return $(this).val();
		}).get();
		$("input#status").val(inputs[1]);
		$("input#parent_id").val(inputs[3]);
		$("input#category_id").val(inputs[3]);
	}

	function editCategoryWindowConfig(id) {
		var inputs = [];
		inputs = $("input." + id).map(function() {
			return $(this).val();
		}).get();
		$("input#cname").val(inputs[0]);
		$("input#status").val(inputs[1]);
		$("input#parent_id").val(inputs[2]);
		$("input#category_id").val(inputs[3]);
				console.log($("input#category_id"));
	}

	function deleteCategoryWindowConfig(id) {
		var inputs = [];
		inputs = $("input." + id).map(function() {
			return $(this).val();
		}).get();
		$("#outputID").text(inputs[0]);
		$("input#category_id").val(inputs[3]);
	}

});

