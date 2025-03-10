/**
 * I Recommend This Plugin Script
 *
 * This script handles the AJAX-based "recommend" functionality in WordPress.
 * It listens for clicks on elements with the .irecommendthis class and sends
 * an AJAX request to update the recommendation count.
 *
 * @package IRecommendThis
 * @version 4.0.0
 */

jQuery(function($) {
	'use strict';

	// IRecommendThis main object
	const IRecommendThis = {
		/**
		 * Initialize the recommendation system
		 */
		init: function() {
			// Get configuration settings
			this.settings = this.getSettings();

			// Initialize event listeners
			this.bindEvents();
		},

		/**
		 * Get settings from the localized variables
		 */
		getSettings: function() {
			// Use only the new variable name
			let settings = (typeof irecommendthis !== 'undefined') ? irecommendthis : null;

			if (!settings) {
				console.error('AJAX settings not found - plugin may not be properly initialized');
				return {
					ajaxurl: ajaxurl || '',
					nonce: '',
					options: '{}',
					removal_delay: 250
				};
			}

			// Parse options
			try {
				settings.parsedOptions = JSON.parse(settings.options);
			} catch (e) {
				console.error('Error parsing options JSON:', e);
				settings.parsedOptions = {};
			}

			return settings;
		},

		/**
		 * Bind event listeners
		 */
		bindEvents: function() {
			// Use event delegation for all recommendation buttons
			$(document).on('click', '.irecommendthis', this.handleRecommendClick.bind(this));
		},

		/**
		 * Handle recommendation button clicks
		 */
		handleRecommendClick: function(event) {
			event.preventDefault();

			const $link = $(event.currentTarget);

			// Prevent multiple processing if this button is already in process
			if ($link.hasClass('processing')) {
				return false;
			}

			// Get post ID
			const postId = this.getPostId($link);
			if (!postId) {
				console.error('Could not determine post ID for recommendation');
				return false;
			}

			// Get state information
			const unrecommend = $link.hasClass('active');
			const suffix = $link.find('.irecommendthis-suffix').text();

			// Process the recommendation
			this.processRecommendation(postId, unrecommend, suffix, $link);

			return false;
		},

		/**
		 * Extract the post ID from a recommendation link
		 */
		getPostId: function($el) {
			// Try data attribute first
			let postId = $el.data('post-id');

			// Fall back to ID attribute
			if (!postId && $el.attr('id')) {
				postId = $el.attr('id').replace('irecommendthis-', '');
			}

			// Last resort: check class names
			if (!postId && $el.attr('class')) {
				$.each($el.attr('class').split(' '), function(_, className) {
					if (className.indexOf('irecommendthis-post-') === 0) {
						postId = className.replace('irecommendthis-post-', '');
						return false;
					}
				});
			}

			return postId;
		},

		/**
		 * Process a recommendation through AJAX
		 */
		processRecommendation: function(postId, unrecommend, suffix, $link) {
			// Add a 'processing' class to prevent duplicate clicks
			$link.addClass('processing');

			// Get all buttons for this post for later updating
			const allButtons = this.getAllButtonsForPost(postId);

			// Make the AJAX request
			$.ajax({
				url: this.settings.ajaxurl,
				type: 'POST',
				data: {
					action: 'irecommendthis',
					recommend_id: postId,
					suffix: suffix,
					unrecommend: unrecommend,
					nonce: this.settings.nonce
				},
				success: (data) => this.handleSuccess(data, postId, unrecommend, allButtons),
				error: (xhr, status, error) => this.handleError(xhr, status, error, $link),
				complete: () => this.handleComplete(postId, allButtons)
			});
		},

		/**
		 * Get all recommendation buttons for a specific post
		 */
		getAllButtonsForPost: function(postId) {
			return $(`.irecommendthis[data-post-id="${postId}"], #irecommendthis-${postId}, .irecommendthis-post-${postId}`);
		},

		/**
		 * Handle successful AJAX response
		 */
		handleSuccess: function(data, postId, unrecommend, $buttons) {
			// Check if the server responded with an error
			if (typeof data === 'object' && data.success === false) {
				console.error('Error:', data.data ? data.data.message : 'Unknown error');
				return;
			}

			const options = this.settings.parsedOptions;

			// Get text states from data attributes or settings
			const defaultLikeText = options.link_title_new || 'Recommend this';
			const defaultUnlikeText = options.link_title_active || 'Unrecommend this';

			// Update each matching button
			$buttons.each(function() {
				const $button = $(this);

				// Get text state from data attributes (if available)
				const likeText = $button.data('like') || defaultLikeText;
				const unlikeText = $button.data('unlike') || defaultUnlikeText;

				// Replace the HTML content with the response data
				$button.html(data);

				// Toggle the 'active' state
				$button.toggleClass('active');

				// Update both title and aria-label for accessibility
				const newState = $button.hasClass('active') ? unlikeText : likeText;
				$button.attr('title', newState);
				$button.attr('aria-label', newState);

				// Check the numeric count, if present
				const $count = $button.find('.irecommendthis-count');
				if ($count.length) {
					const countText = $count.text().trim();
					const count = parseInt(countText, 10);

					if (!isNaN(count)) {
						// Hide count if zero and user wants to hide zero counts
						if (count === 0 && parseInt(options.hide_zero, 10) === 1) {
							$count.hide();
						} else {
							$count.show();
						}
					}
				}
			});
		},

		/**
		 * Handle AJAX errors
		 */
		handleError: function(xhr, status, error, $link) {
			console.error('AJAX Error:', status, error);
			if (xhr.responseJSON && xhr.responseJSON.data) {
				console.error('Error details:', xhr.responseJSON.data);
			}
			$link.removeClass('processing');
		},

		/**
		 * Handle AJAX completion
		 */
		handleComplete: function(postId, $buttons) {
			const removalDelay = parseInt(this.settings.removal_delay, 10) || 250;

			// Slight delay to avoid UI flicker on very fast responses
			setTimeout(function() {
				$buttons.removeClass('processing');
			}, removalDelay);
		}
	};

	// Initialize when DOM is ready
	$(document).ready(function() {
		IRecommendThis.init();
	});
});
