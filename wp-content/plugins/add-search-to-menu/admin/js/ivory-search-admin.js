( function( $ ) {

	'use strict';

	if ( typeof ivory_search === 'undefined' || ivory_search === null ) {
		return;
	}

	$( function() {

		$( window ).load( function() {
			$( '.col-wrapper .load-all' ).on( 'click', function() {
				var post_id = $('#post_ID').val();
				var post_type = $(this).attr('id');
				var this_load = $(this);
				var inc_exc = $('.search-form-editor-panel').attr('id');
				$(this).parent().append('<span class="spinner"></span>');
				$.ajax( {
					type : "post",
					url: ivory_search.ajaxUrl,
					data: {
						action: 'display_posts',
						post_id: post_id,
						post_type: post_type,
						inc_exc: inc_exc
					},
					success: function( response ) {
						$(this_load).parent().find('select').find('option').remove().end().append(response );
						if ( $(this_load).parent().find('select option:selected').length ) {
							$(this_load).parent().find('.col-title span').html( '<strong>'+$(this_load).parent().find('.col-title').text()+'</strong>');
						}
						$(this_load).parent().find('.spinner').remove();
						$(this_load).remove();
					},
					error: function (request, error) {
						alert( " The posts could not be loaded. Because: " + error );
					}
				} );
			} );
		} );

		$('.form-table .actions a.expand').click( function() {
			$('.form-table .actions a.expand').hide();
			$('.form-table .ui-accordion-content, .form-table .actions a.collapse').show();
			$('.form-table .ui-accordion-content').addClass('ui-accordion-content-active');
			$('.form-table h3').addClass('ui-state-active');
			return false;
		} );
		
		$('.form-table .actions a.collapse').click( function() {
			$('.form-table .actions a.expand').show();
			$('.form-table .ui-accordion-content, .form-table .actions a.collapse').hide();
			$('.form-table .ui-accordion-content').removeClass('ui-accordion-content-active');
			$('.form-table h3').removeClass('ui-state-active');
			return false;
		} );

		$( ".form-table" ).accordion( {
			collapsible: true,
			heightStyle: "content",
			icons: false,
		} );

		$('#search-body select[multiple] option').mousedown(function(e) {
			if ($(this).attr('selected')) {
				$(this).removeAttr('selected');
				return false;
			}
		} );

		$( ".col-title .list-search" ).keyup(function() {
			var search_val = $(this).val().toLowerCase();
			var search_sel = $(this).parent().parent().find('select option');
			$( search_sel ).each(function() {
				if ( $(this).text().toLowerCase().indexOf( search_val ) === -1 ) {
					$(this).fadeOut( 'fast' );
				} else {
					$(this).fadeIn( 'fast' );
				}
			} );
		} );

		$( ".list-search.wide" ).keyup(function() {
			var search_val = $(this).val().toLowerCase();
			var search_sel = $(this).parent().find('select option');
			$( search_sel ).each(function() {
				if ( $(this).text().toLowerCase().indexOf( search_val ) === -1 ) {
					$(this).fadeOut( 'fast' );
				} else {
					$(this).fadeIn( 'fast' );
				}
			} );
		} );

		if ( '' === $( '#title' ).val() ) {
			$( '#title' ).focus();
		}

                if ( 0 !== $( '#title' ).length ) {
                    ivory_search.titleHint();
                }

		var changed = false;

		$(document).on("submit", "form", function(event){
			changed = false;
			$(window).off('beforeunload');
		} );

		$( window ).on( 'beforeunload', function( event ) {

			$( '#search-body :input[type!="hidden"]' ).each( function() {
				if ( $( this ).is( ':checkbox, :radio' ) ) {
					if ( this.defaultChecked != $( this ).is( ':checked' ) ) {
						changed = true;
					}
				} else if ( $( this ).is( 'select' ) ) {
					$( this ).find( 'option' ).each( function() {
						if ( this.defaultSelected != $( this ).is( ':selected' ) && '0' != $( this ).val() && 'Default Search Form' != $( this ).text() ) {
							changed = true;
						}
					} );
				} else {
					if ( this.defaultValue != $( this ).val() ) {
						changed = true;
					}
				}
			} );

			if ( changed ) {
				event.returnValue = ivory_search.saveAlert;
				return ivory_search.saveAlert;
			}
		} );

		$( '#is-admin-form-element' ).submit( function() {
			if ( 'copy' != this.action.value ) {
				$( window ).off( 'beforeunload' );
			}

			if ( 'save' == this.action.value ) {
				$( '#publishing-action .spinner' ).addClass( 'is-active' );
			}
		} );

		// Tooltip only Text
		$('#search-body #titlewrap').hover(function(){
			if($(this).find("#title[disabled]").length){
			// Hover over code
			var title = $(this).find("#title[disabled]").attr('title');
			$(this).find("#title[disabled]").data('tipText', title).removeAttr('title');
			$('<p class="title_tooltip"></p>')
			.text(title)
			.appendTo('body')
			.fadeIn('slow');
			}
		}, function() {
			// Hover out code
			$(this).find("#title[disabled]").attr('title', $(this).find("#title[disabled]").data('tipText'));
			$('.title_tooltip').remove();
		}).mousemove(function(e) {
			var mousex = e.pageX + 20; //Get X coordinates
			var mousey = e.pageY - 40; //Get Y coordinates
			$('.title_tooltip')
			.css({ top: mousey, left: mousex })
		});


	} );

	ivory_search.titleHint = function() {
		var $title = $( '#title' );
		var $titleprompt = $( '#title-prompt-text' );

		if ( '' === $title.val() ) {
			$titleprompt.removeClass( 'screen-reader-text' );
		}

		$titleprompt.click( function() {
			$( this ).addClass( 'screen-reader-text' );
			$title.focus();
		} );

		$title.blur( function() {
			if ( '' === $(this).val() ) {
				$titleprompt.removeClass( 'screen-reader-text' );
			}
		} ).focus( function() {
			$titleprompt.addClass( 'screen-reader-text' );
		} ).keydown( function( e ) {
			$titleprompt.addClass( 'screen-reader-text' );
			$( this ).unbind( e );
		} );
	};

	function toggle_description_inputs() {
		if( $( '#_is_ajax-show_description' ).is(':checked') ) {
			$( '._is_ajax-description_source_wrap, ._is_ajax-description_length_wrap' ).removeClass('is-field-disabled').show();
		} else {
			$( '._is_ajax-description_source_wrap, ._is_ajax-description_length_wrap' ).addClass('is-field-disabled').hide();
		}
	}

	toggle_description_inputs();
	$( '._is_ajax-description_wrap .check-radio' ).on( 'click', function() {
		toggle_description_inputs();
	} );

	function toggle_details_box_fields() {
		if( $( '#_is_ajax-show_details_box' ).is(':checked') && ( ( $( '#_is_ajax-show_matching_categories' ).is(':checked') || $( '#_is_ajax-show_matching_tags' ).is(':checked') ) ) ) {
			$( '._is_ajax-product_list_wrap, ._is_ajax-order_by_wrap, ._is_ajax-order_wrap' ).removeClass('is-field-disabled').show();
		} else {
			$( '._is_ajax-product_list_wrap, ._is_ajax-order_by_wrap, ._is_ajax-order_wrap' ).addClass('is-field-disabled').hide();
		}
	}
        toggle_details_box_fields();
	$( '#_is_ajax-show_details_box, #_is_ajax-show_matching_categories, #_is_ajax-show_matching_tags' ).on( 'click', function() {
                    toggle_details_box_fields();
	} );

	function toggle_show_more_result_textbox_fields() {
		if( $( '#_is_ajax-show_more_result' ).is(':checked') ) {
			$( '._is_ajax-more_result_text_wrap' ).removeClass('is-field-disabled').show();
		} else {
			$( '._is_ajax-more_result_text_wrap' ).addClass('is-field-disabled').hide();
		}
	}
	toggle_show_more_result_textbox_fields();
	$( '._is_ajax-show_more_result_wrap .check-radio' ).on( 'click', function() {
		toggle_show_more_result_textbox_fields();
	} );

	function toggle_show_view_all_textbox_fields() {
		if( $( '#_is_ajax-view_all_results' ).is(':checked') ) {
			$( '._is_ajax-view_all_text_wrap' ).removeClass('is-field-disabled').show();
		} else {
			$( '._is_ajax-view_all_text_wrap' ).addClass('is-field-disabled').hide();
		}
	}
	toggle_show_view_all_textbox_fields();
	$( '._is_ajax-view_all_results_wrap .check-radio' ).on( 'click', function() {
		toggle_show_view_all_textbox_fields();
	} );

	function toggle_more_result_fields() {
		if( $( '#_is_ajax-show_more_result' ).is(':checked') ) {
			$( '._is_ajax-more_result_text_wrap' ).removeClass('is-field-disabled').show();
		} else {
			$( '._is_ajax-more_result_text_wrap' ).addClass('is-field-disabled').hide();
		}
	}
	toggle_more_result_fields();
	$( '._is_ajax-show_more_result_wrap .check-radio' ).on( 'click', function() {
		toggle_more_result_fields();
	} );

	// Enable AJAX.
	function toggle_enable_ajax() {
		if( $( '#_is_ajax-enable_ajax' ).is(':checked') ) {
			$( '.form-table-panel-ajax .is-field-wrap' ).removeClass('is-field-disabled');
		} else {
			$( '.form-table-panel-ajax .is-field-wrap' ).addClass('is-field-disabled');
		}
	}

	toggle_enable_ajax();
	
	$( '#_is_ajax-enable_ajax' ).on( 'click', function() {
		toggle_enable_ajax();
	} );

	$( '.form-table-panel-ajax .is-field-disabled-message .message' ).on( 'click', function() {
		$('#_is_ajax-enable_ajax').prop('checked', true);
		toggle_enable_ajax();
	} );

	// Enable Customize Fields.
	function toggle_enable_customize() {
		if( $( '#_is_customize-enable_customize' ).is(':checked') ) {
			$( '.form-table-panel-customize .is-field-wrap' ).removeClass('is-field-disabled');
		} else {
			$( '.form-table-panel-customize .is-field-wrap' ).addClass('is-field-disabled');
		}
	}

	toggle_enable_customize();
	
	$( '#_is_customize-enable_customize' ).on( 'click', function() {
		toggle_enable_customize();
                window.setTimeout(function () {
                alert( 'Please save the changes.' );
                }, 300);
	} );

	$( '.form-table-panel-customize .is-field-disabled-message .message' ).on( 'click', function() {
		$('#_is_customize-enable_customize').prop('checked', true);
		toggle_enable_customize();
                window.setTimeout(function () {
                alert( 'Please save the changes.' );
                }, 300);
	} );

} )( jQuery );
