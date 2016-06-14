jQuery( document ).ready(function( $ ) {
//$(document).ready(function(){

    console.log('csv-active');
	var csvImport = {};//for debugging
	var  $csvForm = $('#csv-import-form')
		,$pbar 	  = $("#csv-import-form-progress-bar")
		,$status  = $("#csv-import-form-status")
		,$percent = $("#csv-import-form-progress-percent")
		,$files = $("#csv-import-form-files")
		,$result  = $('#csv-import-form-result');

    function sendIDtoResults(data){
        $.ajax({
            type: "POST",
            url: "some.php",
            data: { name: "John", location: "Boston" }
        })
        .done(function( msg ) {
            alert( "Data Saved: " + msg );
        });
    }

    var options = { 
    	beforeSend: function() {
    		//clear everything
	        $pbar.show();
	        $result.html("");    
	        $pbar.width('0%');
	        $status.html("");
	        $percent.html("0%");
    	},
    	uploadProgress: function(event, position, total, percentComplete) 
    	{
        	$pbar.width(percentComplete+'%');
        	$percent.html(percentComplete+'%');
 
    	},
    	success: function() 
    	{
        	$pbar.width('100%');
        	$percent.html('100%');
    	},
    	complete: function(response) 
    	{
    		var csvData = $.parseJSON(response.responseText);
    		csvImport = csvData;

    		if ( csvData.status ){
				$('#csv-import-form').trigger('reset');
        		$status.html("<font color='green'>File '"+csvData.fname+"' uploaded</font>");        	
        		$result.html( csvData.content.length+" Plant ID's found" );
				if ($files.html() == "") {
					$files.html(csvData.fname);
				} else {
					$files.html($files.html() + "<br>" +csvData.fname);
				}
				
                perform_search('res_per_page='+(csvData.content.length + 1)+'&item_number='+csvData.content.join(",") );
                setTimeout(function(){
                    select_all();
                },1000);
                //item_number=789%2C456
        	}else{
        		$status.html("<font color='red'>An Error has occured: "+ csvData.msg +"</font>");
        	}
    	},
    	error: function()
    	{
        	$status.html("<font color='red'> ERROR: unable to upload file. Be sure the type of file your uploading is a .csv</font>");

    	}    	
	}; 
	$('#csv-import-form').on('submit', function(e){
		if ($('#csv-file').val() == "") {
			e.preventDefault();
			e.stopPropagation();
			return false;
		}
	});
	$csvForm.ajaxForm(options);
	$('#csv-file').change(function(){
		if ($files.html() != "") {
			var vals = $(this).val(),
			val = vals.length ? vals.split('\\').pop() : '';
			var file_text = $files.html();
			var file_array = file_text.split('<br>');
			$.each(file_array, function(index, value) { 
				if (value == val) {
					alert(val + " is duplicate!");
					$('#csv-import-form').trigger('reset');
					return;
				}
			});
		}
	});
});