import Playlist from './partials/playlist';
jQuery(function($) {
    "use strict";

    const playlists = jQuery('.evp-video-player');
    jQuery.each(playlists, (index, playlist) => {
        new Playlist(playlist);
    });

    document.addEventListener('animationstart', evpPlaylistAdded, false); // Standard + firefox
	document.addEventListener('MSAnimationStart', evpPlaylistAdded, false); // IE
	document.addEventListener('webkitAnimationStart', evpPlaylistAdded, false); // Chrome + Safari

	function evpPlaylistAdded(e) {
		if ('evpPlaylistAdded' !== e.animationName) {
			return;
		}
		const playlist = $(e.target);

		if (!playlist.hasClass('evp-video-player')) {
			return;
		}

		if (playlist.hasClass('evp-playlist-added')) {
			return;
		}

		new Playlist(playlist);
	}
});