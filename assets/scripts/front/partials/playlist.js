import vars from './variables';
import Plyr from './plyr/plyr';

class Playlist {

    /**
     * Dashboard constructor.
     *
     * @since 1.0.0
     */
    constructor(playlist) {
        this.player = false;
        this.playlistContainer = jQuery(playlist);
        this.firstPlayed = false;
        this.playlist = this.playlistContainer.attr('data-playlist');
        if (! this.playlist) {
            return;
        }
        this.playlistData = vars.pldata[this.playlist] || {};
        this.playlistContainer.addClass('evp-playlist-added');
        this.itemsToShow = 5;
        this.visibleItems = this.itemsToShow;
        this.currentlyPlaying = this.playlistContainer.find('.evp-playlist-video-index-item').eq(0);
        this.searchBox = this.playlistContainer.find('.evp-list__search input');
        this.term = '';
        this.localTimeOut = null;
        if (Object.keys(this.playlistData).length) {
            this.render();
            this.events();
        }
    }

    render() {
        const videos = this.playlistData.videos || [];
        if (! videos.length) {
            return;
        }
        const first = videos[0];
        const videoMarkup = `${videos.map(video => {
            if (! video.url) {
                return;
            }
            let author = '';
            let image = '';
            if (Array.isArray(video.thumbnail_url) && video.thumbnail_url.length && video.thumbnail_url[0]) {
                image = `<img src="${video.thumbnail_url[0]}" alt="${video.title}" />`;
            }
            if (video.author_name) {
                if (video.author_url) {
                    author = `<a href="${video.author_url}" target="_blank">${video.author_name}</a>`;
                } else {
                    author = video.author_name;
                }
            }
            author = author ? `<div class="evp-playlist-video__meta">${author}</div>` : '';
            return `
            <div class="evp-playlist-video-index-item" data-video-id="${video.id}" data-search-term="${video.title.toLowerCase()}">
                <div class="evp-playlist-video__image">
                    <div class="evp-playlist-thumbnail-image">${image}</div>
                </div>
                <div class="evp-playlist-video__content">
                    <div class="evp-playlist-video__title">${video.title}</div>
                    ${author}
                </div>
            </div>
            `;
        }).join('')}`;
        const videoFrame = this.getVideoFrame(first);
        const singleVideoFrame = `
        <div class="evp-single-video-frame">
            <div class="evp-single-video__content">
                ${videoFrame}
            </div>
            <div class="evp-single-video__header">
                <div class="evp-single-video__title">${first.title}</div>
                <div class="evp-single-video__meta"><a href="${first.author_url}" target="_blank">${first.author_name}</a></div>
            </div>
        </div>
        `;
        this.setPlayerStyle();
        this.playlistContainer.find('.evp-single-video').html(singleVideoFrame);
        this.playlistContainer.find('.evp-playlist-video-index').html(videoMarkup);
        const iFrame = this.playlistContainer.find('.evp-plyr__video-embed');
        this.player = new Plyr(iFrame);
        this.playlistContainer.find('.evp-playlist__loading').hide();
        this.playlistContainer.find('.evp-playlist__wrapper').fadeIn();
        this.playerStyleUpdate();
        this.player.on('ready', (event) => {
            if (! this.firstPlayed) {
                this.firstPlayed = true;
                this.playerStyleUpdate();
            }
        });
        this.player.on('ended', (event) => {
            this.playNextVideo();
        });
    }

    events() {
        this.playlistContainer.find('.evp-playlist-video-index').on('click', '.evp-playlist-video__image, .evp-playlist-video__title', this.playSelectedVideo.bind(this));
        this.playlistContainer.find('.evp-playlist-load-more').on('click', this.loadMoreVideos.bind(this));
        jQuery(window).on('resize', () => {
			this.resizeTimeout = setTimeout(this.playerStyleUpdate.bind(this), 100);
		});
        this.searchBox.on('keyup paste', this.initSearch.bind(this));
        this.playlistContainer.find('.evp-search-close').on('click', this.clearSearch.bind(this));
    }

    getVideoFrame(video) {
        const provider = video.provider;
        const thumbnail_url = video.thumbnail_url?.[0] ?? '';
        const poster = thumbnail_url ? `data-poster="${thumbnail_url}"` : '';
        if ('youtube' === provider) {
            return `
            <div class="evp-plyr__video-embed plyr__video-embed">
                <iframe
                    src="https://www.youtube.com/embed/${video.id}?origin=${vars.homeUrl}&amp;iv_load_policy=3&amp;modestbranding=1&amp;playsinline=1&amp;showinfo=0&amp;rel=0&amp;enablejsapi=1&amp;vq=hd720"
                    allowfullscreen
                    allowtransparency
                    allow="autoplay"
                ></iframe>
            </div>
            `;
        }

        if ('vimeo' === provider) {
            return `
            <div class="evp-plyr__video-embed plyr__video-embed">
                <iframe
                    src="https://player.vimeo.com/video/${video.id}?loop=false&amp;byline=false&amp;portrait=false&amp;title=false&amp;speed=true&amp;transparent=0&amp;gesture=media"
                    allowfullscreen
                    allowtransparency
                    allow="autoplay"
                ></iframe>
            </div>
            `;
        }

        if ('url' === provider) {
            return `
            <video class="evp-plyr__video-embed plyr__video-embed" playsinline controls" ${poster}>
                <source src="${video.url}" />
            </video>
            `;
        }
    }

    playVideo(selectedVideo) {
        const videoID = selectedVideo.attr('data-video-id');
        const videos = this.playlistData.videos || [];
        if (! videos.length) {
            return;
        }

        const video = videos.find(video => video.id === videoID);
        if ( ! video ) {
            return;
        }

        this.currentlyPlaying = selectedVideo;
        this.currentlyPlaying.parents().find('.evp-currently-playing').removeClass('evp-currently-playing');
        this.currentlyPlaying.addClass('evp-currently-playing');

        const header = `
        <div class="evp-single-video__header">
            <div class="evp-single-video__title">${video.title}</div>
            <div class="evp-single-video__meta"><a href="${video.author_url}" target="_blank">${video.author_name}</a></div>
        </div>`;
        this.playlistContainer.find('.evp-single-video__header').replaceWith(header);
        if ('url' === video.provider) {
            this.player.source = {
                type: 'video',
                sources: [
                    {
                      src: video.url,
                    },
                ],
            }
        } else {
            this.player.source = {
                type: 'video',
                sources: [
                    {
                        src: video.id,
                        provider: video.provider,
                    }
                ]
            }
        }
        const playerWrapper = selectedVideo.closest('.evp-video-player');
        if ( this.playlistContainer.hasClass('evp-wide') ) {
            const videoWrap = selectedVideo.closest('.evp-playlist-video-index');
            const scrollTopPos = selectedVideo.position().top - videoWrap.position().top + videoWrap.scrollTop();
            videoWrap.animate({ scrollTop: scrollTopPos }, 400);
        }
        this.player.on('ready', (event) => {
            this.player.play();
        });
    }

    playSelectedVideo(e) {
        e.preventDefault();
        const selectedVideo = jQuery(e.currentTarget).closest('.evp-playlist-video-index-item');
        this.playVideo(selectedVideo);
    }

    playNextVideo() {
        let nextVideo = false;
        if ( ! this.currentlyPlaying ) {
            nextVideo = this.playlistContainer.find('.evp-playlist-video-index-item').eq(0);
        } else {
            nextVideo = this.currentlyPlaying.next('.evp-playlist-video-index-item');
            if ( ! nextVideo.length ) {
                nextVideo = this.playlistContainer.find('.evp-playlist-video-index-item').eq(0);
            }
        }

        if (nextVideo && nextVideo.length) {
            this.playVideo(nextVideo);
        }
    }

    playerStyleUpdate() {
        // if (! this.player) return;
        const smallLayout  = 720;
        const mediumLayout = 960;
        const largeLayout  = 1440;
        let widthClass = '';
		const width = this.playlistContainer.width();

        this.playlistContainer.removeClass( 'evp-narrow evp-medium evp-wide evp-large' );

        if (width <= smallLayout) {
            widthClass = 'evp-narrow';
        } else if (width <= mediumLayout) {
            widthClass = 'evp-medium';
        } else if (width <= largeLayout) {
            widthClass = 'evp-wide';
        } else if (width > largeLayout) {
            widthClass = 'evp-wide evp-large';
        }

		this.playlistContainer.addClass(widthClass);

        if (width > mediumLayout) {
            const videoContent = this.playlistContainer.find('.evp-single-video-frame');
            const listHeader   = this.playlistContainer.find('.evp-playlist-video-header');
            const height = videoContent.length ? videoContent[0].clientHeight - 20 : 0;
            const headerHeight = listHeader.length ? listHeader[0].clientHeight : 0;
            const finalHeight = height - headerHeight;
            this.playlistContainer.find('.evp-playlist-video-index').css('maxHeight', finalHeight).removeClass('evp-narrow-list');
            this.playlistContainer.find('.evp-playlist-video-more-wrapper').hide();
        } else {
            const listItems = this.playlistContainer.find('.evp-playlist-video-index-item');
            this.playlistContainer.find('.evp-item-visible').removeClass('evp-item-visible');
            this.playlistContainer.find('.evp-playlist-video-index').css('maxHeight', 'none').addClass('evp-narrow-list');
            this.visibleItems = this.itemsToShow;
            if (listItems.length > 5) {
                this.playlistContainer.find('.evp-playlist-video-more-wrapper').show();
            }
        }
    }

    loadMoreVideos() {
        const items = this.playlistContainer.find('.evp-playlist-video-index-item');
        items.slice(this.visibleItems, this.visibleItems + this.itemsToShow).addClass('evp-item-visible');
        this.visibleItems += this.itemsToShow;
        if (this.visibleItems >= items.length) {
            this.playlistContainer.find('.evp-playlist-video-more-wrapper').hide();
        }
    }

    setPlayerStyle() {
        this.playlistContainer.css('--plyr-color-main', '#4CAF50');
        this.playlistContainer.css('--plyr-color-bg', 'rgba( 76, 175, 80, 0.2 )' );
    }

    initSearch() {
        let searchTerm = this.searchBox.val();
		if ( this.term === searchTerm ) {
			return;
		}

		this.term = searchTerm;

        // Filter already loaded episodes.
		clearTimeout(this.localTimeOut);
		this.localTimeOut = setTimeout(function() {
			searchTerm = this.searchBox.val().trim().toLowerCase();
			if (searchTerm && searchTerm.length > 0) {
				this.filterItems();
                this.playlistContainer.addClass('evp-search-opened');
			} else {
                this.clearSearch();
            }
		}.bind(this), 100);
    }

    clearSearch() {
        this.searchBox.val('');
        this.playlistContainer.find('.evp-playlist-video-index-item').show();
        this.playlistContainer.removeClass('evp-search-opened');
    }

    filterItems() {
		const searchTerm = this.searchBox.val().trim().toLowerCase();
		const isSearchTerm = (Boolean)(searchTerm && searchTerm.length > 0);
		const videoList = this.playlistContainer.find('.evp-playlist-video-index-item');
		videoList.each(function() {
			const $this = jQuery( this );
			if ( isSearchTerm ) {
                if ( $this.data( 'search-term' ).includes( searchTerm ) ) {
                    $this.show();
                } else {
                    $this.hide();
                }
            } else {
                $this.show();
            }
		});
	}
}
export default Playlist;
