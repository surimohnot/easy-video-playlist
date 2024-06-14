!function(){"use strict";function e(t){return e="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e},e(t)}function t(e,t,n){return(t=o(t))in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}function n(e,t){for(var n=0;n<t.length;n++){var r=t[n];r.enumerable=r.enumerable||!1,r.configurable=!0,"value"in r&&(r.writable=!0),Object.defineProperty(e,o(r.key),r)}}function o(t){var n=function(t,n){if("object"!==e(t)||null===t)return t;var o=t[Symbol.toPrimitive];if(void 0!==o){var r=o.call(t,"string");if("object"!==e(r))return r;throw new TypeError("@@toPrimitive must return a primitive value.")}return String(t)}(t);return"symbol"===e(n)?n:String(n)}function r(e,t){return r=Object.setPrototypeOf?Object.setPrototypeOf.bind():function(e,t){return e.__proto__=t,e},r(e,t)}function i(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}function l(e){return l=Object.setPrototypeOf?Object.getPrototypeOf.bind():function(e){return e.__proto__||Object.getPrototypeOf(e)},l(e)}var a=wp.i18n.__,c=wp.element,u=c.Component,s=c.Fragment,p=wp.editor,f=(p.MediaUpload,p.PanelColorSettings,wp.apiFetch),y=wp.components,b=(y.Dashicon,y.SelectControl),d=(y.PanelBody,y.Button),m=y.Disabled,v=y.Placeholder,h=(y.RangeControl,y.TextControl,y.TextareaControl,y.ToggleControl,y.Toolbar),w=wp.serverSideRender,g=wp.blockEditor,P=g.BlockControls,S=g.InspectorControls,E=function(o){!function(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),Object.defineProperty(e,"prototype",{writable:!1}),t&&r(e,t)}(E,o);var c,u,p,y,g=(p=E,y=function(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Boolean.prototype.valueOf.call(Reflect.construct(Boolean,[],(function(){}))),!0}catch(e){return!1}}(),function(){var t,n=l(p);if(y){var o=l(this).constructor;t=Reflect.construct(n,arguments,o)}else t=n.apply(this,arguments);return function(t,n){if(n&&("object"===e(n)||"function"==typeof n))return n;if(void 0!==n)throw new TypeError("Derived constructors may only return object or undefined");return i(t)}(this,t)});function E(){var e;!function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,E);var t=""===(e=g.apply(this,arguments)).props.attributes.playlist;return e.state={editing:t,listIndex:[]},e.fetching=!1,e.onSubmitURL=e.onSubmitURL.bind(i(e)),e}return c=E,(u=[{key:"apiDataFetch",value:function(e,n){var o=this;this.fetching?setTimeout(this.apiDataFetch.bind(this,e,n),200):(this.fetching=!0,f({path:"/evp/v1/"+n}).then((function(n){var r=Object.keys(n);r=r.map((function(e){return{label:n[e],value:e}})),o.setState(t({},e,r)),o.fetching=!1})).catch((function(n){o.setState(t({},e,[])),o.fetching=!1,console.log(n)})))}},{key:"componentDidMount",value:function(){this.apiDataFetch("listIndex","lIndex")}},{key:"componentDidUpdate",value:function(e){}},{key:"onSubmitURL",value:function(e){e.preventDefault(),this.props.attributes.playlist&&this.setState({editing:!1})}},{key:"render",value:function(){var e=this,t=this.props.attributes.playlist,n=this.state,o=n.editing,r=n.listIndex,i=this.props.setAttributes;if(o)return wp.element.createElement(s,null,wp.element.createElement(v,{icon:"playlist-video",label:"Playlist"},wp.element.createElement("form",{onSubmit:this.onSubmitURL},!!(r&&Array.isArray(r)&&r.length)&&wp.element.createElement("div",{style:{width:"100%"}},wp.element.createElement(b,{value:t,onChange:function(e){return i({playlist:e})},options:r,style:{maxWidth:"none"}})),wp.element.createElement(d,{type:"submit",style:{backgroundColor:"#f7f7f7"}},a("Show Playlist","easy-video-playlist")))));var l=[{icon:"edit",title:a("Edit Playlist","easy-video-playlist"),onClick:function(){return e.setState({editing:!0})}}];return wp.element.createElement(s,null,wp.element.createElement(P,null,wp.element.createElement(h,{controls:l})),wp.element.createElement(S,null),wp.element.createElement(m,null,wp.element.createElement(w,{block:"evp-block/evp-block",attributes:this.props.attributes})))}}])&&n(c.prototype,u),Object.defineProperty(c,"prototype",{writable:!1}),E}(u),k=E,O=wp.i18n.__;(0,wp.blocks.registerBlockType)("evp-block/evp-block",{title:O("Easy Video Playlist","easy-video-playlist"),description:O("Player for WP Video","easy-video-playlist"),icon:"playlist-video",category:"widgets",keywords:[O("Video Playlist Player","easy-video-playlist"),O("Vimeo player","easy-video-playlist"),O("Youtube player","easy-video-playlist")],edit:k,save:function(){return null}})}();