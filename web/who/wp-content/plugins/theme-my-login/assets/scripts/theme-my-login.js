( function ( $ ) {

	$( '.tml' ).on( 'submit', 'form[data-ajax="1"]', function( e ) {
		var form = $( this ),
			input = form.find( ':input' ),
			submit = form.find( ':submit' ),
			container = $( e.delegateTarget ),
			notices = container.find( '.tml-alerts' );

		e.preventDefault();

		notices.empty();

		input.prop( 'readonly', true );
		submit.prop( 'disabled', true );

		$.ajax( {
			data: form.serialize() + '&ajax=1',
			method: form.attr( 'method' ) || 'get',
			url: form.attr( 'action' )
		} )
		.always( function() {
			input.prop( 'readonly', false );
			submit.prop( 'disabled', false );
		} )
		.done( function( response ) {
			if ( response.success ) {
				if ( response.data.refresh ) {
					location.reload( true );
				} else if ( response.data.redirect ) {
					location.href = response.data.redirect;
				} else if ( response.data.notice ) {
					notices.hide().html( response.data.notice ).fadeIn();
				}
			} else {
				notices.hide().html( response.data.errors ).fadeIn();
			}
		} )
		.fail( function( jqXHR, textStatus, errorThrown ) {
			if ( jqXHR.responseJSON.data.errors ) {
				notices.hide().html( jqXHR.responseJSON.data.errors ).fadeIn();
			}
		} );
	} );
} )( jQuery );

( function ( $ ) {

	$( initFocus );

	function initFocus() {
		var userLogin, key;

		if ( ! themeMyLogin.action ) {
			return;
		}

		userLogin = $( '#user_login' );

		switch ( themeMyLogin.action ) {
			case 'activate' :
				key = $( '#key' );
				if ( key.length ) {
					key.focus();
				}
				break;

			case 'lostpassword' :
			case 'retrievepassword' :
			case 'register' :
				userLogin.focus();
				break;

			case 'resetpass' :
			case 'rp' :
				$( '#pass1' ).focus();
				break;

			case 'login' :
				if ( -1 != themeMyLogin.errors.indexOf( 'invalid_username' ) ) {
					userLogin.val( '' );
				}

				if ( userLogin.val() ) {
					$( '#user_pass' ).focus();
				} else {
					userLogin.focus();
				}
				break;
		}
	}
} )( jQuery );

( function( $ ) {
	function checkPasswordStrength() {
		var pass1 = $( '#pass1' ).val(),
			result = $( '#pass-strength-result' ),
			strength;

		result.removeClass('short bad good strong');
		if ( ! pass1 ) {
			result.html( '&nbsp;' );
			return;
		}

		strength = wp.passwordStrength.meter( pass1, wp.passwordStrength.userInputBlacklist(), pass1 );

		switch ( strength ) {
			case -1:
				result.addClass( 'bad' ).html( pwsL10n.unknown );
				break;
			case 2:
				result.addClass( 'bad' ).html( pwsL10n.bad );
				break;
			case 3:
				result.addClass( 'good' ).html( pwsL10n.good );
				break;
			case 4:
				result.addClass( 'strong' ).html( pwsL10n.strong );
				break;
			case 5:
				result.addClass( 'short' ).html( pwsL10n.mismatch );
				break;
			default:
				result.addClass( 'short' ).html( pwsL10n['short'] );
		}
	}

	$( document ).ready( function() {
		$( '#pass1' ).val( '' ).on( 'keyup paste', checkPasswordStrength );
	} );
} )( jQuery );
