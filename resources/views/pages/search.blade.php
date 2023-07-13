@extends('layouts.user-no-nav')
@section('page_title', __('Discover'))

@section('scripts')
    {!!
        Minify::javascript([
            '/js/PostsPaginator.js',
            '/js/UsersPaginator.js',
            '/js/StreamsPaginator.js',
            '/js/CommentsPaginator.js',
            '/js/Post.js',
            '/js/SuggestionsSlider.js',
            '/js/pages/lists.js',
            '/js/pages/checkout.js',
            '/libs/swiper/swiper-bundle.min.js',
            '/js/plugins/media/photoswipe.js',
            '/libs/photoswipe/dist/photoswipe-ui-default.min.js',
            '/libs/@joeattardi/emoji-button/dist/index.js',
            '/js/plugins/media/mediaswipe.js',
            '/js/plugins/media/mediaswipe-loader.js',
            '/js/pages/search.js',
         ])->withFullUrl()
    !!}
@stop

@section('styles')
    {!!
        Minify::stylesheet([
            '/libs/swiper/swiper-bundle.min.css',
            '/libs/photoswipe/dist/photoswipe.css',
            '/css/pages/checkout.css',
            '/libs/photoswipe/dist/default-skin/default-skin.css',
            '/css/pages/feed.css',
            '/css/posts/post.css',
            '/css/pages/search.css',
         ])->withFullUrl()
    !!}
@stop

@section('meta')
    <meta name="robots" content="noindex">
@stop

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12 col-sm-12 col-lg-8 col-md-7 second p-0">
                <div class="d-flex neutral-bg fixed-top-m px-3 py-3 feed-mobile-search">
                    <span class="h-pill h-pill-primary rounded search-back-button d-flex justify-content-center align-items-center" onClick="Search.goBack()">
                        @include('elements.icon',['icon'=>'arrow-back-outline','variant'=>'medium','centered'=>true])
                    </span>
                    <div class="col pl-2">
                        @include('elements.search-box')
                    </div>
                    @if($activeFilter == 'people')
                        <span class="h-pill h-pill-primary rounded search-back-button d-flex justify-content-center align-items-center" data-toggle="collapse" href="#colappsableFilters" role="button" aria-expanded="false" aria-controls="colappsableFilters">
                             @include('elements.icon',['icon'=>'filter-outline','variant'=>'medium','centered'=>true])
                        </span>
                    @endif
                </div>
                <div class="py-2 m-pt-70">
                    @if($activeFilter == 'people')
                        <div class="mobile-search-filter collapse {{$searchFilterExpanded ? 'show' : ''}}"  id="colappsableFilters">
                            @include('elements.search.search-filters')
                        </div>
                    @endif
                    <div class="inline-border-tabs mt-3">
                        <nav class="nav nav-pills nav-justified bookmarks-nav">
                            @foreach($availableFilters as $filter)
                                <a class="nav-item nav-link {{$filter == $activeFilter ? 'active' : ''}}" href="{{route('search.get',array_merge(['query'=>isset($searchTerm) && $searchTerm ? $searchTerm : ''],['filter'=>$filter]))}}">
                                    <div class="d-flex justify-content-center text-bold">
                                        <span class="d-md-none">
                                        @switch($filter)
                                                @case('live')
                                                @include('elements.icon',['icon'=>'play-outline','centered' => false,'variant'=>'medium'])
                                                @break
                                                @case('top')
                                                @include('elements.icon',['icon'=>'flame-outline','centered' => false,'variant'=>'medium'])
                                                @break
                                                @case('latest')
                                                @include('elements.icon',['icon'=>'time-outline','centered' => false,'variant'=>'medium'])
                                                @break
                                                @case('people')
                                                @include('elements.icon',['icon'=>'people-outline','centered' => false,'variant'=>'medium'])
                                                @break
                                                @case('photos')
                                                @include('elements.icon',['icon'=>'image-outline','centered' => false,'variant'=>'medium'])
                                                @break
                                                @case('videos')
                                                @include('elements.icon',['icon'=>'videocam-outline','centered' => false,'variant'=>'medium'])
                                                @break
                                            @endswitch
                                            </span>
                                             @if($filter == 'live') <div class="blob red d-none d-md-block"></div> @endif
                                        <span class="d-none d-md-block ml-2">{{ucfirst(trim( (in_array($filter,['videos','people']) ? trans_choice($filter,2,['number'=>'']) : __(ucfirst($filter))) )) }}</span>
                                    </div>
                                </a>
                            @endforeach
                        </nav>
                    </div>
                </div>

                @include('elements.message-alert',['classes'=>'p-2'])

                @if(isset($posts))
                    @include('elements.feed.posts-load-more')
                    <div class="feed-box mt-0 pt-2 posts-wrapper">
                        @include('elements.feed.posts-wrapper',['posts'=>$posts])
                    </div>
                    @include('elements.feed.posts-loading-spinner')
                @endif

                @if(isset($users))
                    <div class="users-box mt-4 users-wrapper">
                        @include('elements.search.users-wrapper',['posts'=>$users])
                    </div>
                    @include('elements.feed.posts-loading-spinner')
                @endif

                @if(isset($streams))
                    <div class="streams-box mt-4 streams-wrapper">
                        @include('elements.search.streams-wrapper',['streams'=>$streams])
                    </div>
                    @include('elements.feed.posts-loading-spinner')
                @endif

            </div>
            <div class="col-12 col-sm-12 col-md-5 col-lg-4 first border-left order-0 pt-4 pb-5 min-vh-100 suggestions-wrapper d-none d-md-block">
                <div class="search-widgets">
                    @include('elements.feed.suggestions-box',['profiles'=>$suggestions,'isMobile' => false])
                    @if(getSetting('custom-code-ads.sidebar_ad_spot'))
                        <div class="mt-4">
                            {!! getSetting('custom-code-ads.sidebar_ad_spot') !!}
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @include('elements.checkout.checkout-box')
    </div>
@stop
