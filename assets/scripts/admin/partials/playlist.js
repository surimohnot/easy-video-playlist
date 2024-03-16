import { addVideoEditForm, addVideoForm } from '../../front/partials/templates';
import { sprintf } from '../../lib/functions';
import vars from './variables';

class Playlist {

    /**
     * Dashboard constructor.
     *
     * @since 1.0.0
     */
    constructor() {
        this.playListItems = vars.playList || {};
        this.listManager = jQuery("#evp-playlist-manager");
        this.feedback = jQuery('#evp-action-feedback');
        this.api = vars.api || {};
    }

    init() {
        this.render();
        this.events();
    }

    render(key = false) {
        if (this.listManager.length === 0) {
            return;
        }
        if (Object.keys(this.playListItems).length === 0) {
            this.listManager.find('.evp-playlists-index-list').empty();
            this.renderPlaylist();
            this.listManager.find('.evp-playlists-index').hide();
            this.listManager.find('.evp-create-new legend').text(vars.i18n?.['createfirst'] ?? '');
            return;
        } else {
            this.listManager.find('.evp-create-new legend').text(vars.i18n?.['createnew'] ?? '');
        }
        this.listManager.find('.evp-video-index-list').sortable({
            change: ( event, ui ) => {
                this.listManager.addClass('evp-video-sorted');
            }
        });
        this.renderPlaylistIndex(key);
        this.renderPlaylist(key);
    }

    events() {
        this.listManager.find('.evp-add-playlist-btn').on('click', this.addNewPlaylist.bind(this));
        this.listManager.find('.evp-open-addvideo-modal').on('click', this.openAddVideoModal.bind(this));
        this.listManager.find('.evp-video-modal').on('click', '.evp-cancel-add-video', this.closeVideoModal.bind(this));
        this.listManager.find('.evp-video-modal').on('click', '.evp-add-video-btn', this.addNewVideo.bind(this));
        this.listManager.find('.evp-video-modal').on('click', '.evp-save-edit-info-btn', this.saveEditVideoInfo.bind(this));
        this.listManager.find('.evp-playlists-index-list').on('click', '.evp-play-list-item', this.openPlaylist.bind(this));
        this.listManager.find('.evp-delete-playlist').on('click', this.deletePlaylist.bind(this));
        this.listManager.find('.evp-video-index-list').on('click', '.evp-edit-video-info', this.openEditVideoModal.bind(this));
        this.listManager.find('.evp-video-index-list').on('click', '.evp-delete-video', this.deleteVideo.bind(this));
        this.listManager.find('.evp-save-playlist-sorting').on('click', this.savePlaylistSorting.bind(this));
        this.listManager.find('.evp-playlist-content-tabs-item').on('click', this.toggleTabs.bind(this));
        this.feedback.on('click', '.evp-error-close', (e) => {
			this.feedback.removeClass('evp-error');
		})
    }

    renderPlaylistIndex(plKey) {
        const listContainer = this.listManager.find('.evp-playlists-index-list');
        const playlistItems = `${Object.keys(this.playListItems).map((key) => {
            let clsname = 'evp-play-list-item';
            if (key === plKey) {
                clsname += ' evp-play-list-item-active';
            }
            return `<li class="${clsname}" data-id="${key}"><span class="evp-play-list-item-title-text">${this.playListItems[key].title}</span></li>`;
        }).join("")}`;
        listContainer.html(playlistItems);
        if (! plKey) {
            listContainer.find('.evp-play-list-item').first().addClass('evp-play-list-item-active');
        }
        this.listManager.find('.evp-playlists-index').show();
    }

    renderPlaylist(plKey) {
        const playLists = Object.keys(this.playListItems);
        const firstPlaylistKey = plKey ? plKey : playLists.length ? playLists[0] : '';
        const firstPlaylist = firstPlaylistKey ? this.playListItems[firstPlaylistKey] : '';
        if (firstPlaylist) {
            const container = this.listManager.find('.evp-playlists-content');
            const content = container.find('.evp-video-index-list');
            const videos = firstPlaylist.videos || {};
            const videoMarkup = `${Object.keys(videos).map((key) => {
                const video = videos[key];
                const url = video.url || '';
                const title = video.title || 'Untitled Video';
                if (!url) {
                    return '';
                }
                return `<li class="evp-video-listitem" data-video="${url}" data-key="${key}"><span class="evp-video-listitem-title">${title}</span><span><button class="evp-edit-video-info"><span class="dashicons dashicons-edit"></span></button><button class="evp-delete-video"><span class="dashicons dashicons-no-alt"></span></button></span></li>`;
            }).join("")}`;
            container.find('.evp-playlist-title-text').text(firstPlaylist.title);
            container.attr('data-id', firstPlaylistKey);
            content.html(videoMarkup);
            this.listManager.find('.evp-video-index-list').sortable('refresh');
            container.show();
            this.listManager.find('.evp-playlists-no-content').hide();
            container.find('.evp-playlist-shortcode').html('[evpvideoplaylist playlist="' + firstPlaylistKey + '"]');
        } else {
            const container = this.listManager.find('.evp-playlists-content');
            container.find('.evp-playlist-title-text').empty();
            container.find('.evp-video-index-list').empty();
            container.find('.evp-playlist-shortcode').empty();
            container.hide();
            this.listManager.find('.evp-playlists-no-content').show();
        }
    }

    addNewPlaylist() {
        const name = this.listManager.find('.evp-playlist-name').val();
        if (!name) {
            this.response('Please enter a playlist name.', 'evp-error');
            return;
        }

        const key = name.toLowerCase().replace(/\s/g, '-');
        if (this.playListItems[key]) {
            this.response('Playlist already exists.', 'evp-error');
            return;
        }
        const data = {
            action: 'evp_add_new_playlist',
            security: vars.security,
            playlist: name,
        };
        jQuery.post(vars.ajaxUrl, data, (response) => {
            if (response.success) {
                const key = response.data;
                if (key) {
                    this.playListItems[key] = {
                        title : name,
                        videos: {},
                    };
                }
                this.render(key);
                this.listManager.find('.evp-playlist-name').val('');
                this.response('Playlist added successfully.', 'evp-success');
            }
        }, 'json');
    }

    openAddVideoModal() {
        const modal = jQuery('.evp-video-modal');
        const i18n  = vars.i18n;
        const markup = jQuery(sprintf(addVideoForm(), i18n.vidurl, i18n.addvid, i18n.cancel));
        modal.html(markup);
        modal.addClass('evp-modal-open');
        this.listManager.find('.evp-playlists-content').hide();
        modal.show();
    }

    openEditVideoModal(e) {
        const video = jQuery(e.currentTarget).closest('.evp-video-listitem');
        const key = video.attr('data-key');
        const playlist = video.closest('.evp-playlists-content').attr('data-id');
        const plData = this.playListItems[playlist] || {};
        const data = plData?.videos?.[key] ?? {};
        this.createEditVideoModal(data);
    }

    createEditVideoModal(videoData) {
        const i18n  = vars.i18n;
        const modal = jQuery('.evp-video-modal');
        const thumbUrl = videoData.thumbnail_url.length ? videoData.thumbnail_url[0] : '';
        const markup = jQuery(
            sprintf(
                addVideoEditForm(),
                i18n.videourl,
                videoData.url,
                i18n.title,
                videoData.title,
                i18n.author,
                videoData.author_name,
                i18n.authorurl,
                videoData.author_url,
                i18n.thumbnail,
                thumbUrl,
                i18n.update,
                i18n.cancel,
                i18n.editvidinfo
            )
        );
        modal.html(markup);
        modal.addClass('evp-modal-open');
        this.listManager.find('.evp-playlists-content').hide();
        modal.show();
    }

    closeVideoModal() {
        const modal = jQuery('.evp-video-modal');
        modal.empty();
        modal.removeClass('evp-modal-open');
        modal.hide();
        this.listManager.find('.evp-playlists-content').show();
    }

    addNewVideo() {
        const container = this.listManager.find('.evp-playlists-content');
        const playList = container.attr('data-id');
        const url = this.listManager.find('.evp-video-url').val();
        if (!url) {
            this.response('Please enter a video URL.', 'evp-error');
            return;
        }

        const urlType = this.analyseUrl( url );
        if (!urlType) {
            this.response('Please enter a valid video URL.', 'evp-error');
            return;
        }

        const { provider, type, id } = urlType;
        const videos = this.playListItems[playList]?.['videos'] ?? [];
        if (type !== 'video') {
            if ( 'youtube' === provider && ! this.api['youtube'] ) {
                this.response('Please add your YouTube API key.', 'evp-error');
                if ( vars.setpage ) {
                    setTimeout(function() {
                        window.location.href = vars.setpage;
                    }.bind(this), 1000);
                }
                return; // TODO: Show an error message and reload to api setting page.
            } else if ( 'vimeo' === provider && ! this.api['vimeo'] ) {
                this.response('Please add your Vimeo API key.', 'evp-error');
                return; // TODO: Show an error message and reload to api setting page.
            }
        } else {
            for (let i = 0; i < videos.length; i++) {
                if (videos[i][url] === url) {
                    this.response('Video already exists.', 'evp-error');
                    return;
                }
            }
        }

        const data = {
            action: 'evp_add_new_video',
            security: vars.security,
            playlist: playList,
            videourl: url,
            videotype: type,
            videoid: id,
            videoprovider: provider,
        };
        jQuery.post(vars.ajaxUrl, data, (response) => {
            if (response.success) {
                const data = response.data;
                if (data) {
                    this.playListItems = data;
                    this.render(playList);
                    this.closeVideoModal();
                    const vids = this.playListItems[playList]?.['videos'] ?? [];
                    const lastVid = vids.length ? vids[vids.length - 1] : {};
                    if (vids.length > videos.length && lastVid.provider && 'url' === lastVid.provider) {
                        this.createEditVideoModal(lastVid);
                    }
                    this.response('The Video has been added successfully.', 'evp-success');
                }
            } else {
                this.closeVideoModal();
                this.response('This Video could not be added.', 'evp-error');
            }
            this.listManager.find('.evp-video-url').val('');
        }, 'json');
    }

    analyseUrl( url ) {
        // Patterns for YouTube URLs
        const youtubePatterns = [
            { pattern: /youtube\.com\/(?:watch\?v=|embed\/|v\/)([\w-]+)(?:$|&(?!list=))/i, type: 'video' }, // Single video URL without list attribute
            { pattern: /youtu\.be\/([\w-]+)/i, type: 'video' },
            { pattern: /youtube\.com\/playlist\?list=([\w-]+)/i, type: 'playlist' }, // Playlist URL format 1
            { pattern: /youtube\.com\/watch\?v=[\w-]+&list=([\w-]+)/i, type: 'playlist' }, // Playlist URL format 2
            { pattern: /youtube\.com\/(?:channel|c)\/([\w-]+)/i, type: 'channel' }, // Channel URL
            { pattern: /youtube\.com\/user\/([\w-]+)/i, type: 'user' }, // User page URL
            { pattern: /youtube\.com\/@([\w-]+)/i, type: 'channelUser' } // Channel page URL with '@'
        ];

        // Patterns for Vimeo URLs
        const vimeoPatterns = [
            { pattern: /vimeo\.com\/(\d+)/i, type: 'video' }, // Vimeo video URL
            { pattern: /vimeo\.com\/channels\/([\w-]+)/i, type: 'channel' }, // Vimeo channel URL
            { pattern: /vimeo\.com\/album\/(\d+)/i, type: 'album' }, // Vimeo album URL
            { pattern: /vimeo\.com\/showcase\/(\d+)/i, type: 'showcase' }, // Vimeo showcase URL
            { pattern: /vimeo\.com\/user\/([\w-]+)/i, type: 'user' }, // Vimeo user URL
            { pattern: /vimeo\.com\/groups\/([\w-]+)/i, type: 'group' } // Vimeo group URL
        ];

        // Check YouTube URL patterns
        for (const { pattern, type } of youtubePatterns) {
            const matches = url.match(pattern);
            if (matches) {
                const id = matches[1] || '';
                return { provider: 'youtube', type, id };
            }
        }

        // Check YouTube URL patterns
        for (const { pattern, type } of vimeoPatterns) {
            const matches = url.match(pattern);
            if (matches) {
                const id = matches[1] || '';
                return { provider: 'vimeo', type, id };
            }
        }

        // Check if it's a video file URL
        const videoExtensions = ['mp4', 'm4v', 'webm', 'ogv', 'flv'];
        const urlParts = url.split('?')[0].split('.'); // Split URL by '?' and '.', then get the last part
        const fileExtension = urlParts[urlParts.length - 1].toLowerCase();
        if (videoExtensions.includes(fileExtension)) {
            return { provider: 'url', type: 'video', id: '' };
        }

        return false;
    }

    openPlaylist(e) {
        const item = jQuery(e.currentTarget);
        const key = item.attr('data-id');
        this.listManager.find('.evp-play-list-item-active').removeClass('evp-play-list-item-active');
        item.addClass('evp-play-list-item-active');
        this.closeVideoModal();
        this.resetVideoTabs();
        this.renderPlaylist(key);
    }

    deletePlaylist(e) {
        const key = jQuery(e.currentTarget).closest('.evp-playlists-content').attr('data-id');
        const data = {
            action: 'evp_delete_playlist',
            security: vars.security,
            playlist: key,
        };
        jQuery.post(vars.ajaxUrl, data, (response) => {
            if (response.success) {
                const data = response.data;
                if (false !== data) {
                    this.playListItems = data;
                    this.closeVideoModal();
                    this.render();
                }
                this.response('Playlist Deleted Successfully.', 'evp-success');
            }
        }, 'json');
    }

    deleteVideo(e) {
        const container = this.listManager.find('.evp-playlists-content');
        const item = jQuery(e.currentTarget).closest('.evp-video-listitem');
        const url = item.attr('data-video');
        const playlist = container.attr('data-id');
        if (! url || ! playlist) return;
        const data = {
            action: 'evp_delete_video',
            security: vars.security,
            playlist: playlist,
            video: url,
        };
        jQuery.post(vars.ajaxUrl, data, (response) => {
            if (response.success) {
                item.remove();
                this.response('Video Deleted Successfully.', 'evp-success');
            }
        }, 'json');
    }

    saveEditVideoInfo() {
        const container = this.listManager.find('.evp-playlists-content');
        const modal = jQuery('.evp-video-modal');
        const url = modal.find('.evp-edit-video-url a').attr('href');
        const playlist = container.attr('data-id');
        if (! url || ! playlist) return;
        const data = {
            action: 'evp_edit_video_info',
            security: vars.security,
            playlist: playlist,
            video: url,
            title: modal.find('#evp-edit-video-title').val(),
            thumb: modal.find('#evp-edit-video-thumb').val(),
            author: modal.find('#evp-edit-video-author').val(),
            author_url: modal.find('#evp-edit-video-author-url').val(),
        };
        jQuery.post(vars.ajaxUrl, data, (response) => {
            if (response.success) {
                const data = response.data;
                if (data) {
                    this.playListItems = data;
                    this.closeVideoModal()
                    this.render();
                }
                this.response('Video data edited successfully.', 'evp-success');
            }
        }, 'json');
    }

    savePlaylistSorting() {
        const playlist = this.listManager.find('.evp-playlists-content').attr('data-id');
        const vids = this.listManager.find('.evp-video-listitem');
        const urls = vids.map((index, item) => {
            return jQuery(item).attr('data-video');
        }).get();
        if (0 === urls.length || ! playlist) return;
        const data = {
            action: 'evp_save_playlist_sorting',
            security: vars.security,
            playlist: playlist,
            videos: urls,
        };
        jQuery.post(vars.ajaxUrl, data, (response) => {
            if (response.success) {
                this.listManager.removeClass('evp-video-sorted');
                this.response('Playlist Sorted Successfully.', 'evp-success');
            }
        }, 'json');
    }

    toggleTabs(e) {
        e.preventDefault();
        const currentTab = jQuery(e.currentTarget);
        const container = currentTab.closest('.evp-playlist-content-tabs');
        const selector = currentTab.attr('data-attr');
        if (currentTab.hasClass('evp-tab-active')) return;
        currentTab.siblings('.evp-tab-active').removeClass('evp-tab-active');
        currentTab.addClass('evp-tab-active');
        container.siblings('.evp-playlist-tab-content').hide();
        container.siblings('.' + selector).show();
    }

    resetVideoTabs() {
        this.listManager.find('.evp-playlist-content-tabs-item').removeClass('evp-tab-active').first().addClass('evp-tab-active');
        this.listManager.find('.evp-playlist-tab-content').hide().first().show();
    }

	response(message = '', type = false) {
		this.feedback.removeClass('evp-error evp-success evp-running');
		if (false !== type) {
			this.feedback.addClass(type);
			this.feedback.find('.evp-feedback').text(message);
		}

		// Remove classes after 1.5 seconds
		setTimeout(function() {
			this.feedback.removeClass('evp-success evp-running');
		}.bind(this), 1500);
	}
}
export default Playlist;
