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
			let title = unrecommend ? "Recommend this" : "You already recommended this";

			// Update all buttons with the same id
			$('.dot-irecommendthis[id="' + id + '"]').html(data).toggleClass('active').attr('title', title);

			// Remove processing class to allow future clicks
			$('.dot-irecommendthis[id="' + id + '"]').removeClass('processing');
		});

		return false;
	});
});
