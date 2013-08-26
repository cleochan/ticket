$(document).ready(function() {
	
	var categoryName;
	//Hide window and bind hide window to clicking on document
	initWindow();
	
	$('body').bind("click", function(e) {
		displayWindow(false, e);
		resetWindow();
	})
	
	$("#add_category_link").click(function(e) {
		$('#edit_window').hide();
		$('#select_action_comment').hide();
		$('#edit_form_title').text('Create New Category');
		$('#deleteMessage').parent().parent().hide();
		$("input#cname").parent().parent().show();
		displayWindow(true, e);
		$('.select_action_form').hide().val("edit");;
		addNewCategoryWindowConfig(0);
	});
	
	$('#close_btn').click(function(e){
		displayWindow(false, e);
		resetWindow();
	});
	
	$(".categoryLink").click(function(e) {
		$('#current_working_id').attr('value', $(this).attr('value'));
		categoryName = $(this).text().trim();
		$('#edit_window').hide();
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
	
	$('input#cname').focus(function(e){
		$('input#cname').val("");
	});
	
	function initWindow(){
		$('#edit_window').hide();
		resetWindow(); 
		$('#select_action').prepend('<tr class="category_table_static"><td><select class="select_action_form" name="select_action_menu">'+
  									'<option value="add">Add</option>'+
									'<option value="edit" selected="selected">Edit</option>'+
									'<option value="delete">Delete</option>'+
									'</select></td></tr>')
						   .prepend('<tr class="category_table_static" id="select_action_comment"><td>Select Action to Perform: </td></tr>')
						   .prepend('<div class="ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix"><h2 id="edit_form_title" class="ui-dialog-title"></h2></div>');	
		
		$('body').append('<input type="hidden" id="current_working_id" value="" />');
		
		$("body").append("<div id='overlay' class='ui-widget-overlay' ></div>");
	    $("#overlay")
	      .height($(document).height())
	      .css({
	         'opacity' : 0.2,
	         'position': 'absolute',
	         'top': 0,
	         'left': 0,
	         'background-color': 'black',
	         'width': '100%',
	         'z-index': 99
	      })
	      .hide();

	}
	
	function resetWindow(){
		$('.select_action_form').show();
		$('#select_action_comment').show();
		$('.zend_row').hide();
	}

	function displayWindow(showWindow, e) {
		if (!showWindow) {
			$('#edit_window').hide();
			fadeBackground(false);
		} else {
			$('#edit_window').fadeIn(200);
			fadeBackground(true);
		}
		e.stopPropagation();
	}
	
	function fadeBackground(fade){
		if(fade){
			$("#overlay").fadeIn(180);
		}else{
			$("#overlay").fadeOut(100);
		}
		
	}
	
	function optionWindowConfig(){
		var id = $('#current_working_id').attr('value')
		var action = $(".select_action_form :selected").val();
		resetWindow();
		$('#edit_form_title').text(categoryName);
		switch(action){
			case "add":
				$("input#cname").parent().parent().show();
				addCategoryWindowConfig(id);
			break;
			case "edit":
				$("input#cname").parent().parent().show();
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
	
	function addNewCategoryWindowConfig(id) {
		var inputs = [];
		inputs = $("input." + id).map(function() {
			return $(this).val();
		}).get();
		$("input#cname").val('Please Enter Category Name');
		$("input#status").val(inputs[1]);
		$("input#parent_id").val(0);
		$("input#category_id").val(inputs[3]);
	}
	
	function addCategoryWindowConfig(id) {
		var inputs = [];
		inputs = $("input." + id).map(function() {
			return $(this).val();
		}).get();
		$("input#cname").val('Please Enter Category Name');
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
	}

	function deleteCategoryWindowConfig(id) {
		var inputs = [];
		inputs = $("input." + id).map(function() {
			return $(this).val();
		}).get();
		//$("#deleteMessage p").text("").append(inputs[0]);
		$("input#category_id").val(inputs[3]);
	}

});

