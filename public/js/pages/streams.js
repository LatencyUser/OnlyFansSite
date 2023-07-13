/*
* Feed page & component
 */
"use strict";
/* global app, launchToast, redirect, trans, copyToClipboard, openCreateDialog, openDetailsDialog, openEditDialog, hasActiveStream, inProgressStreamCover, mediaSettings */

$(function () {
    // If on user (my) streams page
    if((typeof openCreateDialog !== 'undefined' && openCreateDialog !== false) || (typeof openEditDialog !== 'undefined' && openEditDialog !== false)){
        Streams.showStreamEditDialog();
    }
    if(typeof openDetailsDialog !== 'undefined' && openDetailsDialog !== false){
        $('.show-stream-details-label').click();
    }
    if(typeof hasActiveStream !== 'undefined'){
        Streams.hasActiveStream = hasActiveStream;
    }
    Streams.initUploader();
    if(inProgressStreamCover.length){
        Streams.state.streamPosterToEdit = inProgressStreamCover;
    }

    Streams.initCoverChangeEvents();

});

var Streams = {

    state : {
        hasActiveStream: null,
        streamIdToDelete: null,
        streamIdToEdit: null,
        streamPosterToEdit: null,
        isStreamSaving:false,
    },

    dropzone: null,

    /**
     * Instantiates the media uploader for avatar / cover
     */
    initUploader:function () {
        let selector = '';
        selector = '.profile-cover-bg';
        Streams.dropzone = new window.Dropzone(selector, {
            url: app.baseUrl + '/my/streams/poster-upload',
            previewTemplate: document.querySelector('.dz-preview').innerHTML.replace('d-none', ''),
            paramName: "file", // The name that will be used to transfer the file
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            clickable:[`${selector} .upload-button`],
            maxFilesize: mediaSettings.max_file_upload_size, // MB
            addRemoveLinks: true,
            dictRemoveFile: "x",
            acceptedFiles: mediaSettings.allowed_file_extensions,
            autoDiscover: false,
            sending: function(file) {
                file.previewElement.innerHTML = "";
            },
            success: function(file, response) {
                $(selector).css('background-image', 'url(' + response.assetSrc + ')');
                file.previewElement.innerHTML = "";
                Streams.state.streamPosterToEdit = response.assetPath;
            },
            error: function(file, errorMessage) {
                if(typeof errorMessage === 'string'){
                    launchToast('danger','Error ',errorMessage,'now');
                }
                else{
                    launchToast('danger','Error ',errorMessage.errors.file,'now');
                }
                file.previewElement.innerHTML = "";
            }
        });
    },

    /**
     * Instantiates the events needed for changing
     */
    initCoverChangeEvents: function(){
        $('.profile-cover-bg').on('tap', function(e) {
            e.preventDefault();
            $('.profile-cover-bg .actions-holder').toggleClass('d-none');
        });

        $('.profile-cover-bg').on({
            mouseenter: function() {
                $('.profile-cover-bg .actions-holder').removeClass('d-none');
            },
            mouseleave: function() {
                $('.profile-cover-bg .actions-holder').addClass('d-none');
            }
        });
    },

    /**
     * Shows up stream management dialog
     * @param mode
     */
    showStreamEditDialog: function (mode = 'create', id = null) {
        Streams.streamIdToEdit = id;
        let dialogModal = $('#stream-update-dialog');
        if(mode === 'create'){
            dialogModal.find('.create-label').removeClass('d-none');
            dialogModal.find('.edit-label').addClass('d-none');
        }
        else{
            dialogModal.find('.create-label').addClass('d-none');
            dialogModal.find('.edit-label').removeClass('d-none');
        }
        dialogModal.modal('show');
    },

    /**
     * Shows up stream stop dialog
     * @param mode
     */
    showStreamStopDialog: function () {
        $('#stream-stop-dialog').modal('show');
    },

    /**
     * Shows up the stream details dialog
     * @param id
     * @param server
     * @param key
     */
    showStreamDetailsDialog: function(id, server, key){
        $('#stream-details-dialog #stream-url').val('rtmp://'+server);
        $('#stream-details-dialog #stream-key').val(key);
        $('#stream-details-dialog').modal('show');
    },

    /**
     * Shows up stream delete dialog
     * @param mode
     */
    showStreamDeleteDialog: function (streamId) {
        Streams.streamIdToDelete = streamId;
        $('#stream-delete-dialog').modal('show');
    },

    /**
     * Method used for updating a stream
     * @param type
     */
    updateStream: function () {
        if(Streams.state.isStreamSaving) return false;
        $('.stream-save-btn').addClass('disabled');
        Streams.state.isStreamSaving = true;
        const type = Streams.hasActiveStream ? 'edit' : 'create';
        let endpoint = 'init';
        let data = {
            'name': $('#stream-name').val(),
            'price': ($('#stream-access_price').val() ? $('#stream-access_price').val() : 0),
            'requires_subscription':  $('#requires_subscription').is(':checked'),
            'is_public':  $('#is_public').is(':checked'),
            'type': type,
            'poster' : Streams.state.streamPosterToEdit
        };
        if(type === 'edit'){
            endpoint = 'edit';
            data.id = Streams.streamIdToEdit;
        }
        $.ajax({
            type: 'POST',
            data: data,
            url: app.baseUrl + '/my/streams/'+endpoint,
            success: function (result) {
                if(result.success){
                    if(type === 'create'){
                        Streams.streamIdToEdit = result.data.id;
                        Streams.hasActiveStream = true;
                        //TODO: Move this into it's onw f
                        // Appending the new stream to the list
                        $('.active-stream-container').html(result.html);
                        // Populating & showing up stream details dialog
                        $('#stream-update-dialog').modal('hide');
                        $('#stream-details-dialog #stream-url').val('rtmp://'+result.data.rtmp_server);
                        $('#stream-details-dialog #stream-key').val(result.data.rtmp_key);
                        $("#requires_subscription").prop("checked", false);
                        if(result.data.requires_subscription){
                            $("#requires_subscription").prop("checked", true);
                        }
                        $("#is_public").prop("checked", false);
                        if(result.data.requires_subscription){
                            $("#is_public").prop("checked", true);
                        }
                        $('#stream-details-dialog').modal('show');
                        // Updating go live/on air button states
                        $('.stream-on-label').removeClass('d-none');
                        $('.stream-off-label').addClass('d-none');
                        $('.nav-item-live a').attr('href', $('.nav-item-live a').attr('href').replace('?action=create',''));
                        launchToast('success',trans('Success'),trans('Stream started')+'.');
                    }
                    else{
                        $('.active-stream-poster').attr('src',result.data.poster);
                        $('.active-stream-name').html($('#stream-name').val());
                        launchToast('success',trans('Success'),result.message);
                        $('#stream-update-dialog').modal('hide');
                    }
                }
                else{
                    launchToast('danger',trans('Error'),result.message);
                }
                $('.stream-save-btn').removeClass('disabled');
                Streams.state.isStreamSaving = false;
            },
            error: function (result) {
                $.each(result.responseJSON.errors,function (field, value) {
                    if(field === 'name'){
                        $('#stream-name').addClass('is-invalid');
                        $('#stream-name').parent().find('.invalid-feedback strong').html(value[0]);
                        $('#stream-name').focus();
                    }
                });
                $('.stream-save-btn').removeClass('disabled');
                Streams.state.isStreamSaving = false;
            }
        });
    },

    /**
     * Method used for ending a stream
     */
    endStream: function(){
        $.ajax({
            type: 'POST',
            data: {},
            dataType: 'json',
            url: app.baseUrl+'/my/streams/stop',
            success: function (result) {
                if(result.success){
                    redirect(app.baseUrl+'/my/streams');
                }
                else{
                    launchToast('danger',trans('Error'),result.message);
                    $('#stream-stop-dialog').modal('hide');
                }
            },
            error: function (result) {
                launchToast('danger',trans('Error'),result.responseJSON.message);
            }
        });
    },

    /**
     * Method used for removing a stream
     */
    deleteStream: function(){
        $.ajax({
            type: 'DELETE',
            data: {id:Streams.streamIdToDelete},
            dataType: 'json',
            url: app.baseUrl+'/my/streams/delete',
            success: function (result) {
                if(result.success){
                    redirect(app.baseUrl+'/my/streams');
                }
                else{
                    launchToast('danger',trans('Error'),result.message);
                    $('#stream-delete-dialog').modal('hide');
                }
            },
            error: function (result) {
                launchToast('danger',trans('Error'),result.responseJSON.message);
            }
        });
    },

    /**
     * Copy stream info to clipboard function
     * @param type
     */
    copyStreamData:function (type) {
        let content = '';
        if(type === 'url'){
            content = $('#stream-details-dialog #stream-url').val();
        }
        if(type === 'key'){
            content = $('#stream-details-dialog #stream-key').val();
        }
        copyToClipboard(content,'#stream-details-dialog');
        launchToast('success', trans('Success'), trans('Link copied to clipboard')+'.', 'now');
    }

};
