@extends('voyager::master')

@section('page_title', __('voyager::generic.viewing').' '.__('voyager::generic.settings'))

@section('css')
    <link href="{{ asset('css/admin-settings.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{asset('/libs/@simonwep/pickr/dist/themes/nano.min.css')}}">
@stop

@section('page_header')
    <h1 class="page-title">
        <i class="voyager-settings"></i> {{ __('voyager::generic.settings') }}
    </h1>
@stop

@section('content')
    <div class="page-content settings container-fluid">
        <form action="{{ route('voyager.settings.update') }}" method="POST" enctype="multipart/form-data" class="save-settings-form">
            {{ method_field("PUT") }}
            {{ csrf_field() }}
            <input type="hidden" name="setting_tab" class="setting_tab" value="{{ $active }}" />
            <div class="panel">

                <div class="page-content settings container-fluid">
                    <ul class="nav nav-tabs">
                        <?php
                        $categoriesOrder = [
                            'Site',
                            'Storage',
                            'Media',
                            'Feed',
                            'Payments',
                            'Websockets',
                            'Emails',
                            'Social login',
                            'Social media',
                            'Custom Code / Ads',
                            'Admin',
                            'Streams',
                            'Compliance',
                            'Security',
                            'Referrals',
                            'Colors',
                        ];
                        $categories = [];
                        foreach($categoriesOrder as $category){
                            if(isset( $settings[$category])){
                                $categories[$category] = $settings[$category];
                            }
                        }
                        $settings = $categories;
                        ?>
                        {{--                        <pre>--}}
                        {{--                        </pre>--}}
                        @foreach($settings as $group => $setting)
                            @if($group != 'Colors' && $group != 'License')
                                <li @if($group == $active) class="active" @endif>
                                    <a data-toggle="tab" class="settings-menu-{{lcfirst($group)}}" href="#{{ \Illuminate\Support\Str::slug($group) }}">{{ $group }}</a>
                                </li>
                            @endif
                        @endforeach
                        <li @if($group === $active && $active === 'Colors') class="active" @endif>
                            <!-- <a data-toggle="tab" href="#colors">Colors</a> -->
                        </li>
                        <li @if($group === $active && $active === 'License') class="active" @endif>
                            <!-- <a data-toggle="tab" href="#license">License</a> -->
                        </li>
                    </ul>

                    <div class="tab-content">

                        <div id="license" class="tab-pane fade in @if($group == $active && $active === 'License') active @endif">

                            <div class="kind-of-a-form-control">

                                <div class="panel-heading setting-row setting-theme_license" data-settingkey="license_product_license_key">
                                    <h3 class="panel-title">
                                        Product license code
                                    </h3>
                                </div>

                                <div class="panel-body no-padding-left-right setting-row" data-settingkey="license_product_license_key">
                                    <div class="col-md-12 no-padding-left-right">
                                        <input disabled type="password" class="form-control license_product_license_key" name="license_product_license_key" placeholder="Your license key" value="{{getSetting('license.product_license_key') ? getSetting('license.product_license_key') : ''}}">
                                    </div>
                                </div>
                                <div class="admin-setting-description">
                                    <code>
                                        Your product license key.
                                    </code>
                                </div>

                                <div class="d-none">
                                    <select class="form-control group_select d-none" name="license_product_license_key_group">
                                        @foreach($groups as $group)
                                            <option value="License" selected></option>
                                        @endforeach
                                    </select>
                                </div>


                            </div>


                        </div>

                        <div id="colors" class="tab-pane fade in @if($group == $active && $active === 'Colors') active @endif">
                            <div class="">
                                <div class="alert alert-info alert-dismissible">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>

                                    <div class="info-label d-flex">
                                        <div class="icon voyager-info-circled"></div>
                                        <span class="ml-2">
                                            Few general notes about generating themes.
                                        </span>
                                    </div>
                                    <ul class="mt-05">
                                        <li>The themes are generated on a remote server. Timings may vary but it might take between 20-40s for a run.</li>
                                        <li>Regular license holders can generate 5 themes per day.</li>
                                        <li>If <code>zip</code> extension is available on the server, the theme will be updated automatically.</li>
                                        <li>If the extension is not available, you will need to upload the archive you'll be getting onto the following directory : <code>public/css/theme</code>.</li>
                                        <li>When updating your site, remember to backup your <code>public/css/theme</code> folder and restore it after the update.</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="kind-of-a-form-control">

                                <div class="panel-heading setting-row setting-theme_license" data-settingkey="theme_license">
                                    <h3 class="panel-title">
                                        License code
                                    </h3>
                                </div>

                                <div class="panel-body no-padding-left-right setting-row setting-theme_license" data-settingkey="theme_license">
                                    <div class="col-md-12 no-padding-left-right">
                                        <input disabled type="password" class="form-control theme_license_field" name="theme_license" placeholder="Your license key">
                                    </div>
                                </div>
                                <div class="admin-setting-description">
                                    <code>
                                        Your product license key.
                                    </code>
                                </div>

                            </div>

                            <div class="kind-of-a-form-control">

                                <div class="panel-heading setting-row setting-theme_color_code" data-settingkey="theme_color_code">
                                    <h3 class="panel-title">
                                        Primary color code
                                    </h3>
                                </div>

                                <div class="panel-body no-padding-left-right setting-row setting-theme_color_code" data-settingkey="theme_color_code">
                                    <div class="col-md-12 no-padding-left-right">
                                        <input type="text" class="form-control" name="theme_color_code" id="theme_color_code" value="#{{getSetting('colors.theme_color_code') ? getSetting('colors.theme_color_code') : 'cb0c9f'}}">
                                    </div>
                                </div>
                                <div class="admin-setting-description">
                                    <code>
                                        Theme primary color hex code. EG: #cb0c9f
                                    </code>
                                </div>

                            </div>

                            <div class="row">

                                <div class="kind-of-a-form-control col-lg-6">

                                    <div class="panel-heading setting-row setting-theme_gradient_from" data-settingkey="theme_gradient_from">
                                        <h3 class="panel-title">
                                            Gradient color start from
                                        </h3>
                                    </div>

                                    <div class="panel-body no-padding-left-right setting-row setting-theme_gradient_from" data-settingkey="theme_gradient_from">
                                        <div class="col-md-12 no-padding-left-right">
                                            <input type="text" class="form-control" name="theme_gradient_from" id="theme_gradient_from" value="#{{getSetting('colors.theme_gradient_from') ? getSetting('colors.theme_gradient_from') : 'cb0c9f'}}">
                                        </div>
                                    </div>
                                    <div class="admin-setting-description">
                                        <code>
                                            Theme's primary gradient - start from, color hex code. EG: #7928CA
                                        </code>
                                    </div>

                                </div>

                                <div class="kind-of-a-form-control col-lg-6">

                                    <div class="panel-heading setting-row setting-theme_gradient_to" data-settingkey="theme_gradient_to">
                                        <h3 class="panel-title">
                                            Gradient color ends on
                                        </h3>
                                    </div>

                                    <div class="panel-body no-padding-left-right setting-row setting-theme_gradient_to" data-settingkey="theme_gradient_to">
                                        <div class="col-md-12 no-padding-left-right">
                                            <input type="text" class="form-control" name="theme_gradient_to" id="theme_gradient_to" value="#{{getSetting('colors.theme_gradient_to') ? getSetting('colors.theme_gradient_to') : 'cb0c9f'}}">
                                        </div>
                                    </div>
                                    <div class="admin-setting-description">
                                        <code>
                                            Theme's primary gradient - ends on, color hex code. EG: #FF0080
                                        </code>
                                    </div>

                                </div>


                                <div class="kind-of-a-form-control col-lg-12">

                                    <div class="panel-heading setting-row setting-theme_skip_rtl" data-settingkey="theme_skip_rtl">
                                        <h3 class="panel-title">
                                            Include RTL versions
                                        </h3>
                                    </div>

                                    <div class="panel-body no-padding-left-right setting-row setting-theme_skip_rtl" data-settingkey="theme_skip_rtl">
                                        <div class="col-md-12 no-padding-left-right">
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" name="theme_skip_rtl" value="">
                                                    Generate RTL Versions as well
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="admin-setting-description">
                                        <code>
                                            Choose if RTL version of the theme should be generated or not. If enabled, theme generation time will increase.
                                        </code>
                                    </div>

                                </div>

                            </div>
                        </div>

                        @foreach($settings as $group => $group_settings)
                            <div id="{{ \Illuminate\Support\Str::slug($group) }}" class="tab-pane fade in @if($group == $active) active @endif">

                                <div class="tab-additional-info">

                                    @if($group == 'Emails')

                                        <div class="emails-info">
                                            <div class="alert alert-info alert-dismissible mb-1">
                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                <div class="info-label mb-0"><div class="icon voyager-info-circled"></div> You can use any SMTP you have access to or mailgun API. Full info can be found over in the documentation.</div>
                                            </div>
                                        </div>

                                    @endif

                                    @if($group == 'Social login')

                                        <div class="social-login-info">
                                            <div class="alert alert-info alert-dismissible mb-1">
                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                <div class="info-label"><div class="icon voyager-info-circled"></div>Each of the social login provider will require you a <i><strong>"Callback Url"</strong></i>. Here are the endpoints that you will need to set up for each provider:</div>
                                                <ul>
                                                    <li><code>Facebook: {{route('social.login.callback',['provider'=>'facebook'])}}</code></li>
                                                    <li><code>Twitter: {{route('social.login.callback',['provider'=>'twitter'])}}</code></li>
                                                    <li><code>Google: {{route('social.login.callback',['provider'=>'google'])}}</code></li>
                                                </ul>
                                            </div>
                                        </div>
                                    @endif

                                </div>

                                @if($group == 'ReCaptcha')
                                    <div class="recaptcha-info">
                                        <div class="alert alert-info alert-dismissible mb-1">
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                            <div class="info-label mb-0 d-flex"><div class="icon voyager-info-circled"></div> <div class="ml-2"> You can get your API Keys from <a target="_blank" class="text-white" href="https://www.google.com/recaptcha/admin">this link</a>. More info in the documentation.</div></div>
                                        </div>
                                    </div>
                                @endif

                                @if($group == 'Payments')

                                    <div class="tab-additional-info">

                                        <div class="payments-info">
                                            <div class="alert alert-info alert-dismissible mb-1 payments-info-crons">
                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                <div class="info-label"><div class="icon voyager-dollar"></div>The payment system requires cronjobs so you can easily setup them by using the following line:</div>
                                                <ul>
                                                    <li><code>* * * * * cd {{base_path()}} && php artisan schedule:run >> /dev/null 2>&1</code></li>
                                                </ul>
                                                {{--                                                <div class="info-label mt-05">For cPanel based installations, you can remove the <i>{root}</i> username out of the command above.</div>--}}
                                                <div class="mt-05">
                                                    Before setting up the payment processors, please also give the documentation a read.
                                                </div>
                                            </div>

                                            <div class="alert alert-info alert-dismissible mb-1 payments-info-paypal d-none">
                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                <div class="info-label"><div class="icon voyager-info-circled"></div> In order to be able to receive payment updates from Paypal, please use these webhooks endpoints:</div>
                                                <ul>
                                                    <li><code>{{route('paypal.payment.update')}}</code></li>
                                                </ul>
                                                <div class="mt-05">
                                                    Before setting up the payment processors, please also give the documentation a read.
                                                </div>
                                            </div>


                                            <div class="alert alert-info alert-dismissible mb-1 payments-info-stripe d-none">
                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                <div class="info-label"><div class="icon voyager-info-circled"></div> In order to be able to receive payment updates from Stripe, please use these webhooks endpoints:</div>
                                                <ul>
                                                    <li><code>{{route('stripe.payment.update')}}</code></li>
                                                </ul>
                                                <div class="mt-05">
                                                    Before setting up the payment processors, please also give the documentation a read.
                                                </div>
                                            </div>

                                            <div class="alert alert-info alert-dismissible mb-1 payments-info-coinbase d-none">
                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                <div class="info-label"><div class="icon voyager-info-circled"></div> In order to be able to receive payment updates from Coinbase, please use these webhooks endpoints:</div>
                                                <ul>
                                                    <li><code>{{route('coinbase.payment.update')}}</code></li>
                                                </ul>
                                            </div>



                                            <div class="alert alert-info alert-dismissible mb-1 payments-info-ccbill d-none">
                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                <div class="info-label"><div class="icon voyager-info-circled"></div> In order to use CCBill as payment provider you'll need the following endpoints:
                                                    <ul>
                                                        <li>Webook URL: <code>{{route('ccBill.payment.update')}}</code></li>
                                                        <li>Approval & Denial URL: <code>{{route('payment.checkCCBillPaymentStatus')}}</code></li>
                                                    </ul>
                                                </div>
                                            </div>

                                            <div class="alert alert-info alert-dismissible mb-1 payments-info-paystack d-none">
                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                <div class="info-label"><div class="icon voyager-info-circled"></div> In order to use Paystack as payment provider you'll need the following endpoints:</div>
                                                <ul>
                                                    <li>Webook URL: <code>{{route('paystack.payment.update')}}</code></li>
                                                    <li>Callback URL: <code>{{route('payment.checkPaystackPaymentStatus')}}</code></li>
                                                </ul>
                                            </div>


                                            {{--                                            <div class="alert alert-info alert-dismissible mb-1">--}}
                                            {{--                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>--}}
                                            {{--                                                <div class="info-label"><div class="icon voyager-info-circled"></div>--}}
                                            {{--                                                    Before using NowPayments as crypto payment provider we recommend reading the documentation --}}
                                            {{--                                        (Setting up the payments providers section).                --}}
                                            {{--                                                </div>--}}
                                            {{--                                            </div>--}}

                                            {{--                                            <div class="invoices-info">--}}
                                            {{--                                                <div class="alert alert-info alert-dismissible mb-1">--}}
                                            {{--                                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>--}}
                                            {{--                                                    <div class="info-label mb-0"><div class="icon voyager-info-circled"></div> You can disable invoices entirely by leaving any of the fields below empty.</div>--}}
                                            {{--                                                </div>--}}
                                            {{--                                            </div>--}}

                                        </div>


                                    </div>

                                    <div class="">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h4 class="mb-4">Payments settings</h4>

                                                <div class="tabbable-panel">
                                                    <div class="tabbable-line">
                                                        <ul class="nav nav-tabs ">
                                                            <li class="active">
                                                                <a href="#payments-general" data-toggle="tab" onclick="Admin.paymentsSettingsSubTabSwitch('general')">
                                                                    General settings </a>
                                                            </li>
                                                            <li>
                                                                <a href="#payments-processors" data-toggle="tab" onclick="Admin.paymentsSettingsSubTabSwitch('processors')">
                                                                    Payment processors </a>
                                                            </li>
                                                            <li>
                                                                <a href="#payments-invoices" data-toggle="tab" onclick="Admin.paymentsSettingsSubTabSwitch('invoices')">
                                                                    Invoices </a>
                                                            </li>
                                                            <li>
                                                                <a href="#payments-withdrawals" data-toggle="tab" onclick="Admin.paymentsSettingsSubTabSwitch('withdrawals')">
                                                                    Withdrawals </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>

                                    <div class="panel-heading setting-row setting-payments.driver" data-settingkey="payments.driver">
                                        <h3 class="panel-title"> Payment processor  </h3>
                                    </div>
                                    <div class="panel-body no-padding-left-right setting-row setting-payments.driver" data-settingkey="payments.driver">
                                        <div class="col-md-12 no-padding-left-right">
                                            <select class="form-control" name="payments.driver" id="payments.driver">
                                                <option value="stripe">Stripe</option>
                                                <option value="paypal">Paypal</option>
                                                <option value="coinbase">Coinbase</option>
                                                <option value="nowpayments">NowPayments</option>
                                                <option value="ccbill">CCBill</option>
                                                <option value="offline">Offline payments</option>
                                                <option value="paystack">Paystack</option>
                                            </select>

                                        </div>

                                    </div>
                                @endif

                                @foreach($group_settings as $setting)
                                    <div class="panel-heading setting-row setting-{{$setting->key}}" data-settingkey={{$setting->key}}>
                                        <h3 class="panel-title">
                                            {{ $setting->display_name }} @if(config('voyager.show_dev_tips'))<code>getSetting('{{ $setting->key }}')</code>@endif
                                        </h3>
                                        @if((env('APP_ENV') != 'production' && env('APP_DEBUG') == true))
                                            <div class="panel-actions">
                                                <a href="{{ route('voyager.settings.move_up', $setting->id) }}">
                                                    <i class="sort-icons voyager-sort-asc"></i>
                                                </a>
                                                <a href="{{ route('voyager.settings.move_down', $setting->id) }}">
                                                    <i class="sort-icons voyager-sort-desc"></i>
                                                </a>
                                                @can('delete', Voyager::model('Setting'))
                                                    <i class="voyager-trash"
                                                       data-id="{{ $setting->id }}"
                                                       data-display-key="{{ $setting->key }}"
                                                       data-display-name="{{ $setting->display_name }}"></i>
                                                @endcan
                                            </div>
                                        @endif
                                    </div>

                                    <div class="panel-body no-padding-left-right setting-row setting-{{$setting->key}}" data-settingkey={{$setting->key}}>
                                        <div class="{{(env('APP_ENV') != 'production' && env('APP_DEBUG') == true) ? 'col-md-10' : 'col-md-12'}} no-padding-left-right">
                                            @if ($setting->type == "text")
                                                <input type="text" class="form-control" name="{{ $setting->key }}" value="{{ $setting->value }}">
                                            @elseif($setting->type == "text_area")
                                                <textarea class="form-control" name="{{ $setting->key }}">{{ $setting->value ?? '' }}</textarea>
                                            @elseif($setting->type == "rich_text_box")
                                                <textarea class="form-control richTextBox" name="{{ $setting->key }}">{{ $setting->value ?? '' }}</textarea>
                                            @elseif($setting->type == "code_editor")
                                                <?php $options = json_decode($setting->details); ?>
                                                <div id="{{ $setting->key }}" data-theme="{{ @$options->theme }}" data-language="{{ @$options->language }}" class="ace_editor min_height_400" name="{{ $setting->key }}">{{ $setting->value ?? '' }}</div>
                                                <textarea name="{{ $setting->key }}" id="{{ $setting->key }}_textarea" class="hidden">{{ $setting->value ?? '' }}</textarea>
                                            @elseif($setting->type == "image" || $setting->type == "file")
                                                @if(isset( $setting->value ) && !empty( $setting->value ) && Storage::disk(config('voyager.storage.disk'))->exists($setting->value))
                                                    <div class="img_settings_container">
                                                        <a href="{{ route('voyager.settings.delete_value', $setting->id) }}" class="voyager-x delete_value"></a>
                                                        <img src="{{ Storage::disk(config('voyager.storage.disk'))->url($setting->value) }}" class="setting-value-image">
                                                    </div>
                                                    <div class="clearfix"></div>
                                                @elseif($setting->type == "file" && isset( $setting->value ))
                                                    @if(json_decode($setting->value) !== null)
                                                        @foreach(json_decode($setting->value) as $file)
                                                            <div class="fileType">
                                                                <a class="fileType" target="_blank" href="{{ Storage::disk(config('voyager.storage.disk'))->url($file->download_link) }}">
                                                                    {{ $file->original_name }}
                                                                </a>
                                                                <a href="{{ route('voyager.settings.delete_value', $setting->id) }}" class="voyager-x delete_value"></a>
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                @endif
                                                <input type="file" name="{{ $setting->key }}">
                                            @elseif($setting->type == "select_dropdown")
                                                <?php $options = json_decode($setting->details); ?>
                                                <?php $selected_value = (isset($setting->value) && !empty($setting->value)) ? $setting->value : NULL; ?>
                                                <select class="form-control" name="{{ $setting->key }}">
                                                    <?php $default = (isset($options->default)) ? $options->default : NULL; ?>
                                                    @if(isset($options->options))
                                                        @foreach($options->options as $index => $option)
                                                            <option value="{{ $index }}" @if($default == $index && $selected_value === NULL) selected="selected" @endif @if($selected_value == $index) selected="selected" @endif>{{ $option }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>

                                            @elseif($setting->type == "radio_btn")
                                                <?php $options = json_decode($setting->details); ?>
                                                <?php $selected_value = (isset($setting->value) && !empty($setting->value)) ? $setting->value : NULL; ?>
                                                <?php $default = (isset($options->default)) ? $options->default : NULL; ?>
                                                <ul class="radio">
                                                    @if(isset($options->options))
                                                        @foreach($options->options as $index => $option)
                                                            <li>
                                                                <input type="radio" id="option-{{ $index }}" name="{{ $setting->key }}"
                                                                       value="{{ $index }}" @if($default == $index && $selected_value === NULL) checked @endif @if($selected_value == $index) checked @endif>
                                                                <label for="option-{{ $index }}">{{ $option }}</label>
                                                                <div class="check"></div>
                                                            </li>
                                                        @endforeach
                                                    @endif
                                                </ul>
                                            @elseif($setting->type == "checkbox")
                                                <?php $options = json_decode($setting->details); ?>
                                                <?php $checked = (isset($setting->value) && $setting->value == 1) ? true : false; ?>
                                                @if (isset($options->on) && isset($options->off))
                                                    <input type="checkbox" name="{{ $setting->key }}" class="toggleswitch" @if($checked) checked @endif data-on="{{ $options->on }}" data-off="{{ $options->off }}">
                                                @else
                                                    <input type="checkbox" name="{{ $setting->key }}" @if($checked) checked @endif class="toggleswitch">
                                                @endif
                                            @endif
                                        </div>
                                        <div class="{{(env('APP_ENV') != 'production' && env('APP_DEBUG') == true) ? 'col-md-2 no-padding-left-right' : 'col-md-2 no-padding-left-right d-none'}}">
                                            <select class="form-control group_select" name="{{ $setting->key }}_group">
                                                @foreach($groups as $group)
                                                    <option value="{{ $group }}" {!! $setting->group == $group ? 'selected' : '' !!}>{{ $group }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                    </div>
                                    <?php
                                    $settingDetails = json_decode($setting->details);
                                    $hasDescription = false;
                                    if(isset($settingDetails->description)){
                                        $hasDescription = true;
                                    }
                                    ?>
                                    @if($hasDescription)
                                        <div class="admin-setting-description setting-row setting-{{$setting->key}}" data-settingkey={{$setting->key}}>
                                            <code>
                                                {{$settingDetails->description}}
                                            </code>
                                        </div>
                                    @endif
                                    @if(!$loop->last)
                                        <hr class="setting-row setting-{{$setting->key}}" data-settingkey={{$setting->key}}>
                                    @endif
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
            <button type="submit" class="btn btn-primary pull-right">{{ __('voyager::settings.save') }}</button>
        </form>

        <div class="clearfix"></div>

        @can('add', Voyager::model('Setting'))
            @if((env('APP_ENV') != 'production' && env('APP_DEBUG') == true))
                <div class="panel mt-10">
                    <div class="panel-heading new-setting">
                        <hr>
                        <h3 class="panel-title"><i class="voyager-plus"></i> {{ __('voyager::settings.new') }}</h3>
                    </div>
                    <div class="panel-body">
                        <form action="{{ route('voyager.settings.store') }}" method="POST">
                            {{ csrf_field() }}
                            <input type="hidden" name="setting_tab" class="setting_tab" value="{{ $active }}" />
                            <div class="col-md-3">
                                <label for="display_name">{{ __('voyager::generic.name') }}</label>
                                <input type="text" class="form-control" name="display_name" placeholder="{{ __('voyager::settings.help_name') }}" required="required">
                            </div>
                            <div class="col-md-3">
                                <label for="key">{{ __('voyager::generic.key') }}</label>
                                <input type="text" class="form-control" name="key" placeholder="{{ __('voyager::settings.help_key') }}" required="required">
                            </div>
                            <div class="col-md-3">
                                <label for="type">{{ __('voyager::generic.type') }}</label>
                                <select name="type" class="form-control" required="required">
                                    <option value="">{{ __('voyager::generic.choose_type') }}</option>
                                    <option value="text">{{ __('voyager::form.type_textbox') }}</option>
                                    <option value="text_area">{{ __('voyager::form.type_textarea') }}</option>
                                    <option value="rich_text_box">{{ __('voyager::form.type_richtextbox') }}</option>
                                    <option value="code_editor">{{ __('voyager::form.type_codeeditor') }}</option>
                                    <option value="checkbox">{{ __('voyager::form.type_checkbox') }}</option>
                                    <option value="radio_btn">{{ __('voyager::form.type_radiobutton') }}</option>
                                    <option value="select_dropdown">{{ __('voyager::form.type_selectdropdown') }}</option>
                                    <option value="file">{{ __('voyager::form.type_file') }}</option>
                                    <option value="image">{{ __('voyager::form.type_image') }}</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="group">{{ __('voyager::settings.group') }}</label>
                                <select class="form-control group_select group_select_new" name="group">
                                    @foreach($groups as $group)
                                        <option value="{{ $group }}">{{ $group }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12">
                                <a id="toggle_options"><i class="voyager-double-down"></i> {{ mb_strtoupper(__('voyager::generic.options')) }}</a>
                                <div class="new-settings-options">
                                    <label for="options">{{ __('voyager::generic.options') }}
                                        <small>{{ __('voyager::settings.help_option') }}</small>
                                    </label>
                                    <div id="options_editor" class="form-control min_height_200" data-language="json"></div>
                                    <textarea id="options_textarea" name="details" class="hidden"></textarea>
                                    <div id="valid_options" class="alert-success alert d-none">{{ __('voyager::json.valid') }}</div>
                                    <div id="invalid_options" class="alert-danger alert d-none">{{ __('voyager::json.invalid') }}</div>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                            <button type="submit" class="btn btn-primary pull-right new-setting-btn">
                                <i class="voyager-plus"></i> {{ __('voyager::settings.add_new') }}
                            </button>
                            <div class="clearfix"></div>
                        </form>
                    </div>
                </div>
            @endif
        @endcan
    </div>

    @can('delete', Voyager::model('Setting'))
        <div class="modal modal-danger fade" tabindex="-1" id="delete_modal" role="dialog">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('voyager::generic.close') }}">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title">
                            <i class="voyager-trash"></i> {!! __('voyager::settings.delete_question', ['setting' => '<span id="delete_setting_title"></span>']) !!}
                        </h4>
                    </div>
                    <div class="modal-footer">
                        <form action="#" id="delete_form" method="POST">
                            {{ method_field("DELETE") }}
                            {{ csrf_field() }}
                            <input type="submit" class="btn btn-danger pull-right delete-confirm" value="{{ __('voyager::settings.delete_confirm') }}">
                        </form>
                        <button type="button" class="btn btn-default pull-right" data-dismiss="modal">{{ __('voyager::generic.cancel') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endcan

@stop

@section('javascript')
    <script type="text/javascript" src="{{asset('/libs/@simonwep/pickr/dist/pickr.es5.min.js')}}"></script>
    <script>
        $('document').ready(function () {
            $('#toggle_options').on('click', function () {
                $('.new-settings-options').toggle();
                if ($('#toggle_options .voyager-double-down').length) {
                    $('#toggle_options .voyager-double-down').removeClass('voyager-double-down').addClass('voyager-double-up');
                } else {
                    $('#toggle_options .voyager-double-up').removeClass('voyager-double-up').addClass('voyager-double-down');
                }
            });

            @can('delete', Voyager::model('Setting'))
            $('.panel-actions .voyager-trash').on('click', function () {
                var display = $(this).data('display-name') + '/' + $(this).data('display-key');

                $('#delete_setting_title').text(display);

                $('#delete_form')[0].action = '{{ route('voyager.settings.delete', [ 'id' => '__id' ]) }}'.replace('__id', $(this).data('id'));
                $('#delete_modal').modal('show');
            });
            @endcan

            $('.toggleswitch').bootstrapToggle();

            $('[data-toggle="tab"]').on('click', function() {
                $(".setting_tab").val($(this).html());
            });

            $('.delete_value').on('click', function(e) {
                e.preventDefault();
                $(this).closest('form').attr('action', $(this).attr('href'));
                $(this).closest('form').submit();
            });

            // Initiliaze rich text editor
            tinymce.init(window.voyagerTinyMCE.getConfig());
        });
    </script>
    <script type="text/javascript">
        $(".group_select").not('.group_select_new').select2({
            tags: true,
            width: 'resolve'
        });
        $(".group_select_new").select2({
            tags: true,
            width: 'resolve',
            placeholder: '{{ __("voyager::generic.select_group") }}'
        });
        $(".group_select_new").val('').trigger('change');
    </script>
    <iframe id="form_target" name="form_target" class="d-none"></iframe>
    <form class="settings-upload" id="my_form" action="{{ route('voyager.upload') }}" target="form_target" method="POST" enctype="multipart/form-data">
        {{ csrf_field() }}
        <input name="image" id="upload_file" type="file" onchange="$('#my_form').submit();this.value='';">
        <input type="hidden" name="type_slug" id="type_slug" value="settings">
    </form>

    <script>
        try{
            var options_editor = ace.edit('options_editor');
            options_editor.getSession().setMode("ace/mode/json");

            var options_textarea = document.getElementById('options_textarea');
            options_editor.getSession().on('change', function() {
                console.log(options_editor.getValue());
                options_textarea.value = options_editor.getValue();
            });
        } catch (e) {
            // eslint-disable-next-line no-console
            console.warn(e);
        }

        var site_settings = {
            'emails.driver': "{{getSetting('emails.driver')}}",
            'storage.driver': "{{getSetting('storage.driver')}}",
            'websockets.driver': "{{getSetting('websockets.driver')}}",
            'colors.theme_color_code': "{{getSetting('colors.theme_color_code')}}",
            'colors.theme_gradient_from': "{{getSetting('colors.theme_gradient_from')}}",
            'colors.theme_gradient_to': "{{getSetting('colors.theme_gradient_to')}}",
            'license.product_license_key': "{{getSetting('license.product_license_key')}}",
        }

    </script>
@stop
