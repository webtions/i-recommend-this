import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import { useEffect } from '@wordpress/element';

export default function Edit({ attributes, setAttributes }) {
    const { postId } = attributes;
    const blockProps = useBlockProps();

    useEffect(() => {
        if (postId === null || postId === undefined) {
            setAttributes({ postId: wp.data.select('core/editor').getCurrentPostId() });
        }
    }, []);

    const handlePostIdChange = (value) => {
        const newValue = value === '' ? '' : parseInt(value, 10);
        setAttributes({ postId: newValue });
    };

    return (
        <div {...blockProps}>
            <InspectorControls>
                <PanelBody title={__('Settings', 'i-recommend-this')}>
                    <TextControl
                        label={__('Post ID', 'i-recommend-this')}
                        value={postId === null || postId === undefined ? '' : postId}
                        onChange={handlePostIdChange}
                    />
                </PanelBody>
            </InspectorControls>
            <p>[irecommendthis id="{postId}"]</p>
        </div>
    );
}
