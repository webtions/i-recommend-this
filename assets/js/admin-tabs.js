/**
 * JavaScript for handling the tabbed interface in the plugin settings.
 *
 * This script saves the currently active tab to the browser's session storage
 * so that when the user navigates away and returns, they can be redirected
 * back to the same tab they were viewing.
 *
 * @package IRecommendThis
 */

( function ( $ ) {
	'use strict';

	$( document ).ready(
		function () {
			// Save the active tab in session storage when clicked.
			$( '.nav-tab' ).on(
				'click',
				function () {
					// Get the tab from the URL.
					var tab = $( this ).attr( 'href' ).split( 'tab=' )[1];

					// Only store the tab if we found it in the URL.
					if ( tab ) {
						// Store the active tab if session storage is available.
						if ( typeof( Storage ) !== "undefined" ) {
							sessionStorage.setItem( 'irecommendthisActiveTab', tab );
						}
					}
				}
			);

			// Check if we should restore a previously selected tab.
			if ( typeof( Storage ) !== "undefined" ) {
				var savedTab = sessionStorage.getItem( 'irecommendthisActiveTab' );

				// If there's a saved tab and we're on the settings page without an explicit tab parameter.
				if ( savedTab && window.location.href.indexOf( 'tab=' ) === -1 ) {
					// Redirect to the saved tab.
					var baseUrl = window.location.href.split( '#' )[0];
					if ( baseUrl.indexOf( '?' ) === -1 ) {
						window.location.href = baseUrl + '?tab=' + savedTab;
					} else {
						window.location.href = baseUrl + '&tab=' + savedTab;
					}
				}
			}
		}
	);

} )( jQuery );
