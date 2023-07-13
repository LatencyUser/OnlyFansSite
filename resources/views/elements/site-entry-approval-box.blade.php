<div class="modal fade" tabindex="-1" role="dialog" id="site-entry-approval-dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="d-flex justify-content-center align-items-center mt-5">
                <img class="brand-logo pb-4" src="{{asset( (Cookie::get('app_theme') == null ? (getSetting('site.default_user_theme') == 'dark' ? getSetting('site.dark_logo') : getSetting('site.light_logo')) : (Cookie::get('app_theme') == 'dark' ? getSetting('site.dark_logo') : getSetting('site.light_logo'))) )}}">
            </div>

            <div class="d-flex justify-content-center align-items-center mt-4 mb-2 px-3 px-md-0">
                 <h4 class="text-uppercase text-bolder">{{__("Enter only if you are over 18")}}</h4>
            </div>

            <div class="modal-body">
                <p>{{__("The website contains content of adult nature and is only available to adults. If you are under the age of 18 (or 21 in some countries), if it is illegal to view such material in your jurisdiction or if it offends you, please do not continue.")}}</p>
            </div>

            <div class="d-flex">
                <div class="col-6">
                    <button type="submit" class="btn  btn-primary btn-block" onClick="acceptSiteEntry();">
                        {{__('Yes')}}
                    </button>
                </div>
                <div class="col-6">
                    <button type="submit" class="btn btn-link btn-block" onClick="redirect('{{getSetting('compliance.age_verification_cancel_url')}}')">
                        {{__('No')}}
                    </button>
                </div>
            </div>
            <div class="modal-body pt-2 pb-2">
                <p class="text-muted">{{__("You can read more about our")}} <a href="{{route('pages.get',['slug'=>'terms-and-conditions'])}}">{{__("terms of usage")}}</a> {{__("over this page")}}.</p>
            </div>
        </div>
    </div>
</div>
