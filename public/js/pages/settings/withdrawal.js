/**
 * Money settings component
 */
"use strict";
/* global app, launchToast, trans */

$(function () {
    Wallet.setPaymentMethodTitle();
    // Deposit amount change event listener
    $('#withdrawal-amount').on('change', function () {
        if (!Wallet.withdrawalAmountValidation()) {
            return false;
        }
    });
    // Checkout proceed button event listener
    $('.withdrawal-continue-btn').on('click', function () {
        Wallet.initWithdrawal();
    });
    $('.custom-control').on('change', function () {
        $('.withdrawal-error-message').hide();
    });
    $('#payment-methods').on('change', function() {
        Wallet.setPaymentMethodTitle();
    });
});

var Wallet = {

    /**
     * Instantiate withdrawal request
     * @returns {boolean}
     */
    initWithdrawal: function () {
        if(!Wallet.withdrawalAmountValidation()){
            return false;
        }

        $('.withdrawal-error-message').hide();
        $.ajax({
            type: 'POST',
            data: {
                amount: $('#withdrawal-amount').val(),
                message: $('#withdrawal-message').val(),
                identifier: $('#withdrawal-payment-identifier').val(),
                method: $('#payment-methods').find(":selected").text(),
            },
            url: app.baseUrl + '/withdrawals/request',
            success: function (result) {
                // eslint-disable-next-line no-undef
                const msgType = result.success ? 'success' : 'danger';
                const msgLabel = result.success ? trans('Success') : trans('Error');
                launchToast(msgType, msgLabel, result.message);

                // append new amounts
                $('.wallet-total-amount').html(result.totalAmount);
                $('.wallet-pending-amount').html(result.pendingBalance);

                // clear inputs
                $('#withdrawal-amount').val('');
                $('#withdrawal-message').val('');
                $('#withdrawal-payment-identifier').val('');

                // Clearing up err messages
                $('#withdrawal-amount').removeClass('is-invalid');
                $('#withdrawal-message').removeClass('is-invalid');

            },
            error: function (result) {
                if(result.status === 422 || result.status === 500) {
                    $.each(result.responseJSON.errors, function (field) {
                        if (field === 'amount') {
                            $('#withdrawal-amount').addClass('is-invalid');
                        }
                        if(field === 'message'){
                            $('#withdrawal-message').addClass('is-invalid');
                        }
                    });
                }
            }
        });
    },

    /**
     * Validates the withdrawal amount
     * @returns {boolean}
     */
    withdrawalAmountValidation: function () {
        let withdrawalAmount = $('#withdrawal-amount').val();
        if (withdrawalAmount.length === 0
            || (withdrawalAmount.length > 0 && (parseFloat(withdrawalAmount) < parseFloat(app.withdrawalsMinAmount)
                || parseFloat(withdrawalAmount) > parseFloat(app.withdrawalsMaxAmount)))) {
            $('#withdrawal-amount').addClass('is-invalid');
            return false;
        } else {
            $('#withdrawal-amount').removeClass('is-invalid');
            return true;
        }
    },

    /**
     * Get withdrawal payment identifier based on payment method from dropdown
     * @returns {string}
     */
    getPaymentIdentifierTitle: function() {
        let title;
        switch ($('#payment-methods').find(":selected").text()) {
        case 'Bank transfer':
            title = 'Bank account';
            break;
        case 'Paypal':
        case 'PayPal':
            title = 'PayPal email';
            break;
        case 'Crypto':
            title = 'Wallet address';
            break;
        case 'Other':
            title = 'Payment account';
            break;
        default:
            title = 'Payment account';
            break;
        }
        return title;
    },

    setPaymentMethodTitle: function () {
        let paymentIdentifierTitle = trans(Wallet.getPaymentIdentifierTitle());
        $('#payment-identifier-label').text(paymentIdentifierTitle);
    }

};
