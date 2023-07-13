<?php

namespace App\Http\Controllers;

use App\Model\UserGender;
use App\Providers\MembersHelperServiceProvider;
use App\Providers\PostsHelperServiceProvider;
use App\Providers\StreamsServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use JavaScript;

class SearchController extends Controller
{

    /**
     * Available search categories
     * @var array
     */
    public $filters = [
        'live',
        'top',
        'latest',
        'people',
        'photos',
        'videos',
    ];

    public function __construct()
    {
        if(getSetting('streams.allow_streams')){
            unset($this->filters[2]);
        }
        else{
            unset($this->filters[0]);
        }
    }

    /**
     * Main search page method
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request){

        // Avoid (browser) page caching when hitting back button
        header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
        header('Pragma: no-cache'); // HTTP 1.0.
        header('Expires: 0 '); // Proxies.

        $jsData = $viewData = [];
        $filters = $this->processFilterParams($request);

        // Redirecting to default people filter if user is not logged in buet selected custom filter
        if(!Auth::check() && $filters['postsFilter'] && $filters['postsFilter'] != 'people') {
            return redirect(route('search.get'));
        }

        // If no filter is selected & user not logged in, default UI to people searcg
        if(!$filters['postsFilter'] && !Auth::check()){
            $filters['postsFilter'] = 'people';
        }

        if(!Auth::check()){
            $this->filters = ['people'];
        }

        /**
         * People custom filter
         */
        if($filters['postsFilter'] == 'people'){

            $searchFilters = [
                'gender' => $request->get('gender'),
                'min_age' => $request->get('min_age'),
                'max_age' => $request->get('max_age'),
                'location' => $request->get('location'),
            ];
            $users = MembersHelperServiceProvider::getSearchUsers(array_merge(['searchTerm' => $filters['searchTerm']],$searchFilters));
            $jsData = [
                'paginatorConfig' => [
                    'next_page_url' => str_replace('/search', '/search/users', $users->nextPageUrl()),
                    'prev_page_url' => str_replace('/search', '/search/users', $users->previousPageUrl()),
                    'current_page' => $users->currentPage(),
                    'total' => $users->total(),
                    'per_page' => $users->perPage(),
                    'hasMore' => $users->hasMorePages(),
                ],
                'searchType' => 'people'
            ];
            if(
                $searchFilters['gender'] ||
                $searchFilters['min_age'] ||
                $searchFilters['max_age'] ||
                $searchFilters['location']
            ){
                $searchFilterExpanded = true;
            }
            else{
                $searchFilterExpanded = false;
            }
            $viewData = [
                'users' => $users,
                'genders' => UserGender::all(),
                'searchFilters' => $searchFilters,
                'searchFilterExpanded' => $searchFilterExpanded
            ];

        }
        /**
         * Live streams custom filter
         */
        elseif($filters['postsFilter'] == 'live') {

            $streams = StreamsServiceProvider::getPublicStreams(['searchTerm' => $filters['searchTerm'], 'status' => 'live']);
            $jsData = [
                'paginatorConfig' => [
                    'next_page_url' => str_replace('/search', '/search/streams', $streams->nextPageUrl()),
                    'prev_page_url' => str_replace('/search', '/search/streams', $streams->previousPageUrl()),
                    'current_page' => $streams->currentPage(),
                    'total' => $streams->total(),
                    'per_page' => $streams->perPage(),
                    'hasMore' => $streams->hasMorePages(),
                ],
                'searchType' => 'streams'
            ];
            $viewData = [
                'streams' => $streams,
                'searchFilterExpanded' => false
            ];
        }
        /**
         * Standard posts filters
         */
        else{
            $startPage = PostsHelperServiceProvider::getFeedStartPage(PostsHelperServiceProvider::getPrevPage($request));
            $posts = PostsHelperServiceProvider::getFeedPosts(Auth::user()->id, false, $startPage, $filters['mediaType'], $filters['sortOrder'], $filters['searchTerm']);
            PostsHelperServiceProvider::shouldDeletePaginationCookie($request);
            $jsData = [
                'paginatorConfig' => [
                    'next_page_url' => str_replace('/search', '/search/posts', $posts->nextPageUrl()),
                    'prev_page_url' => str_replace('/search', '/search/posts', $posts->previousPageUrl()),
                    'current_page' => $posts->currentPage(),
                    'total' => $posts->total(),
                    'per_page' => $posts->perPage(),
                    'hasMore' => $posts->hasMorePages(),
                ],
                'initialPostIDs' => $posts->pluck('id')->toArray(),
                'searchType' => 'feed'
            ];
            $viewData = ['posts' => $posts];
        }
        JavaScript::put(
            array_merge($jsData,
                [
                    'sliderConfig' => [
                        'autoslide'=> getSetting('feed.feed_suggestions_autoplay') ? true : false,
                    ]
                ]
            )
        );

        return view('pages.search',
            array_merge($viewData,[
                'searchTerm' => $filters['searchTerm'],
                'suggestions' => MembersHelperServiceProvider::getSuggestedMembers(),
                'availableFilters' => $this->filters,
                'activeFilter' => $filters['postsFilter'],
            ])
        );


    }

    /**
     * Fetches AJAX paginated (feed search) content
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSearchPosts(Request $request)
    {
        $filters = $this->processFilterParams($request);
        return response()->json(['success'=>true, 'data'=>PostsHelperServiceProvider::getFeedPosts(Auth::user()->id, true, false, $filters['mediaType'], $filters['sortOrder'], $filters['searchTerm'])]);
    }

    /**
     * Fetches AJAX paginated (users search) content
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUsersSearch(Request $request)
    {
        $filters = $this->processFilterParams($request);
        return response()->json(['success'=>true, 'data'=> MembersHelperServiceProvider::getSearchUsers(array_merge(
            ['encodePostsToHtml'=>true,'searchTerm' => $filters['searchTerm']],
            [
                'gender' => $request->get('gender'),
                'min_age' => $request->get('min_age'),
                'max_age' => $request->get('max_age'),
                'location' => $request->get('location'),
            ]
        ))]);
    }

    /**
     * Gets paginated (public) streams
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStreamsSearch(Request $request)
    {
        $filters = $this->processFilterParams($request);
        return response()->json(['success'=>true, 'data'=> StreamsServiceProvider::getPublicStreams(['searchTerm' => $filters['searchTerm'],'encodePostsToHtml'=>true, 'status' => 'live'])]);
    }

    /**
     * Filters out incoming search filters
     *
     * @param $request
     * @return array
     */
    protected function processFilterParams($request){
        $searchTerm = $request->get('query') ? $request->get('query') : false;
        $postsFilter = $request->get('filter') ? $request->get('filter') : false;

        $mediaType = 'image';
        if($postsFilter == 'videos'){
            $mediaType = 'video';
        }
        if($postsFilter == 'photos'){
            $mediaType = 'image';
        }
        $sortOrder = '';
        if($postsFilter == 'top'){
            $mediaType = false;
            $sortOrder = 'top';
        }
        if($postsFilter == 'latest'){
            $mediaType = false;
            $sortOrder = 'latest';
        }
        if($postsFilter == 'live') {
            $mediaType = false;
            $sortOrder = 'latest';
        }

        return [
            'searchTerm' => $searchTerm,
            'postsFilter' => $postsFilter,
            'mediaType' => $mediaType,
            'sortOrder' => $sortOrder
        ];

    }

}
