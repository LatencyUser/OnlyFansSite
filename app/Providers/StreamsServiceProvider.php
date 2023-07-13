<?php

namespace App\Providers;

use App\Model\Stream;
use App\Model\Transaction;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Str;
use View;

class StreamsServiceProvider extends ServiceProvider
{
    const PUSHR_API_ENDPOINT = 'https://www.pushrcdn.com/api/v3/streams';

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Function that checks if user has paid for a stream
     * @param $userId
     * @param $streamId
     * @return bool
     */
    public static function userPaidForStream($userId, $streamId){
        return Transaction::query()->where(
                [
                    'stream_id' => $streamId,
                    'sender_user_id' => $userId,
                    'type' => Transaction::STREAM_ACCESS,
                    'status' => Transaction::APPROVED_STATUS
                ]
            )->first() != null;
    }

    /**
     * Get user's in progress stream if available
     * @param bool $addDurationTag
     * @param null $userId
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public static function getUserInProgressStream($addDurationTag = true, $userId = null){
        if($userId == null){
            $userId = Auth::user()->id;
        }
        $stream = Stream::query()
            ->where(['user_id' => $userId, 'status' => Stream::IN_PROGRESS_STATUS])
            ->first();
        if($stream && $addDurationTag){
            $stream->duration = $stream->created_at->diffInMinutes($stream->ended_at);
            $stream->duration = $stream->duration === 0 ? 1 : $stream->duration;
            $stream->created_at_short = $stream->created_at->format('d M y ');
        }
        return $stream;
    }

    /**
     * Get all streams for the logged in user
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getUserStreams(){
        $streams =  Stream::query()
            ->where(['user_id' => Auth::user()->id, 'status' => Stream::ENDED_STATUS])
            ->orderBy('created_at','DESC')
            ->paginate(6)
        ;
        $streams->getCollection()->transform(function ($stream) {
            $stream->duration = $stream->created_at->diffInMinutes($stream->ended_at);
            $stream->duration = $stream->duration === 0 ? 1 : $stream->duration;
            $stream->created_at_short = $stream->created_at->format('d M y ');
            return $stream;
        });
        return $streams;
    }

    /**
     * Initiate or get an existing streaming by user
     * @param $user
     * @param $name
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function initiateStreamingByUser($options)
    {
        try{
            // check if this user don't already have an initiated or in progress streaming
            $stream = self::getUserInProgressStream();
            if (!$stream) {
                $settings = [
                    'encoder' => getSetting('streams.pushr_encoder'),
                    'dvr' => (int)getSetting('streams.allow_dvr'),
                    'mux' => (int)getSetting('streams.allow_mux'),
                    '360p' => (int)getSetting('streams.allow_360p'),
                    '480p' => (int)getSetting('streams.allow_480p'),
                    '576p' => (int)getSetting('streams.allow_576p'),
                    '720p' => (int)getSetting('streams.allow_720p'),
                    '1080p' => (int)getSetting('streams.allow_1080p'),
                ];
                $pushrStreaming = self::createPushrStreaming(['name'=>$options['name'],'settings' => $settings]);
                if ($pushrStreaming && isset($pushrStreaming['status'])
                    && $pushrStreaming['status'] === 'success'
                    && isset($pushrStreaming['rtmp_key'])
                    && isset($pushrStreaming['rtmp_server'])
                    && isset($pushrStreaming['hls_link'])
                    && isset($pushrStreaming['player_link'])
                    && isset($pushrStreaming['id'])
                ) {
                    $stream = Stream::create([
                        'user_id' => Auth::user()->id,
                        'status' => Stream::IN_PROGRESS_STATUS,
                        'name' => $options['name'],
                        'poster' => $options['poster'],
                        'slug' => Str::slug($options['name']),
                        'price' => $options['price'],
                        'requires_subscription' => $options['requires_subscription'] == 'true' ? 1 : 0,
                        'is_public' => $options['is_public'] == 'true' ? 1 : 0,
                        'pushr_id' => $pushrStreaming['id'],
                        'rtmp_key' => $pushrStreaming['rtmp_key'],
                        'rtmp_server' => $pushrStreaming['rtmp_server'],
                        'hls_link' => $pushrStreaming['hls_link'],
                        'settings' => $settings
                    ]);
                }
            }
            else{
                return ['success' => false, 'message' => __('You can only have one active stream at a time.')];
            }
            return ['success' => true, 'data' => $stream];
        }
        catch (\Exception $exception){
            return ['success' => false, 'message' => $exception->getMessage()];
        }
    }

    /**
     * Creates the stream on pushr network
     * @param $streaming
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function createPushrStreaming($options)
    {
        $httpClient = new Client();
        $createStreamingRequest = $httpClient->request('POST', self::PUSHR_API_ENDPOINT.'/stream', [
                'headers' => [
                    'Accept' => 'application/json',
                    'APIKEY' => getSetting('streams.pushr_key'),
                ],
                'form_params' => array_merge([
                    'action' => 'create',
                    'zone' => getSetting('streams.pushr_zone_id'),
                    'name' => $options['name'],
                ],$options['settings'])
            ]
        );
        return json_decode($createStreamingRequest->getBody(), true);
    }

    /**
     * Fetch pushr streaming details by id
     * @param $id
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function getPushrStreamingDetails($id)
    {
        $httpClient = new Client();
        $createStreamingRequest = $httpClient->request('GET', self::PUSHR_API_ENDPOINT.'/details?id='.$id, [
                'headers' => [
                    'Accept' => 'application/json',
                    'APIKEY' => getSetting('streams.pushr_key'),
                ]
            ]
        );
        return json_decode($createStreamingRequest->getBody(), true);
    }

    public static function getPushrStreamingDvr($id)
    {
        $httpClient = new Client();
        $createStreamingRequest = $httpClient->request('GET', self::PUSHR_API_ENDPOINT.'/dvr?id='.$id, [
                'http_errors' => false,
                'headers' => [
                    'Accept' => 'application/json',
                    'APIKEY' => getSetting('streams.pushr_key'),
                ]
            ]
        );
        if ($createStreamingRequest->getStatusCode() == 200) {
            return json_decode($createStreamingRequest->getBody(), true);
        }
        return false;
    }

    /**
     * Destroy pushr streaming by id
     * @param $id
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function destroyPushrStream($id)
    {
        $httpClient = new Client();
        $createStreamingRequest = $httpClient->request('POST', self::PUSHR_API_ENDPOINT.'/destroy', [
                'headers' => [
                    'Accept' => 'application/json',
                    'APIKEY' => getSetting('streams.pushr_key'),
                ],
                'form_params' => [
                    'id' => $id
                ]
            ]
        );
        return json_decode($createStreamingRequest->getBody(), true);
    }

    /**
     * Gets all available public streams
     * @param $options
     * @return array
     */
    public static function getPublicStreams($options){
        $streams = Stream::where('is_public',1);
        if(isset($options['status'])) {
            if ($options['status'] == 'live') {
                $streams->where('status', Stream::IN_PROGRESS_STATUS);
            } elseif ($options['status'] == 'ended') {
                $streams->where('status', Stream::ENDED_STATUS);
            } else {
                $streams->whereIn('status', [Stream::ENDED_STATUS, Stream::IN_PROGRESS_STATUS]);
            }
        }

        if(isset($options['userId'])){
            $streams->where('user_id', $options['userId']);
        }

        if(isset($options['searchTerm'])){
            $streams->where('name', 'like', '%'.$options['searchTerm'].'%');
        }

        $showUsername = true;
        if(isset($options['showUsername']) && $options['showUsername'] == false) $showUsername = false;

        $streams->orderBy('created_at','DESC');
        if (isset($options['pageNumber'])) {
            $streams = $streams->paginate(9, ['*'], 'page', $options['pageNumber'])->appends(request()->query());
        } else {
            $streams = $streams->paginate(9)->appends(request()->query());
        }

        if(!isset($options['encodePostsToHtml'])){
            $options['encodePostsToHtml'] = false;
        }
        if ($options['encodePostsToHtml']) {
            // Posts encoded as JSON
            $data = [
                'total' => $streams->total(),
                'currentPage' => $streams->currentPage(),
                'last_page' => $streams->lastPage(),
                'prev_page_url' => $streams->previousPageUrl(),
                'next_page_url' => $streams->nextPageUrl(),
                'first_page_url' => $streams->nextPageUrl(),
                'hasMore' => $streams->hasMorePages(),
            ];
            $postsData = $streams->map(function ($stream) use ( $data, $options, $showUsername ) {
                $stream->setAttribute('postPage',$data['currentPage']);
                $stream = ['id' => $stream->id, 'html' => View::make('elements.streams.stream-element-public')->with('stream', $stream)->with('showLiveIndicators',false)->with('showUsername', $showUsername)->render()];
                return $stream;
            });
            $data['users'] = $postsData;
        } else {
            // Collection data posts | To be rendered on the server side
            $postsCurrentPage = $streams->currentPage();
            $streams->map(function ($user) use ($postsCurrentPage) {
                $user->setAttribute('postPage',$postsCurrentPage);
                return $user;
            });
            $data = $streams;
        }
        return $data;
    }

    /**
     * Gets # of public live streams to show the menu pill indicator
     * @return mixed
     */
    public static function getPublicLiveStreamsCount(){
        $streams = Stream::where('is_public',1)->where('status',Stream::IN_PROGRESS_STATUS)->count();
        return $streams;
    }

}
