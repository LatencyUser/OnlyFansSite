@extends('layouts.user-no-nav')
@section('page_title', $stream->name)


@section('styles')
    <link rel="stylesheet" href="{{asset('/libs/video.js/dist/video-js.min.css')}}">
    <link rel="stylesheet" href="{{asset('/css/player-theme.css')}}">
    {!!
        Minify::stylesheet([
            '/libs/dropzone/dist/dropzone.css',
            '/css/pages/checkout.css',
            '/css/pages/stream.css',
         ])->withFullUrl()
    !!}
@stop

@section('scripts')
    <script type="text/javascript" src="{{asset('/libs/video.js/dist/video.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('/libs/videojs-contrib-quality-levels/dist/videojs-contrib-quality-levels.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('/libs/videojs-http-source-selector/dist/videojs-http-source-selector.min.js')}}"></script>
    {!!
        Minify::javascript([
            '/libs/dropzone/dist/dropzone.js',
            '/js/FileUpload.js',
            '/js/pages/stream.js',
            '/libs/videojs-contrib-quality-levels/dist/videojs-contrib-quality-levels.min.js',
            '/libs/videojs-http-source-selector/dist/videojs-http-source-selector.min.js',
            '/libs/pusher-js-auth/lib/pusher-auth.js',
            '/js/pages/checkout.js',
         ])->withFullUrl()
    !!}
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="pt-4 d-flex justify-content-between align-items-center px-3 pb-3 border-bottom">
                <h5 class="text-truncate text-bold mb-0 {{(Cookie::get('app_theme') == null ? (getSetting('site.default_user_theme') == 'dark' ? '' : 'text-dark-r') : (Cookie::get('app_theme') == 'dark' ? '' : 'text-dark-r'))}}">{{$stream->name}}</h5>
                @if(!isset($streamEnded))
                    @if(StreamsHelper::getUserInProgressStream())
                        <button class="btn btn-outline-danger btn-sm px-3 mb-0 d-flex align-items-center" onclick="Streams.showStreamEditDialog('create')">
                            <div class="mr-1">{{__("Streaming")}}</div>
                            <div><div class="blob red"></div></div>
                        </button>
                    @endif
                @endif
            </div>
            <div class="px-3 pt-3">

                <div class="stream-details mb-4 d-flex justify-content-between overflow-hidden">
                    <div class="mr-4 overflow-hidden">

                        <div class="d-flex flex-row my-1">
                            <div class="d-flex justify-content-center">
                                <img class="rounded-circle avatar" src="{{$stream->user->avatar}}" alt="{{$stream->user->username}}">
                            </div>
                            <div class="pl-3 w-100 d-flex align-items-center">
                                <div>
                                    <div class="d-flex flex-column overflow-hidden">
                                        <h5 class="text-truncate">
                                            {!! __(":user's stream",['user'=>"<a href=\"".route('profile',['username'=>$stream->user->username])."\" class=\"text-".(Cookie::get('app_theme') == null ? (getSetting('site.default_user_theme') == 'dark' ? 'white' : 'dark') : (Cookie::get('app_theme') == 'dark' ? 'white' : 'dark'))."\">".$stream->user->name."</a>"]) !!}
                                        </h5>
                                    </div>
                                    @if(!isset($streamEnded))
                                        <span class="text-muted"><span class="live-stream-users-count">0</span> {{__("Watching")}} • {{__("Started streaming")}} {{$stream->created_at->diffForHumans(null,false,true)}}.</span>
                                    @else
                                        {{__('Stream ended :time time ago',['time'=>$stream->ended_at->diffForHumans(null,false,true)])}}
                                    @endif
                                </div>

                            </div>
                        </div>

                    </div>
                    @if(!isset($streamEnded))
                        <div class="d-flex align-items-center">
                            @if(isset($subLocked) && $stream->user->id !== Auth::user()->id)
                                <div class="d-none d-sm-block">
                                <span class="p-pill ml-2 pointer-cursor to-tooltip stream-subscribe-button"
                                      @if(!\App\Providers\GenericHelperServiceProvider::creatorCanEarnMoney($stream->user))
                                          data-placement="top"
                                          title="{{__('This creator cannot earn money yet')}}"
                                      @else
                                          data-toggle="modal"
                                          data-target="#checkout-center"
                                          data-type="one-month-subscription"
                                          data-recipient-id="{{$stream->user->id}}"
                                          data-amount="{{$stream->user->profile_access_price}}"
                                          data-first-name="{{Auth::user()->first_name}}"
                                          data-last-name="{{Auth::user()->last_name}}"
                                          data-billing-address="{{Auth::user()->billing_address}}"
                                          data-country="{{Auth::user()->country}}"
                                          data-city="{{Auth::user()->city}}"
                                          data-state="{{Auth::user()->state}}"
                                          data-postcode="{{Auth::user()->postcode}}"
                                          data-available-credit="{{Auth::user()->wallet->total}}"
                                          data-username="{{$stream->user->username}}"
                                          data-name="{{$stream->user->name}}"
                                          data-avatar="{{$stream->user->avatar}}"
                                          data-stream-id="{{$stream->id}}"
                                      @endif
                                >
                                 @include('elements.icon',['icon'=>'person-add-outline'])
                                </span>
                                </div>
                            @endif

                            @if(isset($priceLocked) && $stream->user->id !== Auth::user()->id)
                                <div class="d-none d-sm-block">
                                <span class="p-pill ml-2 pointer-cursor to-tooltip stream-unlock-button"
                                      @if(!GenericHelper::creatorCanEarnMoney($stream->user))
                                          data-placement="top"
                                          title="{{__('This creator cannot earn money yet')}}"
                                      @else
                                          data-toggle="modal"
                                      		data-target="#checkout-center"
                                      		data-type="stream-access"
                                      		data-recipient-id="{{$stream->user->id ? $stream->user->id : ''}}"
                                      		data-amount="{{$stream->price}}"
                                      		data-first-name="{{Auth::user()->first_name}}"
                                      		data-last-name="{{Auth::user()->last_name}}"
                                      		data-billing-address="{{Auth::user()->billing_address}}"
                                      		data-country="{{Auth::user()->country}}"
                                      		data-city="{{Auth::user()->city}}"
                                      		data-state="{{Auth::user()->state}}"
                                      		data-postcode="{{Auth::user()->postcode}}"
                                      		data-available-credit="{{Auth::user()->wallet->total}}"
                                      		data-username="{{$stream->user->username}}"
                                      		data-name="{{$stream->user->name}}"
                                      		data-avatar="{{$stream->user->avatar}}"
                                      		data-stream-id="{{$stream->id}}"
                                      @endif
                                >
                                 @include('elements.icon',['icon'=>'lock-open-outline'])
                                </span>
                                </div>
                            @endif

                            @if($stream->canWatchStream && $stream->user->id !== Auth::user()->id)
                                <div class="">
                                <span class="p-pill ml-2 pointer-cursor to-tooltip"
                                      @if(!GenericHelper::creatorCanEarnMoney($stream->user))
                                            data-placement="top"
                                            title="{{__('This creator cannot earn money yet')}}"
                                      @else
                                      		data-placement="top"
                                      		title="{{__('Send a tip')}}"
                                      		data-toggle="modal"
                                      		data-target="#checkout-center"
                                      		data-type="tip"
                                      		data-first-name="{{Auth::user()->first_name}}"
                                      		data-last-name="{{Auth::user()->last_name}}"
                                      		data-billing-address="{{Auth::user()->billing_address}}"
                                      		data-country="{{Auth::user()->country}}"
                                      		data-city="{{Auth::user()->city}}"
                                      		data-state="{{Auth::user()->state}}"
                                      		data-postcode="{{Auth::user()->postcode}}"
                                      		data-available-credit="{{Auth::user()->wallet->total}}"
                                      		data-username="{{$stream->user->username}}"
                                      		data-name="{{$stream->user->name}}"
                                      		data-avatar="{{$stream->user->avatar}}"
                                      		data-recipient-id="{{$stream->user->id}}"
                                      		data-stream-id="{{$stream->id}}"
                                    @endif
                                >
                                 @include('elements.icon',['icon'=>'cash-outline'])
                                </span>
                                </div>
                            @endif

                            @if($stream->user->id === Auth::user()->id)
                                <div class="d-none d-sm-block">
                                    <a class="p-pill ml-2 pointer-cursor to-tooltip" href="{{route('my.streams.get')}}?action=details">
                                        @include('elements.icon',['icon'=>'server-outline'])
                                    </a>
                                </div>
                                <div class="d-none d-sm-block">
                                    <a class="p-pill ml-2 pointer-cursor to-tooltip" href="{{route('my.streams.get')}}?action=edit">
                                        @include('elements.icon',['icon'=>'create-outline'])
                                    </a>
                                </div>
                            @endif

                        </div>
                    @endif
                </div>

                <div class="stream-wrapper row">
                    <div class="stream-video col-12">
                        @if($stream->canWatchStream)
                            <video id="my_video_1" class="video-js vjs-fluid vjs-theme-forest" controls preload="auto" autoplay muted>
                                <source src="{{isset($streamEnded) ? 'https://'.$stream->vod_link : $stream->hls_link}}" type="application/x-mpegURL">
                            </video>
                        @else
                            <div class="row d-flex justify-content-center align-items-center">
                                <div class="col-12">
                                    <div class="card p-5">
                                        <div class="p-4 p-md-5">
                                            <img src="{{asset('/img/live-stream-locked.svg')}}" class="stream-locked">
                                        </div>
                                        <div class="d-flex align-items-center justify-content-center" style="">
                                            <span>🔒 {{__("Live stream requires a")}} @if(isset($subLocked)) {{__("valid")}}
                                                <a href="javascript:void(0);" class="stream-subscribe-label to-tooltip"
                                                   @if(!GenericHelper::creatorCanEarnMoney($stream->user))
                                                    data-placement="top"
                                                    title="{{__('This creator cannot earn money yet')}}"
                                                   @endif
                                                >{{__("user subscription")}}</a>@endif
                                                @if(isset($priceLocked))
                                                    {{__("and an")}} <a href="javascript:void(0);" class="stream-unlock-label to-tooltip"
                                                    @if(!GenericHelper::creatorCanEarnMoney($stream->user))
                                                        data-placement="top"
                                                        title="{{__('This creator cannot earn money yet')}}"
                                                    @endif
                                                    >{{__("one time fee")}}</a>
                                                @endif.
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="stream-chat mb-4 {{$stream->canWatchStream ? '' : 'mt-3'}}">
                    @include('elements.message-alert',['classes'=>'py-3'])
                    <div class="card pb-3" >
                        @if($stream->canWatchStream)
                            <div class="chat-content conversations-wrapper overflow-hidden pt-4 pb-1 px-3 flex-fill">
                                <div class="conversation-content pt-1 pb-1 px-3 flex-fill">
                                    @if($stream->messages->count())
                                        @foreach($stream->messages as $message)
                                            @include('elements.streams.stream-chat-message',['message'=>$message, 'streamOwnerId' => $stream->user_id])
                                        @endforeach
                                    @endif
                                    <div class="d-{{$stream->messages->count() ? 'none' : 'flex'}} h-100 align-items-center justify-content-center no-chat-comments-label">
                                        @if($stream->status == 'in-progress')
                                            <div class="d-flex"><span>👋 {{__('There are no messages yet.')}} </span><span class="d-none d-md-block d-lg-block d-xl-block">&nbsp;{{__("Say 'Hi!' to someone!")}}</span></div>
                                        @else
                                            <div class="d-flex"><span>⏲ {{__("Stream ended, can't add comments.")}} </span></div>
                                        @endif

                                    </div>
                                </div>
                            </div>

                            @if(!isset($streamEnded))
                                <div class="conversation-writeup pt-1 pb-1 d-flex align-items-center mb-1">
                                    <form class="message-form w-100 pl-3">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="hidden" name="receiverID" id="receiverID" value="">
                                        <textarea name="message" class="form-control messageBoxInput" placeholder="{{__('Write a message..')}}" onkeyup="textAreaAdjust(this)"></textarea>
                                    </form>
                                    <div class="messenger-buttons-wrapper d-flex">
                                        <button class="btn btn-outline-primary btn-rounded-icon messenger-button send-message ml-3 mr-4" onClick="Stream.sendMessage({{$stream->id}})">
                                            <div class="d-flex justify-content-center align-items-center">
                                                @include('elements.icon',['icon'=>'paper-plane','variant'=>''])
                                            </div>
                                        </button>
                                    </div>
                                </div>
                            @endif

                        @else
                            <div class="d-flex align-items-center justify-content-center mt-4 stream-chat-no-message"><span>🔒 {{__("Chat locked. Unlock the stream to see the messages.")}}</span></div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    @include('elements.checkout.checkout-box')


@stop
