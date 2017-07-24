jQuery(document).ready( function($) {
	/*jQuery( "#create-order-btn" ).on( "click", function() {
		var productname = jQuery( "select.slct-product :selected" ).text();
		var buyer_name = jQuery('#fio-ord').val();
		var email = jQuery('#inputEmail3').val();
		var delivery_method = $("select.slct-method :selected").text();

	  	var data = {
			'action': 'my_action',
			'ajax_productname': productname,
			'ajax_buyer_name': buyer_name,
			'ajax_email': email,
			'ajax_delivery_method': delivery_method
		};
	  jQuery.post( window.wp_data.ajaxurl, data, function( response ) {
			alert(response);
		});
	});*/

    jQuery('#create-order-btn').on("click",function() {
    	var error = false;
        if(jQuery('#inputEmail3').val() != '') {
            var pattern = /^([a-z0-9_\.-])+@[a-z0-9-]+\.([a-z]{2,4}\.)?[a-z]{2,4}$/i;
            if(pattern.test(jQuery('#inputEmail3').val())){
                jQuery('#inputEmail3').css({'border' : '1px solid #569b44'});
                jQuery('#email-valid').text('');
            } else {
            	error = true;
                jQuery('#inputEmail3').css({'border' : '1px solid #ff0000'});
                jQuery('email-valid').text('Не верно');
            }
        } else {
            jQuery('#inputEmail3').css({'border' : '1px solid #ff0000'});
            jQuery('#email-valid').text('Поле email не должно быть пустым');
        }
        if (jQuery('#fio-ord').val() == '') {
        	error = true;
        	jQuery('#fio-valid').text('Поле Ф.И.О. не должно быть пустым');
        }
        else{
        	jQuery('#fio-valid').text('');
        };
        if (!error) {
        	var productname = jQuery( "select.slct-product :selected" ).text();
			var buyer_name = jQuery('#fio-ord').val();
			var email = jQuery('#inputEmail3').val();
			var delivery_method = $("select.slct-method :selected").text();

		  	var data = {
				'action': 'my_action',
				'ajax_productname': productname,
				'ajax_buyer_name': buyer_name,
				'ajax_email': email,
				'ajax_delivery_method': delivery_method
			};
		  	jQuery.post( window.wp_data.ajaxurl, data, function( response ) {
				alert(response);
			});
        };
    });
})