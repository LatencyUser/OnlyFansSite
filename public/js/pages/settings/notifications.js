/**
 * Notification settings component
 */
"use strict";
/* global app, trans, launchToast */

$(function () {
    $('.notification-checkbox').on('click',function () {
        const key = $(this).attr('id');
        const val = $(this).prop("checked");
        NotificationsSettings.updateUserSettings(key,val);
    });
});

var NotificationsSettings = {

    /**
     * Updates user notifications flags
     * @param key
     * @param value
     */
    updateUserSettings: function (key,value) {
        $.ajax({
            type: 'POST',
            data: {
                'key': key,
                'value': value
            },
            dataType: 'json',
            url: app.baseUrl+'/my/settings/save',
            success: function (result) {
                if(result.success){
                    launchToast('success',trans('Success'), trans('Setting saved'));
                }
                else{
                    launchToast('danger',trans('Error'), trans('Setting save failed'));
                }
            },
            error: function () {
                launchToast('danger',trans('Error'), trans('Setting save failed'));
            }
        });
    }

};
