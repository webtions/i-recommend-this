import { useBlockProps } from '@wordpress/block-editor';

export default function save({ attributes }) {
    const { postId } = attributes;
    const blockProps = useBlockProps.save();

    return (
        <div {...blockProps}>
            <div>[dot_recommends id="{postId}"]</div>
        </div>
    );
}
