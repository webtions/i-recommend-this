import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import Edit from './edit';
import save from './save';

registerBlockType('irecommendthis/recommend', {
    apiVersion: 2,
    title: __('I Recommend This', 'i-recommend-this'),
    icon: 'thumbs-up',
    category: 'widgets',
    attributes: {
        postId: {
            type: 'number',
            default: null,
        },
    },
    edit: Edit,
    save,
});
