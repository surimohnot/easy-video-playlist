import edit from './edit';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;

registerBlockType( 'evp-block/evp-block', {
	title: __( 'Easy Video Playlist', 'easy-video-playlist' ),
	description: __( 'Player for WP Video', 'easy-video-playlist' ),
	icon: 'playlist-video',
	category: 'widgets',
	keywords: [
		__( 'Video Playlist Player', 'easy-video-playlist' ),
		__( 'Vimeo player', 'easy-video-playlist' ),
		__( 'Youtube player', 'easy-video-playlist' ),
	],
    edit,
    save() {
        return null;
    },
} );
