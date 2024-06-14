!function(){"use strict";var e=window.EVP_Admin_Data||{},t={ajaxUrl:e.ajaxurl,playList:e.videoPlaylist||[],security:e.security,i18n:e.i18n,api:e.api||{},setpage:e.setpage};function i(){var e=arguments,t=e[0];return(t=t.replace(/>\s+</g,"><")).replace(/{(\d+)}/g,(function(t,i){return void 0!==e[i]?e[i]:t}))}function a(e){return a="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e},a(e)}function s(e,t){for(var i=0;i<t.length;i++){var s=t[i];s.enumerable=s.enumerable||!1,s.configurable=!0,"value"in s&&(s.writable=!0),Object.defineProperty(e,(void 0,n=function(e,t){if("object"!==a(e)||null===e)return e;var i=e[Symbol.toPrimitive];if(void 0!==i){var s=i.call(e,"string");if("object"!==a(s))return s;throw new TypeError("@@toPrimitive must return a primitive value.")}return String(e)}(s.key),"symbol"===a(n)?n:String(n)),s)}var n}var n=function(){function e(){!function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,e),this.playListItems=t.playList||{},this.listManager=jQuery("#evp-playlist-manager"),this.feedback=jQuery("#evp-action-feedback"),this.api=t.api||{}}var a,n;return a=e,n=[{key:"init",value:function(){this.render(),this.events()}},{key:"render",value:function(){var e=this,i=arguments.length>0&&void 0!==arguments[0]&&arguments[0];if(0!==this.listManager.length){var a,s,n,l;if(0===Object.keys(this.playListItems).length)return this.listManager.find(".evp-playlists-index-list").empty(),this.renderPlaylist(),this.listManager.find(".evp-playlists-index").hide(),void this.listManager.find(".evp-create-new legend").text(null!==(a=null===(s=t.i18n)||void 0===s?void 0:s.createfirst)&&void 0!==a?a:"");this.listManager.find(".evp-create-new legend").text(null!==(n=null===(l=t.i18n)||void 0===l?void 0:l.createnew)&&void 0!==n?n:""),this.listManager.find(".evp-video-index-list").sortable({change:function(t,i){e.listManager.addClass("evp-video-sorted")}}),this.renderPlaylistIndex(i),this.renderPlaylist(i)}}},{key:"events",value:function(){var e=this;this.listManager.find(".evp-add-playlist-btn").on("click",this.addNewPlaylist.bind(this)),this.listManager.find(".evp-open-addvideo-modal").on("click",this.openAddVideoModal.bind(this)),this.listManager.find(".evp-video-modal").on("click",".evp-cancel-add-video",this.closeVideoModal.bind(this)),this.listManager.find(".evp-video-modal").on("click",".evp-add-video-btn",this.addNewVideo.bind(this)),this.listManager.find(".evp-video-modal").on("click",".evp-save-edit-info-btn",this.saveEditVideoInfo.bind(this)),this.listManager.find(".evp-playlists-index-list").on("click",".evp-play-list-item",this.openPlaylist.bind(this)),this.listManager.find(".evp-delete-playlist").on("click",this.deletePlaylist.bind(this)),this.listManager.find(".evp-video-index-list").on("click",".evp-edit-video-info",this.openEditVideoModal.bind(this)),this.listManager.find(".evp-video-index-list").on("click",".evp-delete-video",this.deleteVideo.bind(this)),this.listManager.find(".evp-save-playlist-sorting").on("click",this.savePlaylistSorting.bind(this)),this.listManager.find(".evp-playlist-content-tabs-item").on("click",this.toggleTabs.bind(this)),this.feedback.on("click",".evp-error-close",(function(t){e.feedback.removeClass("evp-error")}))}},{key:"renderPlaylistIndex",value:function(e){var t=this,i=this.listManager.find(".evp-playlists-index-list"),a="".concat(Object.keys(this.playListItems).map((function(i){var a="evp-play-list-item";return i===e&&(a+=" evp-play-list-item-active"),'<li class="'.concat(a,'" data-id="').concat(i,'"><span class="evp-play-list-item-title-text">').concat(t.playListItems[i].title,"</span></li>")})).join(""));i.html(a),e||i.find(".evp-play-list-item").first().addClass("evp-play-list-item-active"),this.listManager.find(".evp-playlists-index").show()}},{key:"renderPlaylist",value:function(e){var t=Object.keys(this.playListItems),i=e||(t.length?t[0]:""),a=i?this.playListItems[i]:"";if(a){var s=this.listManager.find(".evp-playlists-content"),n=s.find(".evp-video-index-list"),l=a.videos||{},o="".concat(Object.keys(l).map((function(e){var t=l[e],i=t.url||"",a=t.title||"Untitled Video";return i?'<li class="evp-video-listitem" data-video="'.concat(i,'" data-key="').concat(e,'"><span class="evp-video-listitem-title">').concat(a,'</span><span><button class="evp-edit-video-info"><span class="dashicons dashicons-edit"></span></button><button class="evp-delete-video"><span class="dashicons dashicons-no-alt"></span></button></span></li>'):""})).join(""));s.find(".evp-playlist-title-text").text(a.title),s.attr("data-id",i),n.html(o),this.listManager.find(".evp-video-index-list").sortable("refresh"),s.show(),this.listManager.find(".evp-playlists-no-content").hide(),s.find(".evp-playlist-shortcode").html('[evpvideoplaylist playlist="'+i+'"]')}else{var r=this.listManager.find(".evp-playlists-content");r.find(".evp-playlist-title-text").empty(),r.find(".evp-video-index-list").empty(),r.find(".evp-playlist-shortcode").empty(),r.hide(),this.listManager.find(".evp-playlists-no-content").show()}}},{key:"addNewPlaylist",value:function(){var e=this,i=this.listManager.find(".evp-playlist-name").val();if(i){var a=i.toLowerCase().replace(/\s/g,"-");if(this.playListItems[a])this.response("Playlist already exists.","evp-error");else{var s={action:"evp_add_new_playlist",security:t.security,playlist:i};jQuery.post(t.ajaxUrl,s,(function(t){if(t.success){var a=t.data;a&&(e.playListItems[a]={title:i,videos:{}}),e.render(a),e.listManager.find(".evp-playlist-name").val(""),e.response("Playlist added successfully.","evp-success")}}),"json")}}else this.response("Please enter a playlist name.","evp-error")}},{key:"openAddVideoModal",value:function(){var e=jQuery(".evp-video-modal"),a=t.i18n,s=jQuery(i('<div class="evp-add-video-form">\n        <input type="text" class="evp-video-url" placeholder="{1}">\n        <button class="evp-add-video-btn">\n            <span class="dashicons dashicons-plus-alt"></span>\n            <span>{2}</span>\n        </button>\n        <button class="evp-cancel-add-video">\n            <span class="dashicons dashicons-no"></span>\n            <span>{3}</span>\n        </button>\n    </div>',a.vidurl,a.addvid,a.cancel));e.html(s),e.addClass("evp-modal-open"),this.listManager.find(".evp-playlists-content").hide(),e.show()}},{key:"openEditVideoModal",value:function(e){var t,i,a=jQuery(e.currentTarget).closest(".evp-video-listitem"),s=a.attr("data-key"),n=a.closest(".evp-playlists-content").attr("data-id"),l=this.playListItems[n]||{},o=null!==(t=null==l||null===(i=l.videos)||void 0===i?void 0:i[s])&&void 0!==t?t:{};this.createEditVideoModal(o)}},{key:"createEditVideoModal",value:function(e){var a=t.i18n,s=jQuery(".evp-video-modal"),n=e.thumbnail_url.length?e.thumbnail_url[0]:"",l=jQuery(i('<div class="evp-edit-video-form">\n        <h3>{13}</h3>\n        <div class="evp-edit-video-url evp-edit-video-form-elem"><div><label>{1}</label></div><a href="{2}" data-video="{14}" target="_blank">{2}</a></div>\n        <div class="evp-edit-video-title evp-edit-video-form-elem">\n            <label for="evp-edit-video-title">{3}</label>\n            <input type="text" id="evp-edit-video-title" value="{4}">\n        </div>\n        <div class="evp-edit-video-thumb evp-edit-video-form-elem">\n            <label for="evp-edit-video-thumb">{9}</label>\n            <input type="text" id="evp-edit-video-thumb" value="{10}">\n        </div>\n        <div class="evp-edit-video-author evp-edit-video-form-elem">\n            <label for="evp-edit-video-author">{5}</label>\n            <input type="text" id="evp-edit-video-author" value="{6}">\n        </div>\n        <div class="evp-edit-video-author-url evp-edit-video-form-elem">\n            <label for="evp-edit-video-author-url">{7}</label>\n            <input type="text" id="evp-edit-video-author-url" value="{8}">\n        </div>\n        <button class="evp-save-edit-info-btn">\n            <span class="dashicons dashicons-update"></span>\n            <span>{11}</span>\n        </button>\n        <button class="evp-cancel-add-video">\n            <span class="dashicons dashicons-no"></span>\n            <span>{12}</span>\n        </button>\n    </div>',a.videourl,e.url,a.title,e.title,a.author,e.author_name,a.authorurl,e.author_url,a.thumbnail,n,a.update,a.cancel,a.editvidinfo,e.id));s.html(l),s.addClass("evp-modal-open"),this.listManager.find(".evp-playlists-content").hide(),s.show()}},{key:"closeVideoModal",value:function(){var e=jQuery(".evp-video-modal");e.empty(),e.removeClass("evp-modal-open"),e.hide(),this.listManager.find(".evp-playlists-content").show()}},{key:"addNewVideo",value:function(){var e,i,a=this,s=this.listManager.find(".evp-playlists-content").attr("data-id"),n=this.listManager.find(".evp-video-url").val();if(n){var l=this.analyseUrl(n);if(l){var o=l.provider,r=l.type,d=l.id,v=null!==(e=null===(i=this.playListItems[s])||void 0===i?void 0:i.videos)&&void 0!==e?e:[];if("video"!==r){if("youtube"===o&&!this.api.youtube)return this.response("Please add your YouTube API key.","evp-error"),void(t.setpage&&setTimeout(function(){window.location.href=t.setpage}.bind(this),1e3));if("vimeo"===o&&!this.api.vimeo)return void this.response("Please add your Vimeo API key.","evp-error")}else for(var p=0;p<v.length;p++)if(v[p][n]===n)return void this.response("Video already exists.","evp-error");var c={action:"evp_add_new_video",security:t.security,playlist:s,url:n,sourcetype:r,sourceid:d,provider:o};jQuery.post(t.ajaxUrl,c,(function(e){if(e.success){var t=e.data;if(t){var i,n;a.playListItems=t,a.render(s),a.closeVideoModal();var l=null!==(i=null===(n=a.playListItems[s])||void 0===n?void 0:n.videos)&&void 0!==i?i:[],o=l.length?l[l.length-1]:{};l.length>v.length&&o.provider&&"url"===o.provider&&a.createEditVideoModal(o),a.response("The Video has been added successfully.","evp-success")}}else a.closeVideoModal(),e.message?a.response(e.message,"evp-error"):a.response("This Video could not be added.","evp-error");a.listManager.find(".evp-video-url").val("")}),"json")}else this.response("Please enter a valid video URL.","evp-error")}else this.response("Please enter a video URL.","evp-error")}},{key:"analyseUrl",value:function(e){for(var t=0,i=[{pattern:/youtube\.com\/(?:watch\?v=|embed\/|v\/)([\w-]+)(?:$|&(?!list=))/i,type:"video"},{pattern:/youtu\.be\/([\w-]+)/i,type:"video"},{pattern:/youtube\.com\/playlist\?list=([\w-]+)/i,type:"playlist"},{pattern:/youtube\.com\/watch\?v=[\w-]+&list=([\w-]+)/i,type:"playlist"},{pattern:/youtube\.com\/(?:channel|c)\/([\w-]+)/i,type:"channel"},{pattern:/youtube\.com\/user\/([\w-]+)/i,type:"user"},{pattern:/youtube\.com\/@([\w-]+)/i,type:"channelUser"}];t<i.length;t++){var a=i[t],s=a.pattern,n=a.type,l=e.match(s);if(l)return{provider:"youtube",type:n,id:l[1]||""}}for(var o=0,r=[{pattern:/vimeo\.com\/(\d+)/i,type:"video"},{pattern:/vimeo\.com\/channels\/([\w-]+)/i,type:"channel"},{pattern:/vimeo\.com\/album\/(\d+)/i,type:"album"},{pattern:/vimeo\.com\/showcase\/(\d+)/i,type:"showcase"},{pattern:/vimeo\.com\/user\/([\w-]+)/i,type:"user"},{pattern:/vimeo\.com\/groups\/([\w-]+)/i,type:"group"}];o<r.length;o++){var d=r[o],v=d.pattern,p=d.type,c=e.match(v);if(c)return{provider:"vimeo",type:p,id:c[1]||""}}var u=e.split("?")[0].split("."),y=u[u.length-1].toLowerCase();return!!["mp4","m4v","webm","ogv","flv"].includes(y)&&{provider:"url",type:"video",id:""}}},{key:"openPlaylist",value:function(e){var t=jQuery(e.currentTarget),i=t.attr("data-id");this.listManager.find(".evp-play-list-item-active").removeClass("evp-play-list-item-active"),t.addClass("evp-play-list-item-active"),this.closeVideoModal(),this.resetVideoTabs(),this.renderPlaylist(i)}},{key:"deletePlaylist",value:function(e){var i=this,a=jQuery(e.currentTarget).closest(".evp-playlists-content").attr("data-id"),s={action:"evp_delete_playlist",security:t.security,playlist:a};jQuery.post(t.ajaxUrl,s,(function(e){if(e.success){var t=e.data;!1!==t&&(i.playListItems=t,i.closeVideoModal(),i.render()),i.response("Playlist Deleted Successfully.","evp-success")}}),"json")}},{key:"deleteVideo",value:function(e){var i=this,a=this.listManager.find(".evp-playlists-content"),s=jQuery(e.currentTarget).closest(".evp-video-listitem"),n=s.attr("data-video"),l=a.attr("data-id"),o=s.attr("data-key");if(n&&l){var r={action:"evp_delete_video",security:t.security,playlist:l,video:n,video_id:o};jQuery.post(t.ajaxUrl,r,(function(e){e.success&&(s.remove(),i.response("Video Deleted Successfully.","evp-success"))}),"json")}}},{key:"saveEditVideoInfo",value:function(){var e=this,i=this.listManager.find(".evp-playlists-content"),a=jQuery(".evp-video-modal"),s=a.find(".evp-edit-video-url a").attr("href"),n=a.find(".evp-edit-video-url a").attr("data-video"),l=i.attr("data-id");if(s&&l){var o={action:"evp_edit_video_info",security:t.security,playlist:l,video:s,video_id:n,title:a.find("#evp-edit-video-title").val(),thumb:a.find("#evp-edit-video-thumb").val(),author:a.find("#evp-edit-video-author").val(),author_url:a.find("#evp-edit-video-author-url").val()};jQuery.post(t.ajaxUrl,o,(function(t){if(t.success){var i=t.data;i&&(e.playListItems=i,e.closeVideoModal(),e.render()),e.response("Video data edited successfully.","evp-success")}}),"json")}}},{key:"savePlaylistSorting",value:function(){var e=this,i=this.listManager.find(".evp-playlists-content").attr("data-id"),a=this.listManager.find(".evp-video-listitem"),s=a.map((function(e,t){return jQuery(t).attr("data-video")})).get(),n=a.map((function(e,t){return jQuery(t).attr("data-key")})).get();if(0!==s.length&&i){var l={action:"evp_save_playlist_sorting",security:t.security,playlist:i,videos:s,ids:n};jQuery.post(t.ajaxUrl,l,(function(t){t.success&&(e.listManager.removeClass("evp-video-sorted"),e.response("Playlist Sorted Successfully.","evp-success"))}),"json")}}},{key:"toggleTabs",value:function(e){e.preventDefault();var t=jQuery(e.currentTarget),i=t.closest(".evp-playlist-content-tabs"),a=t.attr("data-attr");t.hasClass("evp-tab-active")||(t.siblings(".evp-tab-active").removeClass("evp-tab-active"),t.addClass("evp-tab-active"),i.siblings(".evp-playlist-tab-content").hide(),i.siblings("."+a).show())}},{key:"resetVideoTabs",value:function(){this.listManager.find(".evp-playlist-content-tabs-item").removeClass("evp-tab-active").first().addClass("evp-tab-active"),this.listManager.find(".evp-playlist-tab-content").hide().first().show()}},{key:"response",value:function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:"",t=arguments.length>1&&void 0!==arguments[1]&&arguments[1];this.feedback.removeClass("evp-error evp-success evp-running"),!1!==t&&(this.feedback.addClass(t),this.feedback.find(".evp-feedback").text(e)),setTimeout(function(){this.feedback.removeClass("evp-success evp-running")}.bind(this),1500)}}],n&&s(a.prototype,n),Object.defineProperty(a,"prototype",{writable:!1}),e}(),l=n;function o(e){return o="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e},o(e)}function r(e,t){for(var i=0;i<t.length;i++){var a=t[i];a.enumerable=a.enumerable||!1,a.configurable=!0,"value"in a&&(a.writable=!0),Object.defineProperty(e,(void 0,s=function(e,t){if("object"!==o(e)||null===e)return e;var i=e[Symbol.toPrimitive];if(void 0!==i){var a=i.call(e,"string");if("object"!==o(a))return a;throw new TypeError("@@toPrimitive must return a primitive value.")}return String(e)}(a.key),"symbol"===o(s)?s:String(s)),a)}var s}var d=function(){function e(){!function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,e),this.playListItems=t.playList||{},this.settingsManager=jQuery("#evp-settings")}var i,a;return i=e,(a=[{key:"init",value:function(){this.settingsManager.length&&this.events()}},{key:"events",value:function(){var e=this;this.settingsManager.find(".evp-settings-toggle-visibility").on("click",e.toggleVisibility.bind(e)),this.settingsManager.find(".evp-settings-api-submit").on("click",e.submitApiKey.bind(e))}},{key:"toggleVisibility",value:function(e){var t=jQuery(e.target).closest(".evp-settings-toggle-visibility"),i=t.closest(".evp-settings-api-input-wrapper").find("input");t.toggleClass("toggled-on"),i.attr("type","text"===i.attr("type")?"password":"text")}},{key:"submitApiKey",value:function(e){var i=jQuery(e.target).siblings(".evp-settings-api-input-wrapper").find("input"),a=i.val(),s=i.data("attr"),n={action:"evp_save_api_key",security:t.security,api_key:s,api_val:a};console.log(n),jQuery.post(t.ajaxUrl,n,(function(e){e.success&&location.reload()}),"json")}}])&&r(i.prototype,a),Object.defineProperty(i,"prototype",{writable:!1}),e}();jQuery((function(e){(new l).init(),(new d).init()}))}();