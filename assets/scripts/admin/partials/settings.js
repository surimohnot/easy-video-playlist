import vars from './variables';

class Settings {

    /**
     * Dashboard constructor.
     *
     * @since 1.0.0
     */
    constructor() {
        this.playListItems = vars.playList || {};
        this.settingsManager = jQuery("#evp-settings");
    }

    init() {
        if ( ! this.settingsManager.length ) {
            return;
        }
        this.events();
    }

    events() {
        const _this = this;
        this.settingsManager.find('.evp-settings-toggle-visibility').on('click', _this.toggleVisibility.bind(_this));
        this.settingsManager.find('.evp-settings-api-submit').on('click', _this.submitApiKey.bind(_this));
    }

    toggleVisibility(e) {
        const button = jQuery(e.target).closest('.evp-settings-toggle-visibility');
        const input = button.closest('.evp-settings-api-input-wrapper').find('input');
        button.toggleClass('toggled-on');
        input.attr('type', input.attr('type') === 'text' ? 'password' : 'text');
    }

    submitApiKey(e) {
        const button  = jQuery(e.target);
        const input   = button.siblings('.evp-settings-api-input-wrapper').find('input');
        const value   = input.val();
        const datakey = input.data('attr');
        const data = {
            action: 'evp_save_api_key',
            security: vars.security,
            api_key: datakey,
            api_val: value
        };
        console.log(data);
        jQuery.post(vars.ajaxUrl, data, (response) => {
            if (response.success) {
                location.reload();
            }
        }, 'json');
    }
}
export default Settings;
