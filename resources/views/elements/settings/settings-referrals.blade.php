<div class="pb-2">
    <div class="pb-2 text-center">{{__('Copy your referral link and invite other people to get a fee from their earnings.')}}</div>
    <div class="pl-5 pr-5">
        <div class="input-group">
            <input type="text" class="form-control text-center"
                   @switch(getSetting('referrals.referrals_default_link_page'))
                       @case('profile')
                           value="{{route('profile',['ref'=>\Illuminate\Support\Facades\Auth::user()->referral_code, 'username'=>\Illuminate\Support\Facades\Auth::user()->username])}}"
                       @break
                       @case('home')
                            value="{{route('home',['ref'=>\Illuminate\Support\Facades\Auth::user()->referral_code])}}"
                       @break
                       @case('register')
                            value="{{route('register',['ref'=>\Illuminate\Support\Facades\Auth::user()->referral_code])}}"
                       @break
                   @endswitch
                   placeholder="{{route('profile',['ref'=>\Illuminate\Support\Facades\Auth::user()->referral_code, 'username'=>\Illuminate\Support\Facades\Auth::user()->username])}}"
                   id="copy-input"
            >
            <span class="input-group-btn">
      <button class="btn btn-primary btn-block rounded mr-0 text-truncate" type="button" id="copy-button"
              data-toggle="tooltip" data-placement="bottom"
              title={{__('Copy to Clipboard')}}>
        {{__('Copy')}}
      </button>
    </span>
        </div>
    </div>
</div>
<div class="table-wrapper ">
    <div class="">
        <div class="col py-3 text-bold border-bottom">
            <div class="col-lg-12 text-truncate d-md-block text-center">{{__('Your referral list')}}</div>
        </div>
        @if(count($referrals))
            @foreach($referrals as $referral)
                <div class="col d-flex align-items-center py-3 border-bottom">
                    <div class="pl-2">
                        @if($referral->usedBy)
                            <a href="{{route('profile',['username'=>$referral->usedBy->username])}}">
                                <img class="rounded-circle avatar" src="{{$referral->usedBy->avatar}}" alt="{{$referral->usedBy->username}}">
                            </a>
                        @else
                            <a href="{{route('profile',['username'=>$referral->usedBy->username])}}">
                                <img class="rounded-circle avatar" src="{{\App\Providers\GenericHelperServiceProvider::getStorageAvatarPath(null)}}" alt="Avatar">
                            </a>
                        @endif
                    </div>
                    <div class="col-lg-4 text-truncate">
                        <a href="{{route('profile',['username'=>$referral->usedBy->username])}}" class="text-dark-r">
                            <b>{{$referral->usedBy->name}}</b>
                        </a>
                    </div>
                    <div class="col-lg-4 d-none d-md-block">
                        {{__('Since')}}: {{ \Carbon\Carbon::parse($referral->created_at)->format('Y-m-d') }}
                    </div>
                    <div class="col-lg-4 text-truncate">
                        {{__('Earned')}}:<b>{{config('app.site.currency_symbol')}}{{\App\Providers\UsersServiceProvider::getTotalAmountEarnedFromRewardsByUsers(\Illuminate\Support\Facades\Auth::user()->id, $referral->used_by)}}</b>
                    </div>
                </div>
            @endforeach
            <div class="d-flex flex-row-reverse mt-3 mr-4">
                {{ $referrals->links() }}
            </div>
        @else
            <div class="p-3 text-center">
                <p>{{__('There are no referrals to show.')}}</p>
            </div>
        @endif
    </div>
</div>


