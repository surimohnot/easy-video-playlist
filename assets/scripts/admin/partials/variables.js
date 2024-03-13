const adminData = window.EVP_Admin_Data || {};
const vars = {
    ajaxUrl  : adminData.ajaxurl,
    playList : adminData.videoPlaylist || [],
    security : adminData.security,
    i18n     : adminData.i18n,
    api      : adminData.api || {},
    setpage  : adminData.setpage,
}
export default vars;