import vars from './partials/variables';
import Playlist from './partials/playlist';
jQuery(function($) {
    "use strict";

    // Initialize the playlist manager.
    const playlist = new Playlist();
    playlist.init();
});