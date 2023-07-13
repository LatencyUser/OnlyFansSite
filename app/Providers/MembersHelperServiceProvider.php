<?php

namespace App\Providers;

use App\Model\FeaturedUser;
use App\Model\UserGender;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use View;

class MembersHelperServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Small method that tries to fetch up a list of the most popular profiles across the platform
     * If there isn't a big enough number to choose from, it fallbacks to latest profiles.
     *
     * @param bool $encodeToHtml
     * @param array $filters
     * @return mixed
     */
    public static function getSuggestedMembers($encodeToHtml = false, $filters = [])
    {

        $skipEmptyProfiles = getSetting('feed.suggestions_skip_empty_profiles') ? true : false;
        $skipUnverifiedProfiles = getSetting('feed.suggestions_skip_unverified_profiles') ? true : false;

        if(getSetting('feed.suggestions_use_featured_users_list')){
            $userLists = FeaturedUser::get()->pluck('user_id')->toArray();
            $members = User::limit(getSetting('feed.feed_suggestions_total_cards') * getSetting('feed.feed_suggestions_card_per_page'))->where('public_profile', 1)->whereIn('id',$userLists);
        }
        else{
            // Get top 32 list of most subbed users
            $mostSubbedMax = (int) getSetting('feed.feed_suggestions_total_cards') * 3;
            $topSubbedUsers = DB::select("
            SELECT usersTable.id, COUNT(subsTable.id ) AS subs_count FROM users usersTable
            INNER JOIN subscriptions subsTable ON usersTable.id = subsTable.recipient_user_id
            ".($skipUnverifiedProfiles ? 'INNER JOIN user_verifies verifications ON usersTable.id = verifications.user_id AND verifications.status = \'verified\'' : '')."
            WHERE usersTable.role_id = 2
            ".($skipEmptyProfiles ? 'AND usersTable.avatar IS NOT NULL AND usersTable.cover IS NOT NULL' : '')."
            GROUP BY usersTable.id
            ORDER BY subs_count DESC
            LIMIT 0,{$mostSubbedMax}
        ");
            $topSubbedUsers = array_map(function ($v) {
                return $v->id;
            }, $topSubbedUsers);

            $members = User::limit(getSetting('feed.feed_suggestions_total_cards') * getSetting('feed.feed_suggestions_card_per_page'))->where('public_profile', 1);

            // If there are more than 9 users having subs, use those
            // Otherwise, grab latest 9 users by date
            if (count($topSubbedUsers) >= 6) {
                $members->whereIn('id', $topSubbedUsers);
            } else {
                $members->where('role_id',2);
                $members->orderByDesc('users.created_at');
                if(Auth::check()){
                    $members->where('users.id', '<>', Auth::user()->id);
                }
                if($skipEmptyProfiles){
                    $members->where('avatar', '<>', null);
                    $members->where('cover', '<>', null);
                }

                if($skipUnverifiedProfiles){
                    $members->join('user_verifies', function ($join) {
                        $join->on('users.id', '=', 'user_verifies.user_id');
                        $join->on('user_verifies.status', '=', DB::raw("'verified'"));
                    });
                }

            }
        }



        // Filtering free/paid accounts
        if (isset($filters['free'])) {
            $members->where('paid_profile', 0);
        }
        $members = $members->get();

        // Shuffle the list each time for more randomness
        $members = $members->shuffle();
        // Return either raw data to the views or json encoded, rendered views
        if ($encodeToHtml) {
            $viewData = View::make('elements.feed.suggestions-wrapper')->with('profiles', $members);
            if(isset($filters['isMobile'])){
                $viewData->with('isMobile',true);
            }
            $membersData['html'] = $viewData->render();
            return $membersData;
        } else {
            return $members;
        }
    }

    /**
     * Returns a list of latest profiles.
     * @param $limit
     * @return mixed
     */
    public static function getFeaturedMembers($limit)
    {
        $members = FeaturedUser::with(['user'])->orderByDesc('featured_users.created_at')->limit($limit);
        $members->join('users', function ($join) {
            $join->on('users.id', '=', 'featured_users.user_id');
        });
        $members = $members->get()->map(function ($v){
            return $v->user;
        });
        if(count($members)){
            return $members;
        }
        else{
            $members = User::limit(3)->where('public_profile', 1)->whereIn('role_id',[1,2])->orderByDesc('created_at')->get();
            return $members;
        }
    }


    /**
     * Returns list of filtered users
     *
     * @param $options TODO:: Rename to filters
     * @return array
     */
    public static function getSearchUsers($options){

        $users = User::where('public_profile',1);
        $users->where('role_id',2);

        if(Auth::check()){
            $users->where('id', '<>', Auth::user()->id);
        }

        if(isset($options['gender']) && $options['gender'] !== 'all'){
            $genderID = UserGender::where('gender_name',strtolower($options['gender']))->select('id')->first();
            if(isset($genderID->id)){
                $users->where('gender_id',$genderID->id);
            }
        }

        if(isset($options['min_age'])){
            $minDate = Carbon::now()->subYear($options['min_age']);
            $users->where('birthdate', '<', $minDate->format('Y-m-d'));
        }

        if(isset($options['max_age'])){
            $maxDate = Carbon::now()->subYear($options['max_age']);
            $users->where('birthdate', '>', $maxDate->format('Y-m-d'));
        }

        if(isset($options['location'])){
            $users->where('location', 'like', '%' . $options['location'] . '%');
        }

        if(isset($options['searchTerm'])){
            // Might take a small hit on performance
            $users->where(function($query) use ($options) {
                $query->where('username', 'like', '%'.$options['searchTerm'].'%');
                $query->orWhere('bio', 'like', '%' . $options['searchTerm'] . '%');
                $query->orWhere('name', 'like', '%' . $options['searchTerm'] . '%');
            });
        }

        $users->orderBy('id', 'DESC');

        if (isset($options['pageNumber'])) {
            $users = $users->paginate(9, ['*'], 'page', $options['pageNumber'])->appends(request()->query());
        } else {
            $users = $users->paginate(9)->appends(request()->query());
        }

        if(!isset($options['encodePostsToHtml'])){
            $options['encodePostsToHtml'] = false;
        }

        if ($options['encodePostsToHtml']) {
            // Posts encoded as JSON
            $data = [
                'total' => $users->total(),
                'currentPage' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'prev_page_url' => $users->previousPageUrl(),
                'next_page_url' => $users->nextPageUrl(),
                'first_page_url' => $users->nextPageUrl(),
                'hasMore' => $users->hasMorePages(),
            ];
            $postsData = $users->map(function ($user) use ( $data) {
                $user->setAttribute('postPage',$data['currentPage']);
                $user = ['id' => $user->id, 'html' => View::make('elements.search.users-list-element')->with('user', $user)->render()];
                return $user;
            });
            $data['users'] = $postsData;
        } else {
            // Collection data posts | To be rendered on the server side
            $postsCurrentPage = $users->currentPage();
            $users->map(function ($user) use ($postsCurrentPage) {
                $user->setAttribute('postPage',$postsCurrentPage);
                return $user;
            });
            $data = $users;
        }

        return $data;
    }

}
