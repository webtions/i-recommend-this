/**
 * Registers a new block provided a unique name and an object defining its behavior.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
import { registerBlockType } from '@wordpress/blocks';
import {
	useBlockProps,
	InspectorControls,
	BlockControls,
	AlignmentToolbar,
	useBlockDisplayInformation,
} from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
	ToggleControl,
	Notice,
	Placeholder,
	Spinner,
} from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import metadata from './block.json';

registerBlockType( metadata, {
	/**
	 * The edit function describes the structure of your block in the context of the editor.
	 * This represents what the editor will render when the block is used.
	 *
	 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/
	 *
	 * @param {Object}   props               Block properties.
	 * @param {Object}   props.attributes    Block attributes.
	 * @param {Function} props.setAttributes Function to update block attributes.
	 * @param {Object}   props.context       Block context (for query loops).
	 * @param {string}   props.clientId      The block's client ID.
	 * @return {WPElement} Element to render.
	 */
	edit( { attributes, setAttributes, context, clientId } ) {
		const { postId, alignText, useCurrentPost } = attributes;
		const [ error, setError ] = useState( null );
		const [ loading, setLoading ] = useState( false );

		// Get block props with the alignment class
		const blockProps = useBlockProps( {
			className: `has-text-align-${ alignText }`,
		} );

		// Get current post ID from the editor
		const currentPostId = useSelect( ( select ) => {
			const { getCurrentPostId } = select( 'core/editor' );
			return getCurrentPostId ? getCurrentPostId() : null;
		}, [] );

		// Get context post ID if in a query loop
		const contextPostId = context?.postId || null;

		// Handle post ID determination when the block is inserted or settings change
		useEffect( () => {
			if ( useCurrentPost ) {
				if ( contextPostId ) {
					// We're in a query loop, use the context post ID
					if ( postId !== contextPostId ) {
						setAttributes( { postId: contextPostId } );
					}
				} else if ( currentPostId && postId !== currentPostId ) {
					// Not in a query loop, use the current post being edited
					setAttributes( { postId: currentPostId } );
				}
			}
		}, [ useCurrentPost, contextPostId, currentPostId, postId, setAttributes ] );

		// Function to handle changes in the Post ID input
		const handlePostIdChange = ( value ) => {
			try {
				// Handle empty input
				if ( value === '' ) {
					setAttributes( { postId: null } );
					setError( null );
					return;
				}

				// Parse the input value
				const newValue = parseInt( value, 10 );

				// Validate the parsed value
				if ( isNaN( newValue ) || newValue <= 0 ) {
					setError( 'Please enter a valid post ID (a positive number)' );
					return;
				}

				// Update the attribute and clear any error
				setAttributes( { postId: newValue } );
				setError( null );
			} catch ( e ) {
				setError( 'Invalid post ID format' );
			}
		};

		// Toggle for using current post vs specific post ID
		const toggleUseCurrentPost = () => {
			if ( ! useCurrentPost ) {
				// Switching to use current post - determine the appropriate ID
				const newId = contextPostId || currentPostId || null;
				setAttributes( {
					useCurrentPost: true,
					postId: newId,
				} );
			} else {
				// Switching to use specific post ID - keep the current one by default
				setAttributes( {
					useCurrentPost: false,
				} );
			}

			// Clear any existing error
			setError( null );
		};

		// Determine which post ID is actually being used for preview
		const effectivePostId = useCurrentPost
			? contextPostId || currentPostId
			: postId;

		// Create a readable preview of the current state
		const getPreviewContent = () => {
			if ( ! effectivePostId ) {
				return (
					<Notice status="warning" isDismissible={ false }>
						No valid post ID available. Please select a post or provide an ID.
					</Notice>
				);
			}

			// Show different shortcode preview based on settings
			const shortcodePreview = useCurrentPost
				? '[irecommendthis use_current_post="true"]'
				: `[irecommendthis id="${ effectivePostId }"]`;

			return (
				<div className="recommend-preview">
					<div className="recommend-shortcode">{ shortcodePreview }</div>
					<div className="recommend-info">
						Using post ID: { effectivePostId || 'Unknown' }
					</div>
				</div>
			);
		};

		return (
			<div { ...blockProps }>
				<BlockControls>
					<AlignmentToolbar
						value={ alignText }
						onChange={ ( newAlign ) => setAttributes( { alignText: newAlign } ) }
					/>
				</BlockControls>

				<InspectorControls>
					<PanelBody title="Settings">
						<ToggleControl
							label="Use current post in query loops"
							checked={ useCurrentPost }
							onChange={ toggleUseCurrentPost }
							help={
								useCurrentPost
									? "In query loops, the plugin will use each post's ID automatically."
									: 'Using a specific post ID for all instances.'
							}
						/>

						{ ! useCurrentPost && (
							<>
								<TextControl
									label="Specific Post ID"
									value={ postId === null || postId === undefined ? '' : postId }
									onChange={ handlePostIdChange }
									type="number"
									min="1"
								/>
								{ error && (
									<Notice status="error" isDismissible={ false }>
										{ error }
									</Notice>
								) }
							</>
						) }

						{ contextPostId && (
							<Notice status="info" isDismissible={ false }>
								This block is inside a Query Loop. Post ID: { contextPostId }
							</Notice>
						) }
					</PanelBody>
				</InspectorControls>

				{ loading ? (
					<Placeholder>
						<Spinner />
					</Placeholder>
				) : (
					getPreviewContent()
				) }
			</div>
		);
	},

	/**
	 * The save function defines the way in which the different attributes should be combined
	 * into the final markup, which is then serialized by the block editor into `post_content`.
	 *
	 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/
	 *
	 * @param {Object} props            Block properties.
	 * @param {Object} props.attributes Block attributes.
	 * @return {WPElement} Element to render.
	 */
	save( { attributes } ) {
		const { postId, alignText, useCurrentPost } = attributes;
		const blockProps = useBlockProps.save( {
			className: `has-text-align-${ alignText }`,
		} );

		return (
			<div { ...blockProps }>
				{ useCurrentPost ? (
					'[irecommendthis use_current_post="true"]'
				) : (
					`[irecommendthis id="${ postId }"]`
				) }
			</div>
		);
	},
} );
