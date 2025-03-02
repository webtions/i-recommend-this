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

		// IMPORTANT: Always use data-post-id as the primary identifier
		// This ensures consistency across multiple instances of the same post button
		var id = link.data('post-id');

		// If data-post-id is somehow missing, fall back to ID parsing (backward compatibility)
		if (!id) {
			// Extract just the post ID, ignoring any instance-specific suffix
			var idParts = link.attr('id').split('-');
			if (idParts.length >= 2) {
				id = idParts[1];  // Get the post ID portion
			}
		}

		var suffix = link.find('.irecommendthis-suffix').text(); // Get the suffix text

		// Support both new and legacy variable names for backward compatibility
		var ajaxSettings = irecommendthis || dot_irecommendthis;
		var nonce = ajaxSettings.nonce; // Get the nonce for security

		link.addClass('processing'); // Add processing class to the link

		// Make an AJAX request
		$.ajax({
			url: ajaxSettings.ajaxurl,
			type: 'POST',
			data: {
				action: 'irecommendthis', // The updated action name
				recommend_id: id,
				suffix: suffix,
				unrecommend: unrecommend,
				security: nonce,
			},
			success: function(data) {
				// Parse the options from the plugin settings
				var options = JSON.parse(ajaxSettings.options);
				var title_new = options.link_title_new || "Recommend this";
				var title_active = options.link_title_active || "You already recommended this";
				let title = unrecommend ? title_new : title_active;

				// Target all buttons with the same post-id data attribute
				// This ensures all instances for the same post are updated
				$('.irecommendthis[data-post-id="' + id + '"]').each(function() {
					$(this).html(data).toggleClass('active').attr('title', title);

					// Check if the count is zero and hide/show accordingly
					var count = $(this).find('.irecommendthis-count').text().trim();

					if (parseInt(count) === 0 && parseInt(options.hide_zero) === 1) {
						$(this).find('.irecommendthis-count').hide();
					} else {
						$(this).find('.irecommendthis-count').show();
					}
				});

				// Remove processing class to allow future clicks
				$('.irecommendthis[data-post-id="' + id + '"]').removeClass('processing');
			},
			error: function(xhr, status, error) {
				console.error('AJAX Error: ' + status + ' - ' + error);
				link.removeClass('processing'); // Remove processing class on error
			}
		});

		return false; // Prevent the default link behavior
	});
});
