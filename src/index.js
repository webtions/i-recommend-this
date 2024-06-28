import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import Edit from './edit';
import save from './save';
import './editor.scss';
import './style.scss';

registerBlockType('themeist/dot-recommends', {
    title: __('Dot Recommends', 'i-recommend-this'),
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
