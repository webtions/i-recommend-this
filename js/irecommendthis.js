/**
 * I Recommend This Plugin Script
 *
 * This script handles the AJAX-based "recommend" functionality in WordPress.
 * It listens for clicks on elements with the .irecommendthis class and sends
 * an AJAX request to update the recommendation count.
 *
 * @package   IRecommendThis
 * @version   1.0
 */
jQuery(function($) {
	/**
	 * Cache global AJAX settings and parse options from the localized variable `irecommendthis`.
	 */
	var ajaxSettings = (typeof irecommendthis !== 'undefined') ? irecommendthis : null;
	if (!ajaxSettings) {
		console.error('AJAX settings not found - plugin may not be properly initialized');
		return;
	}

	var options = {};
	try {
		options = JSON.parse(ajaxSettings.options);
	} catch (e) {
		console.error('Error parsing options JSON:', e);
	}

	/**
	 * Helper function to extract the post ID from various possible sources:
	 *   1. data-post-id attribute
	 *   2. The element's ID (e.g., #irecommendthis-123)
	 *   3. A class name (e.g., .irecommendthis-post-123)
	 *
	 * @param {object} $el - The jQuery element from which to extract the post ID.
	 * @return {string|undefined} - The extracted post ID or undefined if not found.
	 */
	function getPostId($el) {
		var postId = $el.data('post-id');

		// Fallback: extract ID from element ID
		if (!postId && $el.attr('id')) {
			postId = $el.attr('id').replace('irecommendthis-', '');
		}

		// Fallback: extract ID from class name
		if (!postId) {
			$.each($el.attr('class').split(' '), function(_, className) {
				if (className.indexOf('irecommendthis-post-') === 0) {
					postId = className.replace('irecommendthis-post-', '');
					return false; // Break out of the loop once we find it
				}
			});
		}

		return postId;
	}

	/**
	 * Because we don't know where the like button might appear in the DOM,
	 * we use event delegation on the document.
	 */
	$(document).on('click', '.irecommendthis', function(event) {
		event.preventDefault();
		var $link = $(this);

		// Prevent multiple processing if this button is already in-process
		if ($link.hasClass('processing')) {
			return false;
		}

		// Determine if this click is "unrecommend" (the button is already active)
		var unrecommend = $link.hasClass('active');

		// Attempt to retrieve the post ID via our helper
		var id = getPostId($link);
		if (!id) {
			console.error('Could not determine post ID for recommendation');
			return false;
		}

		// Retrieve suffix text from the .irecommendthis-suffix element (if present)
		var suffix = $link.find('.irecommendthis-suffix').text();

		// Add a 'processing' class to prevent duplicate clicks
		$link.addClass('processing');

		/**
		 * Read an optional removal_delay from ajaxSettings (in ms).
		 * If not provided or invalid, default to 250 ms.
		 */
		var removalDelay = parseInt(ajaxSettings.removal_delay, 10);
		if (isNaN(removalDelay)) {
			removalDelay = 250;
		}

		/**
		 * Make the AJAX request to WordPress admin-ajax.php
		 */
		$.ajax({
			url: ajaxSettings.ajaxurl,
			type: 'POST',
			data: {
				action: 'irecommendthis',      // Must match the server-side hook
				recommend_id: id,             // The post ID
				suffix: suffix,               // Any suffix text (e.g., "Likes")
				unrecommend: unrecommend,     // True/false for toggling
				nonce: ajaxSettings.nonce     // Security nonce
			},
			success: function(data) {
				// Check if the server responded with an error
				if (typeof data === 'object' && data.success === false) {
					console.error('Error:', data.data ? data.data.message : 'Unknown error');
					return;
				}

				// Determine which title we should display after the click
				var title_new = options.link_title_new || "Recommend this";
				var title_active = options.link_title_active || "You already recommended this";
				var title = unrecommend ? title_new : title_active;

				// Cache a selector for all related buttons with this post ID
				var $buttons = $('.irecommendthis[data-post-id="' + id + '"], #irecommendthis-' + id + ', .irecommendthis-post-' + id);

				// Update each matching button
				$buttons.each(function() {
					var $button = $(this);

					// Replace the HTML content with the response data
					$button.html(data)
						// Toggle 'active' state
						.toggleClass('active')
						// Update the tooltip/title
						.attr('title', title);

					// Check the numeric count, if present
					var countText = $button.find('.irecommendthis-count').text().trim();
					var count = parseInt(countText, 10);

					if (!isNaN(count)) {
						// Hide count if zero and user wants to hide zero counts
						if (count === 0 && parseInt(options.hide_zero, 10) === 1) {
							$button.find('.irecommendthis-count').hide();
						} else {
							$button.find('.irecommendthis-count').show();
						}
					}
				});
			},
			error: function(xhr, status, error) {
				// Log any AJAX errors for debugging
				console.error('AJAX Error:', status, error);
				if (xhr.responseJSON && xhr.responseJSON.data) {
					console.error('Error details:', xhr.responseJSON.data);
				}
			},
			complete: function() {
				/**
				 * Slight delay to avoid UI flicker on very fast responses.
				 * This is configured by ajaxSettings.removal_delay, defaulting to 250 ms.
				 */
				setTimeout(function() {
					$('.irecommendthis[data-post-id="' + id + '"], #irecommendthis-' + id + ', .irecommendthis-post-' + id)
						.removeClass('processing');
				}, removalDelay);
			}
		});

		// Prevent default link behavior
		return false;
	});
});
