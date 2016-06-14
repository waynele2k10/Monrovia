jQuery(document).ready(function ($) {
    // Validate form
    $('#nav-menu-meta').submit(function (e) {
        if ($("#sortable2 li[data-post-status='publish']").length == 0 || 
            $("#sortable3 li[data-post-status='publish']").length == 0 ||
            $("#sortable4 li[data-post-status='publish']").length == 0 ||
            $("#sortable5 li[data-post-status='publish']").length == 0) {
            alert('Please add post for featured section!');
            return false;
        }else{
            return true;
        }
    });
    // highlight active post
    $("#sortable2 li.publish:last").css("border", "1px solid blue");
    $("#sortable3 li.publish:last").css("border", "1px solid blue");
    $("#sortable4 li.publish:last").css("border", "1px solid blue");
    $("#sortable5 li.publish:last").css("border", "1px solid blue");
    
    //
    $("#sortable2 li.publish:last input.mark-active").prop("checked", true);
    $("#sortable3 li.publish:last input.mark-active").prop("checked", true);
    $("#sortable4 li.publish:last input.mark-active").prop("checked", true);
    $("#sortable5 li.publish:last input.mark-active").prop("checked", true);
    $("ul.droptrue").sortable({
        connectWith: "ul",
        placeholder: "highlight"
    });

    $(".container-sortable").sortable({
        connectWith: "ul.dropfalse1",
        items: "li:not(.sortable-feature)",
        placeholder: "highlight",
        receive: function (event, ui) {
            setValue(ui.item);
        }
    });

    $("#sortable1, .container-sortable").disableSelection();
    $(document).on('click', '.container-sortable li:not(.sortable-feature)', function(e) {
        if(e.target == this) setValue(this);
    });
    function setValue(selector) {
        var str = jQuery(selector).attr('data-post-date');
        var id = jQuery(selector).attr('data-post-id');
        var status = jQuery(selector).attr('data-post-status');
        var res = str.substring(0, 16);
        var position = jQuery(selector).parent().attr('data-name');
        $("#datetime24").attr("value", res);
        $("#datetime24").attr("data-id", id);
        $("#datetime24").attr("data-position", position);
        $("#datetime24").attr("data-status", status);
        $("#datetime24").combodate('setValue', res);
        $("li[data-post-id='" + id + "'] .remove-post").remove();
        $("#" + id).append('<span class="remove-post">X</span>');
        $('#monrovia-popup').simplePopup({
            centerPopup: true,
            closed: function () {
                var id = jQuery('#datetime24').attr('data-id');
                var date = jQuery('#datetime24').attr('value');
                var position = jQuery('#datetime24').attr('data-position');
                var status = jQuery('#datetime24').attr('data-status');
                var input_position = '<input name="featured[item][' + id + '][position]" type="hidden" value="' + position + '">';
                var input_date = '<input name="featured[item][' + id + '][date]" type="hidden" value="' + date + '">';
                var input_status = '<input name="featured[item][' + id + '][status]" type="hidden" value="' + status + '">';
                $("li[data-post-id='" + id + "'] input").remove();
                var html_date = '<span class="post_date">'+date+'</span>';
                $("li[data-post-id='" + id + "'] .post_date").remove();
                $("#" + id).append('<span class="mark-active"><input type="radio" class="mark-active" name="featured['+position+']" value="'+id+'"></span>');
                $("#" + id).append(html_date);
                $("#" + id).append(input_position);
                $("#" + id).append(input_date);
                $("#" + id).append(input_status);
            }
        });
    }
});
