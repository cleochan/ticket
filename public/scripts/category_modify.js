$(document).ready(function(){
       

	       $(".categoryLink").click(function(){

	       var id = $(this).attr('value');
	       var inputs = [];
	       inputs = $("input."+id).map(function(){ return $(this).val();}).get();
	       $("input#cname").val(inputs[0]);
	       $("input#status").val(inputs[1]);
	       $("input#parent_id").val(inputs[2]);
	       $("input#category_id").val(inputs[3]);
        });

});

