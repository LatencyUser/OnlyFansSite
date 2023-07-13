/**
 * Deposit page component
 */
"use strict";
/* global FileUpload, app, mediaSettings, trans, launchToast */

$(function () {
    // Deposit amount change event listener
    $('#deposit-amount').on('change', function () {
        if (!DepositSettings.depositAmountValidation()) {
            return false;
        }

        // update payment amount
        DepositSettings.amount = $('#deposit-amount').val();
    });

    // Checkout proceed button event listener
    $('.deposit-continue-btn').on('click', function () {
        DepositSettings.initPayment();
    });

    $('.custom-control').on('change', function () {
        $('.error-message').hide();
        $('.invalid-files-error').hide();
        $('.payment-error').hide();
        DepositSettings.triggerManualPaymentDetails();
    });

    DepositSettings.initUploader();
});

/**
 * Deposit class
 */
var DepositSettings = {

    stripe: null,
    paymentProvider: null,
    amount: null,
    myDropzone : null,
    uploadedFiles: [],
    manualPaymentDescription: null,

    /**
     * Instantiates new payment session
     */
    initPayment: function () {
        if (!DepositSettings.depositAmountValidation()) {
            return false;
        }

        let processor = DepositSettings.getSelectedPaymentMethod();
        if (processor !== false) {
            $('.paymentProcessorError').hide();
            $('.error-message').hide();
            if(processor === 'manual'){
                let paymentValidation = DepositSettings.manualPaymentValidation();
                if(!paymentValidation) {
                    return false;
                }
            }
            DepositSettings.updateDepositForm();
            $('.payment-button').trigger('click');
        } else {
            $('.payment-error').removeClass('d-none');
        }
    },

    /**
     * Returns currently selected payment method
     */
    getSelectedPaymentMethod: function () {
        const val = $('input[name="payment-radio-option"]:checked').val();
        if (val) {
            switch (val) {
            case 'payment-stripe':
                DepositSettings.provider = 'stripe';
                break;
            case 'payment-paypal':
                DepositSettings.provider = 'paypal';
                break;
            case 'payment-coinbase':
                DepositSettings.provider = 'coinbase';
                break;
            case 'payment-manual':
                DepositSettings.provider = 'manual';
                break;
            case 'payment-nowpayments':
                DepositSettings.provider = 'nowpayments';
                break;
            case 'payment-ccbill':
                DepositSettings.provider = 'ccbill';
                break;
            case 'payment-paystack':
                DepositSettings.provider = 'paystack';
                break;
            }
            return DepositSettings.provider;
        }
        return false;
    },

    /**
     * Show payment details on deposit form
     */
    triggerManualPaymentDetails: function() {
        let paymentMethod = this.getSelectedPaymentMethod();
        let manualDetails = $('.manual-details');
        if(paymentMethod === 'manual') {
            if(manualDetails.hasClass('d-none')){
                $(manualDetails.removeClass('d-none'));
            }
        } else {
            if(!manualDetails.hasClass('d-none')) {
                manualDetails.addClass('d-none');
            }
        }
    },

    /**
     * Updates deposit form with predefined values
     */
    updateDepositForm: function () {
        $('#payment-type').val('deposit');
        $('#provider').val(DepositSettings.provider);
        $('#wallet-deposit-amount').val(DepositSettings.amount);
        $('#manual-payment-files').val(DepositSettings.uploadedFiles);
        $('#manual-payment-description').val($('#manualPaymentDescription').val());
    },

    /**
     * Validates deposit amount field
     * @returns {boolean}
     */
    depositAmountValidation: function () {
        const depositAmount = $('#deposit-amount').val();
        if (depositAmount.length < 1 || (depositAmount.length > 0 && (parseFloat(depositAmount) < parseFloat(app.depositMinAmount) || parseFloat(depositAmount) > parseFloat(app.depositMaxAmount)))) {
            $('#deposit-amount').addClass('is-invalid');
            return false;
        } else {
            $('#deposit-amount').removeClass('is-invalid');
            $('#wallet-deposit-amount').val(depositAmount);
            return true;
        }
    },

    /**
     * Instantiates the media uploader
     */
    initUploader:function () {
        try{
            let selector = '.dropzone';
            DepositSettings.myDropzone = new window.Dropzone(selector, {
                url: app.baseUrl + '/attachment/upload/payment-request',
                previewTemplate: document.querySelector('#tpl').innerHTML,
                paramName: "file", // The name that will be used to transfer the file
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                // clickable:[`${selector} .upload-button`],
                maxFilesize: mediaSettings.max_file_upload_size, // MB
                addRemoveLinks: true,
                dictRemoveFile: "x",
                acceptedFiles: mediaSettings.manual_payments_file_extensions,
                dictDefaultMessage: trans("Drop files here to upload"),
                autoDiscover: false,
                previewsContainer: ".dropzone-previews",
                autoProcessQueue: true,
                parallelUploads: 1,
            });
            DepositSettings.myDropzone.on("addedfile", file => {
                FileUpload.updatePreviewElement(file, true);
            });
            DepositSettings.myDropzone.on("success", (file, response) => {
                DepositSettings.uploadedFiles.push(response.attachmentID);
                file.upload.attachmentId = response.attachmentID;
                DepositSettings.manualPaymentValidation();
            });
            DepositSettings.myDropzone.on("removedfile", function(file) {
                DepositSettings.removeAsset(file.upload.attachmentId);
                DepositSettings.uploadedFiles = DepositSettings.uploadedFiles.filter(uploadedFile => uploadedFile !== file.upload.attachmentId);
            });
            DepositSettings.myDropzone.on("error", (file, errorMessage) => {
                if(typeof errorMessage.errors !== 'undefined'){
                    // launchToast('danger',trans('Error'),errorMessage.errors.file)
                    $.each(errorMessage.errors,function (field,error) {
                        launchToast('danger',trans('Error'),error);
                    });
                }
                else{
                    if(typeof errorMessage.message !== 'undefined'){
                        launchToast('danger',trans('Error'),errorMessage.message);
                    }
                    else{
                        launchToast('danger',trans('Error'),errorMessage);
                    }
                }
                DepositSettings.myDropzone.removeFile(file);
            });
            // eslint-disable-next-line no-empty
        } catch (e) {
        }
    },

    /**
     * Removes the uploaded asset
     * @param attachmentId
     */
    removeAsset: function (attachmentId) {
        $.ajax({
            type: 'POST',
            data: {
                'attachmentId': attachmentId,
            },
            url: app.baseUrl + '/attachment/remove',
            success: function () {
                launchToast('success',trans('Success'),trans('Attachment removed.'));
            },
            error: function () {
                launchToast('danger',trans('Error'),trans('Failed to remove the attachment.'));
            }
        });
    },

    /**
     * Validates manual payment files
     * @returns {boolean}
     */
    manualPaymentValidation: function () {
        const uploadedFilesCount = DepositSettings.uploadedFiles.length;
        if (uploadedFilesCount < 1) {
            if($('.invalid-files').hasClass('d-none')){
                $('.invalid-files').removeClass('d-none');
            }
            return false;
        } else {
            if(!$('.invalid-files').hasClass('d-none')) {
                $('.invalid-files').addClass('d-none');
            }
            return true;
        }
    },
};
