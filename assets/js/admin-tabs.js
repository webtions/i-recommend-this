/**
 * JavaScript for handling the tabbed interface in the plugin settings.
 *
 * @package IRecommendThis
 */

(function($) {
	'use strict';

	$(document).ready(function() {
		// Save the active tab in session storage when clicked
		$('.nav-tab').on('click', function() {
			// Get the tab from the URL
			var tab = $(this).attr('href').split('tab=')[1];

			// Store the active tab
			if (typeof(Storage) !== "undefined") {
				sessionStorage.setItem('irecommendthisActiveTab', tab);
			}
		});
	});
})(jQuery);
