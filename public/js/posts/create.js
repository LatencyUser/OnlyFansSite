/*
* Post create page
 */
"use strict";
/* global PostCreate, FileUpload, mediaSettings, isAllowedToPost */

$(function () {
    // Initing button save
    $('.post-create-button').on('click',function () {
        PostCreate.save('create');
    });

    $('.draft-clear-button').on('click',function () {
        PostCreate.clearDraft();
    });
    // Populating draft data, if available
    const draftData = PostCreate.populateDraftData();
    PostCreate.initPostDraft(draftData);
    if(isAllowedToPost){
        // Initiating file manager
        FileUpload.initDropZone('.dropzone','/attachment/upload/post', mediaSettings.use_chunked_uploads);
    }

});


// Saving draft data before unload
window.addEventListener('beforeunload', function () {
    if(!PostCreate.isSavingRedirect){
        PostCreate.saveDraftData();
    }
});
