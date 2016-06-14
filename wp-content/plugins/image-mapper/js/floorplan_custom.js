
jQuery(document).ready(function ($) {
    function getSizeImg() {
        $("img.floorplan_image").attr({"data-width": $("img.floorplan_image").outerWidth(true),
            "data-height": $("img.floorplan_image").outerHeight(true)});
    }
    getSizeImg();
    $("img.floorplan_image").load(function () {
        getSizeImg();
    });

    $('#myModal').on('hidden.bs.modal', function () {
        jQuery('#myModal .modal-footer .mark-remove').remove();
    })
    var newImage = 0;
    var curtop = 0;
    var curleft = 0;
    var markertop = 0;
    var markerleft = 0;
    //Counter
    counter = 0;
    jQuery(document).on("click", ".box-clone", function (event) {
        var offset = jQuery(this).offset();
        var xPos = offset.left;
        var yPos = offset.top;
        markertop = yPos - curtop;
        markerleft = xPos - curleft;
        jQuery('#left_val').val(markerleft);
        jQuery('#top_val').val(markertop);
    });

    jQuery("#droppable").droppable({
        accept: '.box',
        drop: function (event, ui) {
            counter++;
            var offset = jQuery('#droppable').offset();
            var xPos = offset.left;
            var yPos = offset.top;
            curtop = yPos;
            curleft = xPos;
            if (navigator.userAgent.indexOf('Safari') != -1 && navigator.userAgent.indexOf('Chrome') == -1) {
                var cloned = jQuery(ui.helper).clone(true);
                jQuery(this).append(cloned);
                var Offset = jQuery(cloned).offset();
                jQuery(cloned).removeAttr('style');
                jQuery("#droppable .box").addClass("box-clone");
                jQuery(".box-clone").removeClass("ui-draggable box");
                jQuery(".box-clone").attr("href", "#myModal");
                newImage += 1;
                jQuery(cloned).attr("id", "newimg_" + newImage);
                jQuery(".box-clone").attr("onclick", "setTrashEdit(this)");
                //jQuery(".box-clone").wrap('<div></div>');
                jQuery(".box-clone").draggable({
                    containment: 'parent'
                });
                var btnOffset = jQuery('#source-btn').offset();
                var newImgLength = jQuery('a[id^="newimg_"]').length;
                var width = 0;
                if (jQuery('a[id^="newimg_"]').length > 1) {
                    width = jQuery('#newimg_' + newImgLength).width();
                    //Offset.left = Offset.left - width*newImgLength;
                }


                jQuery("#newimg_" + newImage).css('left', Offset.left - btnOffset.left);
                jQuery("#newimg_" + newImage).css('top', Offset.top - btnOffset.top);
                clearFoorImageForm();
            } else
            {
                var cloned = jQuery(ui.helper).clone(true);
                jQuery(this).append(cloned);
                cloned.removeClass('ui-draggable box').addClass('box-clone');
                cloned.attr("href", "#myModal");
                newImage += 1;
                cloned.attr("id", "newimg_" + newImage);
                cloned.attr("onclick", "setTrashEdit(this)");
                jQuery(".box-clone").draggable({
                    containment: 'parent'
                });
                clearFoorImageForm();
            }
            jQuery('#source-btn').fadeOut('fast');
            jQuery('#fp-instruction-msg').html('Please slide your circe in position on the image and click to add content!');
        }
    });

    jQuery(".box").draggable({
        containment: 'droppable', //it's deifne container area for the drag and drop      
        helper: 'clone',
        start: function (event, ui) {
            if (navigator.userAgent.indexOf('Safari') != -1 && navigator.userAgent.indexOf('Chrome') == -1) {
                jQuery(ui.helper).css("margin-left", event.clientX - jQuery(event.target).offset().left);
                jQuery(ui.helper).css("margin-top", event.clientY - jQuery(event.target).offset().top);
            }
        }
    });

});

function add_edit_room_details() {
    if (jQuery('#upload_img').val() == "" && jQuery('#existing-floor-image').attr('src') == '') {
        jQuery('#err-msg').html('Please select an image.');
    } else {
        var post_id = jQuery('#post_id').val();
        var marker_img_id = jQuery('#marker_img_id').val();
        var imageUrl = jQuery('#existing-floor-image').attr('src');
        var roomTitle = jQuery('#img-title').val();
        var roomDescription = jQuery('#description').val();
        var roomLink = jQuery('#link').val();
        var divOffset = jQuery('#droppable').offset()
        var Offset = jQuery('#' + marker_img_id).offset();
        var markerTop = jQuery('#' + marker_img_id).css('top');//Offset.top - divOffset.top;
        var markerLeft = jQuery('#' + marker_img_id).css('left'); //Offset.left - divOffset.left;
        markerTop = markerTop.split('px');
        markerLeft = markerLeft.split('px');
        markerTop = markerTop[0];
        markerLeft = markerLeft[0];
        var img = jQuery(".floorplan_image");
        var mark_left = markerLeft;
        var mark_left_percent = mark_left * 100 / img.attr("data-width") + "%";
        var mark_top = markerTop;
        var mark_top_percent = mark_top * 100 / img.attr("data-height") + "%";
        var data = {
            action: 'room_details',
            post_id: post_id,
            marker_img_id: marker_img_id,
            marker_image_top: mark_top_percent,
            marker_image_left: mark_left_percent,
            roomImage: imageUrl,
            roomTitle: roomTitle,
            /* roomDimension:roomDimension,
             roomCapacity:roomCapacity, */
            roomDescription: roomDescription,
            roomLink: roomLink,
        };
        jQuery.post(ajaxurl, data, function (response) {
            jQuery('.modal-header .close').trigger('click');
            var res = jQuery.parseJSON(response);
            if (res.images.id != marker_img_id) {
                var newImage = "<a id=\"" + res.images.id + "\"  class=\"mark-feature\"  style=\"position: absolute; top:" + mark_top_percent + "; left:" + mark_left_percent + "; \" onclick=\"editMarkerData('" + res.images.id + "')\" data-toggle=\"modal\" role=\"button\" href=\"#myModal\" >";
                newImage += "</a>";
                jQuery('#' + marker_img_id).remove();
                jQuery('#droppable').append(newImage);
            }
            jQuery('#mark_count').val(res.count);
            jQuery('#source-btn').fadeIn('fast');
        });
    }
}

function addToFancyboxWrap(obj) {
    obj = jQuery(obj).parent();
    jQuery('#fancybox-wrap').append("<div onclick=\"delete_room('" + jQuery(obj).attr('id') + "');\" class=\"floor-plan-trash\"></div><a id=\"newimg\" data-toggle=\"modal\" role=\"button\" href=\"#myModal\"><div  onclick=\"editMarkerData('" + jQuery(obj).attr('id') + "');\" class=\"floor-plan-edit\"></div></a>");
}

function editMarkerData(obj) {
    jQuery('.floor-plan-new-trash').removeAttr('onclick');
    jQuery('.floor-plan-new-trash').attr('onclick', "delete_room('" + obj + "')")
    clearFoorImageForm();
    jQuery('#fancybox-close').trigger('click');
    jQuery('#existing-floor-image').attr('src', '')
    jQuery('#myModal .modal-footer').append("<button type=\"button\" class=\"mark-remove btn btn-danger\" data-dismiss=\"modal\" onclick=\"delete_room('" + obj + "');\">Delete</button>");
    var data = {
        type: 'json',
        action: 'floorplan_get_image',
        marker_id: obj
    };
    jQuery.post(ajaxurl, data, function (response) {
        var img = jQuery.parseJSON(response);
        jQuery('#existing-floor-image').attr('src', img.img);
        jQuery('#img-title').val(img.title);
        jQuery('#description').val(img.description);
        jQuery('#link').val(img.link);
    });

    jQuery('#marker_img_id').val(obj);
    var top = jQuery('#' + obj).css('top');
    top = top.split('%')
    jQuery('#top_val').val(top[0]);
    var left = jQuery('#' + obj).css('left');
    left = left.split('%')
    jQuery('#left_val').val(left[0]);
}

function delete_room(marker_id) {
    var data = {
        action: 'floorplan_delete_changes_action',
        marker_id: marker_id,
        q: 'deleteRoom',
    };
    jQuery.post(ajaxurl, data, function (response) {
        var res = jQuery.parseJSON(response);
        if (res.result) {
            //jQuery('#'+marker_id).remove();
            removeNewDiv(marker_id);
            jQuery('#fancybox-close').trigger('click');
        }
    });
}

function setTrashEdit(obj)
{
    jQuery('#existing-floor-image').attr('src', '');
    jQuery('#img-title').val('');
    jQuery('#description').val('');
    jQuery('#link').val('');
    if (jQuery('#myModal .modal-footer .mark-remove').length < 1) {
        jQuery('#myModal .modal-footer').append("<button type=\"button\" class=\"mark-remove btn btn-danger\" data-dismiss=\"modal\" onclick=\"removeNewDiv('" + obj.id + "');\">Delete</button>");
    }
    jQuery('#marker_img_id').val(obj.id);
}

function removeNewDiv(id)
{
    jQuery('#' + id).remove();
    jQuery('.close').trigger('click');
    jQuery('#source-btn').fadeIn('fast');
    jQuery('#fp-instruction-msg').html('Please drag the circle to your image map');
    jQuery('#myModal .modal-footer .mark-remove').remove();
}


function clearFoorImageForm() {
    jQuery('#err-msg').html('');
    jQuery('#existing-floor-image').attr('src', '')
    jQuery('#img-title').val('');
    jQuery('#description').val('');
    jQuery('#link').val('');
}

function clearAllCameras() {
    var rooms = jQuery('#droppable').children();
    var post_id = jQuery('#postid').val();
    var arrRooms = new Array();
    var c = 0;
    for (var i = 0; i < rooms.length; i++) {
        if (jQuery(rooms[i]).attr('id')) {
            var id = jQuery(rooms[i]).attr('id');
            id = id.split('_');
            if (id[0] != 'newimg') {
                arrRooms[c] = jQuery(rooms[i]).attr('id');
                c++;
            }
        }
    }
    if (arrRooms.length > 0) {
        var data = {
            type: 'json',
            action: 'floorplan_delete_changes_action',
            marker_id: arrRooms,
            q: 'deleteAll',
            postid: post_id
        };
        jQuery.post(ajaxurl, data, function (response) {
            var res = jQuery.parseJSON(response);
            if (res.result) {
                jQuery('#droppable a').remove();
                jQuery('#source-btn').fadeIn('fast');
                jQuery('#mark_count').val('');
            }
        });
    } else {
        jQuery('#droppable a').remove();
        jQuery('#source-btn').fadeIn('fast');
    }
}

function deleteImage() {
    jQuery('#image_id').val('');
    jQuery('#droppable').html('');
    clearAllCameras();
}


