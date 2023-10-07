const frontData = window.EVP_Front_Data || {};
const vars = {
    pldata: frontData.data || {},
    homeUrl: frontData.url || '',
}
export default vars;