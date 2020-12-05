jQuery(document).ready(function($){

	// Color Picker
	$('.mts-color-picker-field').wpColorPicker();

    // Image Uploader
	mtsCatImageField = {

		uploader : function( id ) {

			var frame = wp.media({
				title : mtsCatVars.imgframe_title,
				multiple : false,
				library : { type : 'image' },
				button : { text : mtsCatVars.imgbutton_title }
			});

			frame.on('close',function( ) {
				var attachments = frame.state().get('selection').toJSON();
				if (attachments[0]) {
					$("#" + id + '-preview').html('<img src="' + attachments[0].url + '" class="mts_image_upload_img" />');
					$("#" + id + '_id').val(attachments[0].id);
					$("#" + id + '_url').val(attachments[0].url);

					if ( $("#" + id + '-upload+.mts-clear-image').length == 0 ) {
						$("#" + id + '-upload').after('<a href="#" class="mts-clear-image">'+mtsCatVars.imgremove_title+'</a>');
					}
				}
			});

			frame.open();
			return false;
		}
	};

	$(document).on('click', '.mts-clear-image', function(e){
		e.preventDefault();
		var $this = $(this),
			id = $this.prev().data('id');

		$("#" + id + '-preview').html('');
		$("#" + id + '_id').val('');
		$("#" + id + '_url').val('');
		$this.remove();
	});

	var addTagClicked = '';
	$('form#addtag input#submit').click( function(e) {

		var form = $(this).parents('form');

		if ( validateForm( form ) ) {
			
			addTagClicked = 'clicked';
		}
	});

	$(document).on( 'ajaxComplete', function() {

		if ( $('body').hasClass('edit-tags-php') ) {

			if ( 'clicked' === addTagClicked ) {

				$('.mts-color-picker-field').wpColorPicker( 'color', '' );
				$('.wp-color-result').removeAttr('style');
				$('.mts-clear-image').click();
			}

			addTagClicked = '';
		}
	});
});