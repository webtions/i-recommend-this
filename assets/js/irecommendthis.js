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

jQuery(
	function ( $ ) {
		'use strict';

		// IRecommendThis main object - Using object literal pattern for organization.
		const IRecommendThis = {
			/**
			 * Initialize the recommendation system.
			 */
			init: function () {
				// Get configuration settings.
				this.settings = this.getSettings();

				// Initialize event listeners.
				this.bindEvents();
			},

			/**
			 * Get settings from the localized variables.
			 *
			 * WordPress passes plugin settings via wp_localize_script() which
			 * creates the 'irecommendthis' global object with all our settings.
			 */
			getSettings: function () {
				// Use only the new variable name.
				let settings = ( typeof irecommendthis !== 'undefined' ) ? irecommendthis : null;

				if ( ! settings ) {
					console.error( 'AJAX settings not found - plugin may not be properly initialized.' );
					return {
						ajaxurl:       ajaxurl || '',
						nonce:         '',
						options:       '{}',
						removal_delay: 250
					};
				}

				// Parse options from JSON string to object.
				try {
					settings.parsedOptions = JSON.parse( settings.options );
				} catch ( e ) {
					console.error( 'Error parsing options JSON:', e );
					settings.parsedOptions = {};
				}

				return settings;
			},

			/**
			 * Bind event listeners for recommendation buttons.
			 *
			 * Uses event delegation to capture clicks on any current or future
			 * recommendation buttons added to the page.
			 */
			bindEvents: function () {
				// Use event delegation for all recommendation buttons.
				$( document ).on( 'click', '.irecommendthis', this.handleRecommendClick.bind( this ) );
			},

			/**
			 * Handle recommendation button clicks.
			 *
			 * This function processes user interactions with the recommendation buttons,
			 * determines the post ID, and manages the button state.
			 *
			 * @param {Object} event The click event object.
			 * @return {boolean} Returns false to prevent default action.
			 */
			handleRecommendClick: function ( event ) {
				event.preventDefault();

				const $link = $( event.currentTarget );

				// Prevent multiple processing if this button is already in process.
				if ( $link.hasClass( 'processing' ) ) {
					return false;
				}

				// Get post ID from the button.
				const postId = this.getPostId( $link );
				if ( ! postId ) {
					console.error( 'Could not determine post ID for recommendation.' );
					return false;
				}

				// Get state information - whether this is a like or unlike action.
				const unrecommend = $link.hasClass( 'active' );
				const suffix      = $link.find( '.irecommendthis-suffix' ).text();

				// Process the recommendation via AJAX.
				this.processRecommendation( postId, unrecommend, suffix, $link );

				return false;
			},

			/**
			 * Extract the post ID from a recommendation link.
			 *
			 * Tries multiple methods to determine the post ID:
			 * 1. From data-post-id attribute (preferred)
			 * 2. From ID attribute
			 * 3. From class names
			 *
			 * @param {Object} $el jQuery element object.
			 * @return {string|null} The post ID or null if not found.
			 */
			getPostId: function ( $el ) {
				// Try data attribute first (best practice).
				let postId = $el.data( 'post-id' );

				// Fall back to ID attribute.
				if ( ! postId && $el.attr( 'id' ) ) {
					postId = $el.attr( 'id' ).replace( 'irecommendthis-', '' );
				}

				// Last resort: check class names.
				if ( ! postId && $el.attr( 'class' ) ) {
					// Find class names that match our pattern.
					$.each(
						$el.attr( 'class' ).split( ' ' ),
						function ( _, className ) {
							if ( className.indexOf( 'irecommendthis-post-' ) === 0 ) {
								postId = className.replace( 'irecommendthis-post-', '' );
								// Break the each loop.
								return false;
							}
						}
					);
				}

				return postId;
			},

			/**
			 * Process a recommendation through AJAX.
			 *
			 * Sends the request to the server to update the recommendation count
			 * and handles the response.
			 *
			 * @param {string} postId      The post ID to recommend/unrecommend.
			 * @param {boolean} unrecommend Whether this is an unrecommend action.
			 * @param {string} suffix      The text suffix for the count.
			 * @param {Object} $link       The jQuery button element that was clicked.
			 */
			processRecommendation: function ( postId, unrecommend, suffix, $link ) {
				// Add a 'processing' class to prevent duplicate clicks.
				$link.addClass( 'processing' );

				// Get all buttons for this post for later updating (there might be multiple on the page).
				const allButtons = this.getAllButtonsForPost( postId );

				// Make a single AJAX request with simpler approach.
				$.ajax(
					{
						url: this.settings.ajaxurl,
						type: 'POST',
						data: {
							action: 'irecommendthis',
							recommend_id: postId,
							suffix: suffix,
							unrecommend: unrecommend,
							nonce: this.settings.nonce
						},
						success: function ( data ) {
							// Handle the response.
							IRecommendThis.handleSuccess( data, postId, unrecommend, allButtons );
						},
						error: function ( xhr, status, error ) {
							IRecommendThis.handleError( xhr, status, error, $link );
						},
						complete: function () {
							// Clean up processing state with a slight delay for visual feedback.
							setTimeout(
								function () {
									allButtons.removeClass( 'processing' );
								},
								IRecommendThis.settings.removal_delay || 250
							);
						}
					}
				);
			},

			/**
			 * Get all recommendation buttons for a specific post.
			 *
			 * Finds all buttons on the page that refer to the same post ID,
			 * so they can all be updated simultaneously.
			 *
			 * @param {string} postId The post ID to find buttons for.
			 * @return {Object} jQuery collection of matching buttons.
			 */
			getAllButtonsForPost: function ( postId ) {
				// Create selector for all possible buttons matching this post ID.
				return $( '.irecommendthis[data-post-id="' + postId + '"], #irecommendthis-' + postId + ', .irecommendthis-post-' + postId );
			},

			/**
			 * Handle successful AJAX response.
			 *
			 * Updates all buttons for the post with new count and state.
			 *
			 * @param {string|Object} data       The response from the server.
			 * @param {string} postId            The post ID that was acted upon.
			 * @param {boolean} unrecommend      Whether this was an unrecommend action.
			 * @param {Object} $buttons          jQuery collection of buttons to update.
			 */
			handleSuccess: function ( data, postId, unrecommend, $buttons ) {
				// Check if the server responded with an error.
				if ( typeof data === 'object' && data.success === false ) {
					console.error( 'Error:', data.data ? data.data.message : 'Unknown error.' );
					return;
				}

				const options = this.settings.parsedOptions;

				// Get text states from data attributes or settings.
				const defaultLikeText   = options.link_title_new || 'Recommend this';
				const defaultUnlikeText = options.link_title_active || 'Unrecommend this';

				// Update each matching button.
				$buttons.each(
					function () {
						const $button = $( this );

						// Get text state from data attributes (if available).
						const likeText   = $button.data( 'like' ) || defaultLikeText;
						const unlikeText = $button.data( 'unlike' ) || defaultUnlikeText;

						// Replace the HTML content with the response data.
						$button.html( data );

						// Toggle the 'active' state.
						$button.toggleClass( 'active' );

						// Update both title and aria-label for accessibility.
						const newState = $button.hasClass( 'active' ) ? unlikeText : likeText;
						$button.attr( 'title', newState );
						$button.attr( 'aria-label', newState );

						// Check the numeric count, if present.
						const $count = $button.find( '.irecommendthis-count' );
						if ( $count.length ) {
							const countText = $count.text().trim();
							const count     = parseInt( countText, 10 );

							if ( ! isNaN( count ) ) {
								// Hide count if zero and user wants to hide zero counts.
								if ( count === 0 && parseInt( options.hide_zero, 10 ) === 1 ) {
									$count.hide();
								} else {
									$count.show();
								}
							}
						}
					}
				);
			},

			/**
			 * Handle AJAX errors.
			 *
			 * Logs error information and removes processing state.
			 *
			 * @param {Object} xhr    The XHR object.
			 * @param {string} status The status text.
			 * @param {string} error  The error message.
			 * @param {Object} $link  The jQuery button element.
			 */
			handleError: function ( xhr, status, error, $link ) {
				console.error( 'AJAX Error:', status, error );
				if ( xhr.responseJSON && xhr.responseJSON.data ) {
					console.error( 'Error details:', xhr.responseJSON.data );
				}
				$link.removeClass( 'processing' );
			}
		};

		// Initialize when DOM is ready.
		$(
			function () {
				IRecommendThis.init();
			}
		);
	}
);
