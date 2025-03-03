jQuery(function($) {
	// Wait for the DOM to be ready
	$(document).on('click', '.irecommendthis', function(event) {
		event.preventDefault(); // Prevent the default link behavior
		var link = $(this);

		// If the link is already processing, do nothing
		if (link.hasClass('processing')) {
			return false;
		}

		var unrecommend = link.hasClass('active');

		// First try to use the data attribute (preferred method)
		var id = link.data('post-id');

		// Fallback: try to extract post ID from class name if data attribute isn't present
		if (!id) {
			var postClass = link.attr('class').split(' ').find(function(c) {
				return c.startsWith('irecommendthis-post-');
			});

			if (postClass) {
				id = postClass.replace('irecommendthis-post-', '');
			}
		}

		// If we still don't have an ID, there's a problem
		if (!id) {
			console.error('Could not determine post ID for recommendation');
			return false;
		}

		var suffix = link.find('.irecommendthis-suffix').text(); // Get the suffix text

		// Support both new and legacy variable names for backward compatibility
		var ajaxSettings = irecommendthis || dot_irecommendthis;

		if (!ajaxSettings) {
			console.error('AJAX settings not found - plugin may not be properly initialized');
			return false;
		}

		// Get the security token - make sure we use the property name 'security'
		var security = ajaxSettings.security;

		link.addClass('processing'); // Add processing class to the link

		// Make an AJAX request
		$.ajax({
			url: ajaxSettings.ajaxurl,
			type: 'POST',
			data: {
				action: 'irecommendthis', // Use the standard action name
				recommend_id: id,
				suffix: suffix,
				unrecommend: unrecommend,
				security: security, // Use the security token
			},
			success: function(data) {
				// Check if we received an error response
				if (data.indexOf('Security verification failed') !== -1) {
					console.warn('Security verification failed. The action was not processed.');
					link.removeClass('processing');
					return;
				}

				// Parse the options from the plugin settings
				var options = JSON.parse(ajaxSettings.options);
				var title_new = options.link_title_new || "Recommend this";
				var title_active = options.link_title_active || "You already recommended this";
				var title = unrecommend ? title_new : title_active;

				// Update all buttons with the same post ID (using data attribute)
				$('.irecommendthis[data-post-id="' + id + '"]').each(function() {
					$(this).html(data)
						.toggleClass('active')
						.attr('title', title);

					// Check if the count is zero and hide/show accordingly
					var count = $(this).find('.irecommendthis-count').text().trim();

					if (parseInt(count) === 0 && parseInt(options.hide_zero) === 1) {
						$(this).find('.irecommendthis-count').hide();
					} else {
						$(this).find('.irecommendthis-count').show();
					}
				});

				// Also update using class selector for any elements that might not have the data attribute
				$('.irecommendthis-post-' + id).each(function() {
					if (!$(this).data('post-id')) {
						$(this).html(data)
							.toggleClass('active')
							.attr('title', title);

						var count = $(this).find('.irecommendthis-count').text().trim();
						if (parseInt(count) === 0 && parseInt(options.hide_zero) === 1) {
							$(this).find('.irecommendthis-count').hide();
						} else {
							$(this).find('.irecommendthis-count').show();
						}
					}
				});

				// Remove processing class from all affected elements
				$('.irecommendthis[data-post-id="' + id + '"], .irecommendthis-post-' + id).removeClass('processing');
			},
			error: function(xhr, status, error) {
				console.error('AJAX Error:', status, error);
				if (xhr.responseJSON && xhr.responseJSON.data) {
					console.error('Error details:', xhr.responseJSON.data);
				}
				link.removeClass('processing'); // Remove processing class on error
			}
		});

		return false; // Prevent the default link behavior
	});
});
