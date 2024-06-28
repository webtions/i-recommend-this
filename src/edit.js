import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import { useEffect } from '@wordpress/element';

export default function Edit({ attributes, setAttributes }) {
    const { postId } = attributes;
    const blockProps = useBlockProps();

    useEffect(() => {
        if (!postId) {
            setAttributes({ postId: wp.data.select('core/editor').getCurrentPostId() });
        }
    }, []);

    return (
        <div {...blockProps}>
            <InspectorControls>
                <PanelBody title={__('Settings', 'i-recommend-this')}>
                    <TextControl
                        label={__('Post ID', 'i-recommend-this')}
                        value={postId}
                        onChange={(value) => setAttributes({ postId: parseInt(value, 10) })}
                    />
                </PanelBody>
            </InspectorControls>
            <div>[dot_recommends id="{postId}"]</div>
        </div>
    );
}
