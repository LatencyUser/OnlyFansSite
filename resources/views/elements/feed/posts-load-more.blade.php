@if(Cookie::get('app_feed_prev_page') && PostsHelper::isComingFromPostPage(request()->session()->get('_previous')))
    <div class="px-2 mt-3 reverse-paginate-btn pt-1 pt-md-2 pt-md-0 mb-0 mb-md-4">
        <button class="btn btn-outline-primary btn-block {{isset($classes) ?? $classes}}" onclick="PostsPaginator.loadPreviousResults()">
            {{__('Load previous posts')}}
        </button>
    </div>
@endif
