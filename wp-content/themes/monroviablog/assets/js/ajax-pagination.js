(function($) {

	var pageNumber = 1;
	var month = "";
	var year = "";
	function load_posts(){
		pageNumber++;
		
		if ($('select.month').length) {
			month = $('select.month').val();
		}
		
		if ($('select.year').length) {
			year = $('select.year').val();
		}
		
		$.ajax({
			type: "POST",
			dataType: "html",
			url: ajax_posts.ajaxurl,
			data: {
				action: 'more_post_ajax',
				query_vars: ajax_posts.query_vars,
				page: pageNumber,
				year: year,
				monthnum: month
			},
			success: function(data){
				var $data = $(data);
				if($.trim(data)){
					$(".list-post ul").append($data);
					$("#pagination").attr("disabled",false);
					$("#pagination").html("LOAD MORE POSTS");
				} else{
					$("#pagination").attr("disabled",true);
					$("#pagination").html("No more post!");
				}
                                $.resizeList('.list-post > ul');
			},
			error : function(jqXHR, textStatus, errorThrown) {
				$loader.html(jqXHR + " :: " + textStatus + " :: " + errorThrown);
			}

		});
		return false;
	}

	$("#pagination").on("click",function(event){ // When btn is pressed.
		event.preventDefault();
		$("#pagination").attr("disabled",true);
		$("#pagination").html("LOADING...!");
		load_posts();
	});

})(jQuery);