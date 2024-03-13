import vars from './partials/variables';
import Playlist from './partials/playlist';
import Settings from './partials/settings';
jQuery(function($) {
    "use strict";

    // Initialize the playlist manager.
    const playlist = new Playlist();
    playlist.init();

    // Initialize player settings.
    const settings = new Settings();
    settings.init();
});