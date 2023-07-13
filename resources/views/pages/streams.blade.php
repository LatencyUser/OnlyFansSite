@extends('layouts.user-no-nav')
@section('page_title', __('Your live streams'))

@section('styles')
    {!!
        Minify::stylesheet([
            '/libs/dropzone/dist/dropzone.css',
            '/css/pages/stream.css',
         ])->withFullUrl()
    !!}
@stop

@section('scripts')
    {!!
        Minify::javascript([
            '/libs/dropzone/dist/dropzone.js',
            '/js/FileUpload.js',
            '/js/pages/streams.js',
         ])->withFullUrl()
    !!}
@stop

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="pt-4 d-flex justify-content-between align-items-center px-3 pb-3 border-bottom">
                <div>
                    <h5 class="text-truncate text-bold mb-0 {{(Cookie::get('app_theme') == null ? (getSetting('site.default_user_theme') == 'dark' ? '' : 'text-dark-r') : (Cookie::get('app_theme') == 'dark' ? '' : 'text-dark-r'))}}">{{__('Streams')}}</h5>
                </div>
                <div class="d-flex">
                    <div class="stream-on-label w-100 {{StreamsHelper::getUserInProgressStream() ? '' : 'd-none'}}">
                        <button class="btn btn-outline-danger btn-sm px-3 mb-0 d-flex align-items-center">
                            <div class="mr-1">{{__("Streaming")}}</div>
                            <div><div class="blob red"></div></div>
                        </button>
                    </div>

                    <div class="stream-off-label w-100 {{StreamsHelper::getUserInProgressStream() ? 'd-none' : ''}}">
                        <button class="btn btn-outline-danger btn-sm px-3 mb-0 d-flex align-items-center {{!GenericHelper::isUserVerified() && getSetting('site.enforce_user_identity_checks') ? 'disabled' : '' }}" onclick="{{!GenericHelper::isUserVerified() && getSetting('site.enforce_user_identity_checks') ? '' : "Streams.showStreamEditDialog('create')" }}" data-toggle="{{!GenericHelper::isUserVerified() && getSetting('site.enforce_user_identity_checks') ? 'none' : 'tooltip' }}" data-placement="top" title="{{__('Go live')}}">
                            <div class="mr-1">{{__("New stream")}}</div>
                            <div> @include('elements.icon',['icon'=>'ellipse','variant'=>'','classes'=>'flex-shrink-0 text-danger'])</div>
                        </button>
                    </div>

                </div>
            </div>

            <div class="px-3 pt-3">
                @if(!GenericHelper::isUserVerified() && getSetting('site.enforce_user_identity_checks'))
                    <div class="alert alert-warning text-white font-weight-bold mt-2 mb-4" role="alert">
                        {{__("Before being able to start a new stream, you need to complete your")}} <a class="text-white" href="{{route('my.settings',['type'=>'verify'])}}">{{__("profile verification")}}</a>.
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                <div class="card py-3 px-3">
                    <p class="h6 text-bolder mb-2 text-bold-600">{{__("Active streams")}}</p>
                    <div class="lists-wrapper mt-{{StreamsHelper::getUserInProgressStream() ? '2' : '0'}} active-stream-container">
                        @if(StreamsHelper::getUserInProgressStream())
                            @include('elements.streams.stream-element',['stream'=>$activeStream, 'isLive' => true])
                        @else
                            <span>{{__("There are no active streams. Click the button above to start a new one.")}}</span>
                        @endif
                    </div>
                </div>
                <div class="card py-3 px-3 my-4">
                    <p class="h6 text-bolder mb-2 text-bold-600">{{__("Previous streams")}}</p>
                    @if($previousStreams->count())
                        <div class="lists-wrapper mt-2">
                            @foreach($previousStreams as $stream)
                                @include('elements.streams.stream-element',['stream'=>$stream, 'isLive' => false])
                            @endforeach
                            @if($previousStreams->total() > 6)
                                <div class="d-flex flex-row-reverse mt-3 mr-4">
                                    {{ $previousStreams->links() }}
                                </div>
                            @endif
                        </div>
                    @else
                        <span>{{__("There are no previous streams.")}}</span>
                    @endif
                </div>
            </div>
        </div>

    </div>
    @include('elements.streams.stream-create-update-dialog')
    @include('elements.streams.stream-stop-dialog')
    @include('elements.streams.stream-delete-dialog')
    @include('elements.streams.stream-details-dialog')
    @include('elements.dropzone-dummy-element')
@stop
