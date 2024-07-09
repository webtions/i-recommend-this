jQuery(document).ready(function($) {
	$(document).on('click', '.dot-irecommendthis', function(event) {
		event.preventDefault();
		var link = $(this);

		// Check if the link is already processing
		if (link.hasClass('processing')) {
			return false;
		}

		var unrecommend = link.hasClass('active');
		var id = $(this).attr('id');
		var suffix = link.find('.dot-irecommendthis-suffix').text();

		// Generate a nonce
		var nonce = dot_irecommendthis.nonce;

		// Add processing class to disable further clicks
		link.addClass('processing');

		$.post(dot_irecommendthis.ajaxurl, {
			action: 'dot-irecommendthis',
			recommend_id: id,
			suffix: suffix,
			unrecommend: unrecommend,
			security: nonce,
		}, function(data) {
			// Get the correct titles from the plugin settings
			var options = JSON.parse(dot_irecommendthis.options);
			var title_new = options.link_title_new || "Recommend this";
			var title_active = options.link_title_active || "You already recommended this";

			let title = unrecommend ? title_new : title_active;

			// Update all buttons with the same id
			$('.dot-irecommendthis[id="' + id + '"]').each(function() {
				$(this).html(data).toggleClass('active').attr('title', title);

				// Check if the count is zero and hide if necessary
				var count = $(this).find('.dot-irecommendthis-count').text().trim();

				if (parseInt(count) === 0 && parseInt(options.hide_zero) === 1) {
					$(this).find('.dot-irecommendthis-count').hide();
				} else {
					$(this).find('.dot-irecommendthis-count').show();
				}
			});

			// Remove processing class to allow future clicks
			$('.dot-irecommendthis[id="' + id + '"]').removeClass('processing');
		});

		return false;
	});
});
