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
		var id = link.attr('id').split('-')[1]; // Get the post ID from the element's ID
		var suffix = link.find('.irecommendthis-suffix').text(); // Get the suffix text

		var nonce = dot_irecommendthis.nonce; // Get the nonce for security

		link.addClass('processing'); // Add processing class to the link

		// Make an AJAX request
		$.ajax({
			url: dot_irecommendthis.ajaxurl,
			type: 'POST',
			data: {
				action: 'dot-irecommendthis', // The action to be performed
				recommend_id: id,
				suffix: suffix,
				unrecommend: unrecommend,
				security: nonce,
			},
			success: function(data) {
				// Parse the options from the plugin settings
				var options = JSON.parse(dot_irecommendthis.options);
				var title_new = options.link_title_new || "Recommend this";
				var title_active = options.link_title_active || "You already recommended this";

				let title = unrecommend ? title_new : title_active;

				// Update all elements with the same id
				$('.irecommendthis[id="irecommendthis-' + id + '"]').each(function() {
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
				$('.irecommendthis[id="irecommendthis-' + id + '"]').removeClass('processing');
			},
			error: function(xhr, status, error) {
				console.error('AJAX Error: ' + status + ' - ' + error);
				link.removeClass('processing'); // Remove processing class on error
			}
		});

		return false; // Prevent the default link behavior
	});
});
