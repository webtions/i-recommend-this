/**
 * Registers a new block provided a unique name and an object defining its behavior.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls, BlockControls, AlignmentToolbar } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl } from '@wordpress/components';
import { useEffect } from '@wordpress/element';
import metadata from './block.json';

registerBlockType(metadata, {
	/**
	 * The edit function describes the structure of your block in the context of the editor.
	 * This represents what the editor will render when the block is used.
	 *
	 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/
	 *
	 * @param {Object} props            Block properties.
	 * @param {Object} props.attributes Block attributes.
	 * @param {Function} props.setAttributes Function to update block attributes.
	 * @return {WPElement} Element to render.
	 */
	edit({ attributes, setAttributes }) {
		const { postId, alignText, useCurrentPost } = attributes;
		const blockProps = useBlockProps({
			className: `has-text-align-${alignText}`
		});

		// Set the default post ID to current post ID if not already set and useCurrentPost is true
		useEffect(() => {
			if (useCurrentPost && (postId === null || postId === undefined)) {
				setAttributes({ postId: wp.data.select('core/editor').getCurrentPostId() });
			}
		}, []);

		// Function to handle changes in the Post ID input
		const handlePostIdChange = (value) => {
			const newValue = value === '' ? null : parseInt(value, 10);
			setAttributes({ postId: newValue });
		};

		// Toggle for using current post vs specific post ID
		const toggleUseCurrentPost = () => {
			setAttributes({
				useCurrentPost: !useCurrentPost,
				postId: !useCurrentPost ? null : postId
			});
		};

		return (
			<div {...blockProps}>
				<BlockControls>
					<AlignmentToolbar
						value={alignText}
						onChange={(newAlign) => setAttributes({ alignText: newAlign })}
					/>
				</BlockControls>
				<InspectorControls>
					<PanelBody title="Settings">
						<ToggleControl
							label="Use current post in query loops"
							checked={useCurrentPost}
							onChange={toggleUseCurrentPost}
							help={useCurrentPost ?
								"In query loops, the plugin will use each post's ID automatically." :
								"Using a specific post ID for all instances."}
						/>

						{!useCurrentPost && (
							<TextControl
								label="Specific Post ID"
								value={postId === null || postId === undefined ? '' : postId}
								onChange={handlePostIdChange}
							/>
						)}
					</PanelBody>
				</InspectorControls>
				<div className="recommend-preview">
					{useCurrentPost ? (
						"[irecommendthis use_current_post=\"true\"]"
					) : (
						`[irecommendthis id="${postId}"]`
					)}
				</div>
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
	save({ attributes }) {
		const { postId, alignText, useCurrentPost } = attributes;
		const blockProps = useBlockProps.save({
			className: `has-text-align-${alignText}`
		});

		return (
			<div {...blockProps}>
				{useCurrentPost ? (
					"[irecommendthis use_current_post=\"true\"]"
				) : (
					`[irecommendthis id="${postId}"]`
				)}
			</div>
		);
	}
});
