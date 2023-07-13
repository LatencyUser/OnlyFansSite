/**
 * Privacy settings component
 */
"use strict";
/* global GeneralSettings, app, launchToast, trans, reload, NotificationsSettings, userGeoBlocking */

$(function () {
    $('.custom-control-input').on('change',function () {
        const key = $(this).attr('id');
        const val = $(this).prop("checked");
        GeneralSettings.updateFlagSetting(key,val);
    });

    $('#enable_2fa').on('change',function () {
        if($('#enable_2fa').prop("checked")){
            $('.allowed-devices').removeClass('d-none');
        }
        else{
            $('.allowed-devices').addClass('d-none');
        }
    });

    if(userGeoBlocking.enabled){

        var $select = $("#countrySelect").selectize({
            onChange(value) {
                NotificationsSettings.updateUserSettings('geoblocked_countries',JSON.stringify(value));
            }
        });

        userGeoBlocking.countries.map((v)=>{
            var selectize = $select[0].selectize;
            selectize.addItem(v, true);
        });
    }

});

// eslint-disable-next-line no-unused-vars
var PrivacySettings = {

    deviceToBeDeleted: null,

    /**
     * Method used for removing a list
     */
    removeDevice: function(){
        $.ajax({
            type: 'DELETE',
            data: {
                'signature': PrivacySettings.deviceToBeDeleted
            },
            dataType: 'json',
            url: app.baseUrl+'/device-verify/delete',
            success: function (result) {
                if(result.success){
                    reload();
                }
                else{
                    launchToast('danger',trans('Error'),result.errors[0]);
                }
            },
            error: function (result) {
                launchToast('danger',trans('Error'),result.responseJSON.message);
            }
        });
    },

    /**
     * Shows up lists delete confirmation
     */
    showDeviceDeleteConfirmation: function(signature){
        PrivacySettings.deviceToBeDeleted = signature;
        $('#device-delete-dialog').modal('show');
    },
};
