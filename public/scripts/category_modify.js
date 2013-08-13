$(document).ready(function() {
	
	$('#edit_window').hide();
	//Hide window and bind hide window to clicking on document
	$('#view-content').bind("click", function(e) {
		displayWindow(false, e);
	})

	$(".categoryLink").click(function(e) {
		var windowType = $("#window_type").val();
		$('#edit_window').hide();
		displayWindow(true, e);
		
		switch(windowType){
			case "edit":
				editCategoryWindowConfig(this);
			break;
			case "delete":
				deleteCategoryWindowConfig(this);
			break;
			default:
			break;
		}
		
	});

	$('#edit_window').click(function(e) {
		e.stopPropagation();

	});

	function displayWindow(showWindow, e) {
		if (!showWindow) {
			$('#edit_window').hide(100);
		} else {
			$('#edit_window').fadeIn(200);
		}
		e.stopPropagation();
	}

	function editCategoryWindowConfig(selected) {
		var id = $(selected).attr('value');
		var inputs = [];
		inputs = $("input." + id).map(function() {
			return $(this).val();
		}).get();
		$("input#cname").val(inputs[0]);
		$("input#status").val(inputs[1]);
		$("input#parent_id").val(inputs[2]);
		$("input#category_id").val(inputs[3]);
	}

	function deleteCategoryWindowConfig(selected) {
		var id = $(selected).attr('value');
		var inputs = [];
		inputs = $("input." + id).map(function() {
			return $(this).val();
		}).get();
		$("input#category_id").val(inputs[3]);
		$("#outputID").text(inputs[0]);
	}

});

