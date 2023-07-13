<h5 class="mt-3">{{__('Proceed with payment')}}</h5>
<div class="input-group mb-3 mt-3">
    <div class="input-group-prepend">
        <span class="input-group-text" id="amount-label">@include('elements.icon',['icon'=>'cash-outline','variant'=>'medium'])</span>
    </div>
    <input class="form-control" placeholder="{{\App\Providers\PaymentsServiceProvider::getDepositLimitAmounts()}}"
           aria-label="{{__('Username')}}"
           aria-describedby="amount-label"
           id="deposit-amount"
           type="number"
           min="{{\App\Providers\PaymentsServiceProvider::getDepositMinimumAmount()}}"
           step="1"
           max="{{\App\Providers\PaymentsServiceProvider::getDepositMaximumAmount()}}">
    <div class="invalid-feedback">{{__('Please enter a valid amount.')}}</div>
</div>

<div>
    <div class="payment-method">
        @if(config('paypal.client_id') && config('paypal.secret'))
            <div class="custom-control custom-radio mb-1">
                <input type="radio" id="customRadio1" name="payment-radio-option" class="custom-control-input"
                       value="payment-paypal">
                <label class="custom-control-label" for="customRadio1">{{__("Paypal")}}</label>
            </div>
        @endif
        @if(getSetting('payments.stripe_secret_key') && getSetting('payments.stripe_public_key'))
            <div class="custom-control custom-radio mb-1">
                <input type="radio" id="customRadio2" name="payment-radio-option" class="custom-control-input"
                       value="payment-stripe">
                <label class="custom-control-label stepTooltip" for="customRadio2" title=""
                       data-original-title="{{__('You need to login first')}}">{{__("Stripe")}}</label>
            </div>
        @endif
        @if(getSetting('payments.coinbase_api_key'))
            <div class="custom-control custom-radio mb-1">
                <input type="radio" id="customRadio3" name="payment-radio-option" class="custom-control-input"
                       value="payment-coinbase">
                <label class="custom-control-label stepTooltip" for="customRadio3" title="">{{__("Coinbase")}}</label>
            </div>
        @endif
        @if(getSetting('payments.nowpayments_api_key'))
            <div class="custom-control custom-radio mb-1">
                <input type="radio" id="customRadio5" name="payment-radio-option" class="custom-control-input"
                       value="payment-nowpayments">
                <label class="custom-control-label stepTooltip" for="customRadio5" title="">{{__("NowPayments Crypto")}}</label>
            </div>
        @endif
        @if(\App\Providers\PaymentsServiceProvider::ccbillCredentialsProvided())
            <div class="custom-control custom-radio mb-1">
                <input type="radio" id="customRadio6" name="payment-radio-option" class="custom-control-input"
                       value="payment-ccbill">
                <label class="custom-control-label stepTooltip" for="customRadio6" title="">{{__("CCBill")}}</label>
            </div>
        @endif
        @if(getSetting('payments.paystack_secret_key'))
            <div class="custom-control custom-radio mb-1">
                <input type="radio" id="customRadio7" name="payment-radio-option" class="custom-control-input"
                       value="payment-paystack">
                <label class="custom-control-label stepTooltip" for="customRadio7" title="">{{__("Paystack")}}</label>
            </div>
        @endif
        @if(getSetting('payments.allow_manual_payments'))
            <div class="custom-control custom-radio mb-1">
                <input type="radio" id="customRadio4" name="payment-radio-option" class="custom-control-input"
                       value="payment-manual">
                <label class="custom-control-label stepTooltip" for="customRadio4" title="">{{__("Bank transfer")}}</label>
            </div>
            <div class="manual-details d-none">
                <h5 class="mt-4 mb-3">{{__("Add payment details")}}</h5>

                <div class="alert alert-primary text-white font-weight-bold" role="alert">
                    <p class="mb-0">{{__('Once confirmed, your credit will be available and you will be notified via email.')}}</p>
                    <ul class="mt-2 mb-2">
                        <li>{{__('IBAN')}}: {{getSetting('payments.offline_payments_iban')}}</li>
                        <li>{{__('BIC/SWIFT')}}: {{getSetting('payments.offline_payments_swift')}}</li>
                        <li>{{__('Bank name')}}: {{getSetting('payments.offline_payments_bank_name')}}</li>
                        <li>{{__('Account owner')}}: {{getSetting('payments.offline_payments_owner')}}</li>
                        <li>{{__('Account number')}}: {{getSetting('payments.offline_payments_account_number')}}</li>
                        <li>{{__('Routing number')}}: {{getSetting('payments.offline_payments_routing_number')}}</li>
                    </ul>
                </div>

                <div>
                    <label for="manualPaymentDescription" title="">{{__("Description")}}</label>
                    <textarea class="form-control" id="manualPaymentDescription" rows="1"></textarea>
                </div>
                <p class="mb-1 mt-2">{{__("Please attach clear photos with one the following: check, money order or bank transfer.")}}</p>
                <div class="dropzone-previews dropzone manual-payment-uploader w-100 ppl-0 pr-0 pt-1 pb-1 border rounded"></div>
                <div class="text-danger invalid-files d-none">{{__('Please upload at least one file')}}</div>
            </div>
        @endif
    </div>
    <div class="payment-error error text-danger d-none mt-3">{{__('Please select your payment method')}}</div>
    <button class="btn btn-primary btn-block rounded mr-0 mt-4 deposit-continue-btn" type="submit">{{__('Add funds')}}
        <div class="spinner-border spinner-border-sm ml-2 d-none" role="status">
            <span class="sr-only">{{__('Loading...')}}</span>
        </div>
    </button>
</div>
@include('elements.uploaded-file-preview-template')




