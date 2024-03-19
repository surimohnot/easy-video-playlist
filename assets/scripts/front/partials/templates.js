function addVideoForm() {
    return `<div class="evp-add-video-form">
        <input type="text" class="evp-video-url" placeholder="{1}">
        <button class="evp-add-video-btn">
            <span class="dashicons dashicons-plus-alt"></span>
            <span>{2}</span>
        </button>
        <button class="evp-cancel-add-video">
            <span class="dashicons dashicons-no"></span>
            <span>{3}</span>
        </button>
    </div>`;
}

function addVideoEditForm() {
    return `<div class="evp-edit-video-form">
        <h3>{13}</h3>
        <div class="evp-edit-video-url evp-edit-video-form-elem"><div><label>{1}</label></div><a href="{2}" data-video="{14}" target="_blank">{2}</a></div>
        <div class="evp-edit-video-title evp-edit-video-form-elem">
            <label for="evp-edit-video-title">{3}</label>
            <input type="text" id="evp-edit-video-title" value="{4}">
        </div>
        <div class="evp-edit-video-thumb evp-edit-video-form-elem">
            <label for="evp-edit-video-thumb">{9}</label>
            <input type="text" id="evp-edit-video-thumb" value="{10}">
        </div>
        <div class="evp-edit-video-author evp-edit-video-form-elem">
            <label for="evp-edit-video-author">{5}</label>
            <input type="text" id="evp-edit-video-author" value="{6}">
        </div>
        <div class="evp-edit-video-author-url evp-edit-video-form-elem">
            <label for="evp-edit-video-author-url">{7}</label>
            <input type="text" id="evp-edit-video-author-url" value="{8}">
        </div>
        <button class="evp-save-edit-info-btn">
            <span class="dashicons dashicons-update"></span>
            <span>{11}</span>
        </button>
        <button class="evp-cancel-add-video">
            <span class="dashicons dashicons-no"></span>
            <span>{12}</span>
        </button>
    </div>`;
}

export {addVideoForm, addVideoEditForm};