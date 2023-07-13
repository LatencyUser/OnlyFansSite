<?php

namespace App\Http\Controllers;

use App\Events\NewStreamChatMessage;
use App\Http\Requests\SaveNewStreamRequest;
use App\Model\Stream;
use App\Model\StreamMessage;
use App\Providers\AttachmentServiceProvider;
use App\Providers\GenericHelperServiceProvider;
use App\Providers\PostsHelperServiceProvider;
use App\Providers\StreamsServiceProvider;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use JavaScript;
use Pusher\Pusher;
use Ramsey\Uuid\Uuid;
use View;

class StreamsController extends Controller
{

    /**
     * Streams management endpoint
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request){
        $action = false;
        if(!getSetting('streams.allow_streams')){
            abort(404);
        }
        if($request->get('action')){
            $action = $request->get('action');
        }
        $currentStream = StreamsServiceProvider::getUserInProgressStream();
        JavaScript::put([
            'openCreateDialog' => $action == 'create' ? true : false,
            'openEditDialog' => $action == 'edit' ? true : false,
            'openDetailsDialog' => $action == 'details' ? true : false,
            'hasActiveStream' => $currentStream ? true : false,
            'inProgressStreamCover' => $currentStream && asset($currentStream->poster) !== asset('/img/live-stream-cover.svg') ? $currentStream->getOriginal('poster') : '',
            'mediaSettings' => [
                'allowed_file_extensions' => '.'.str_replace(',', ',.', AttachmentServiceProvider::filterExtensions('imagesOnly')),
                'max_file_upload_size' => (int) getSetting('media.max_file_upload_size'),
                'manual_payments_file_extensions' => '.'.str_replace(',', ',.', AttachmentServiceProvider::filterExtensions('manualPayments')),
                'manual_payments_excel_icon' => asset('/img/excel-preview.svg'),
                'manual_payments_pdf_icon' => asset('/img/pdf-preview.svg'),
            ],
        ]);
        return view('pages.streams',[
            'activeStream' => StreamsServiceProvider::getUserInProgressStream(),
            'previousStreams' => StreamsServiceProvider::getUserStreams(),

        ]);
    }

    /**
     * Stream actual page
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getStream(Request $request){
        $streamID = $request->route('streamID');
        $streamSlug = $request->route('slug');
        $stream = Stream::where('id',$streamID)->where('slug',$streamSlug)->where('status',Stream::IN_PROGRESS_STATUS)->first();
        if(!$stream){
            abort(404);
        }
        // TODO: Move this onto their own method
        // Access checks
        $stream->setAttribute('canWatchStream', true);
        if($stream->requires_subscription && !PostsHelperServiceProvider::hasActiveSub(Auth::user()->id, $stream->user->id)){
            $stream->setAttribute('canWatchStream', false);
            $data['subLocked'] = true;
        }
        if($stream->price > 0 && !StreamsServiceProvider::userPaidForStream(Auth::user()->id, $stream->id)){
            $stream->setAttribute('canWatchStream', false);
            $data['priceLocked'] = true;
        }
        if(Auth::user()->id == $stream->user->id){
            $stream->setAttribute('canWatchStream', true);
        }
        JavaScript::put([
            'streamVars' => [
                'canWatchStream' => $stream->canWatchStream,
                'streamId' => $stream->id,
                'pusherDebug' => (bool) env('APP_DEBUG'),
                'pusherCluster' => config('broadcasting.connections.pusher.options.cluster'),
                'streamOwnerId' => $stream->user_id,
                'streamPoster' => $stream->poster
            ],
        ]);
        $data['stream'] = $stream;
        return view('pages.stream', $data);
    }

    /**
     * Vod page rendering endpoint
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getVod(Request $request){
        $streamID = $request->route('streamID');
        $streamSlug = $request->route('slug');

        $stream = Stream::where('id',$streamID)->where('slug',$streamSlug)->where('status',Stream::ENDED_STATUS)->first();
        if(!$stream){
            abort(404);
        }

        // TODO: Move this onto their own method
        // Access checks
        $stream->setAttribute('canWatchStream', true);
        if($stream->requires_subscription && !PostsHelperServiceProvider::hasActiveSub(Auth::user()->id, $stream->user->id)){
            $stream->setAttribute('canWatchStream', false);
            $data['subLocked'] = true;
        }
        if($stream->price > 0 && !StreamsServiceProvider::userPaidForStream(Auth::user()->id, $stream->id)){
            $stream->setAttribute('canWatchStream', false);
            $data['priceLocked'] = true;
        }
        if(Auth::user()->id == $stream->user->id){
            $stream->setAttribute('canWatchStream', true);
        }

        JavaScript::put([
            'streamVars' => [
                'canWatchStream' => $stream->canWatchStream,
                'streamId' => $stream->id,
                'pusherDebug' => (bool) env('APP_DEBUG'),
                'pusherCluster' => config('broadcasting.connections.pusher.options.cluster'),
                'streamOwnerId' => $stream->user_id
            ],
        ]);

        $data['stream'] = $stream;
        $data['streamEnded'] = true;
        return view('pages.stream', $data);
    }

    /**
     * Initiate live streaming by creator
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function initStream(SaveNewStreamRequest $request)
    {
        $streamName = $request->get('name');
        $requires_subscription = $request->get('requires_subscription');
        $is_public = $request->get('is_public');
        $price = $request->get('price');
        $poster = $request->get('poster');

        if(!GenericHelperServiceProvider::isUserVerified() && getSetting('site.enforce_user_identity_checks')){
            return response()->json([
                'success' => false,
                'message' => __('Please confirm your ID first.')
            ]);
        }

        $streaming = StreamsServiceProvider::initiateStreamingByUser(['name' => $streamName, 'requires_subscription' => $requires_subscription, 'is_public' => $is_public, 'price' => $price, 'poster' => $poster]);
        if($streaming['success']){
            $responseData = [
                'success' => true,
                'data' => $streaming['data'],
                'html' => View::make('elements.streams.stream-element')->with('stream', $streaming['data'])->with('isLive', true)->render()
            ];
        }
        else{
            $responseData = [
                'success' => false,
                'message' => $streaming['message'],
            ];
        }
        return response()->json($responseData);
    }

    /**
     * (Re)saves stream details when updating
     * @param SaveNewStreamRequest $request
     * @return array
     */
    public function saveStreamDetails(SaveNewStreamRequest $request){
        try{
            $stream =  Stream::query()
                ->where([
                    'user_id' => Auth::user()->id,
                    'status' => Stream::IN_PROGRESS_STATUS,
                    'id' => $request->get('id'),
                ])
                ->first();

            // Deleting old poster
            if($stream->poster && $stream->poster !== $request->get('poster')){
                $storage = Storage::disk(config('filesystems.defaultFilesystemDriver'));
                $storage->delete($stream->poster);
            }

            $stream->update([
                'name' => $request->get('name'),
                'price' => $request->get('price'),
                'requires_subscription' => $request->get('requires_subscription') == 'true' ? 1 : 0,
                'is_public' => $request->get('is_public') == 'true' ? 1 : 0,
                'poster' => $request->get('poster')
            ]);
            return ['success' => true, 'message' => __('Stream updated successfully.'), 'data' => ['poster' => $stream->poster]];

        }
        catch (\Exception $exception){
            return ['success' => false, 'message' => $exception->getMessage()];
        }
    }

    /**
     * Stream end endpoint
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function stopStream(Request $request){
        try {
            $stream = StreamsServiceProvider::getUserInProgressStream(false);
            if ($stream) {
                if($stream->settings['dvr']){
                    $dvrDetails = StreamsServiceProvider::getPushrStreamingDvr($stream->pushr_id);
                    if($dvrDetails){
                        $stream->vod_link = $dvrDetails[$stream->pushr_id][0]['dvr_url'];
                    }
                }
                StreamsServiceProvider::destroyPushrStream($stream->pushr_id);
                $stream->ended_at = Carbon::now();
                $stream->status = Stream::ENDED_STATUS;
                $stream->save();
            }
            else{
                return response()->json([
                    'success' => false,
                    'message' => __('No active streams available.')
                ]);
            }
            return response()->json([
                'success' => true,
                'message' => __('The stream has been queued to be stopped.')
            ]);
        }
        catch (\Exception $exception){
            return response()->json(['success' => false, 'message' => $exception->getMessage()]);
        }
    }

    /**
     * Stream delete endpoint
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteStream(Request $request){
        try {
            $streamId = $request->get('id');
            $stream = Stream::where('user_id',Auth::user()->id)->where('status',Stream::ENDED_STATUS)->where('id',$streamId)->withCount('streamPurchases')->first();

            if(getSetting('compliance.disable_creators_ppv_delete')){
                if($stream->stream_purchases_count > 0){
                    return response()->json(['success' => false, 'message' => __('The stream has been bought and can not be deleted.')]);
                }
            }

            if ($stream) {
                $stream->status = Stream::DELETED_STATUS;
                $stream->save();
            }
            else{
                return response()->json([
                    'success' => false,
                    'message' => __('Stream could not be found.')
                ]);
            }
            return response()->json([
                'success' => true,
                'message' => __('The stream has been deleted successfully.')
            ]);
        }
        catch (\Exception $exception){
            return response()->json(['success' => false, 'errors' => [$exception->getMessage()]]);
        }
    }

    /**
     * Pusher init method for stream live counter
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     * @throws \Pusher\PusherException
     */
    public function authorizeUser(Request $request)
    {
        $envVars['PUSHER_APP_KEY'] = config('broadcasting.connections.pusher.key');
        $envVars['PUSHER_APP_SECRET'] = config('broadcasting.connections.pusher.secret');
        $envVars['PUSHER_APP_ID'] = config('broadcasting.connections.pusher.app_id');
        $envVars['PUSHER_APP_CLUSTER'] = config('broadcasting.connections.pusher.options.cluster');
        $pusher = new Pusher(
            $envVars['PUSHER_APP_KEY'],
            $envVars['PUSHER_APP_SECRET'],
            $envVars['PUSHER_APP_ID'],
            [
                'cluster' => $envVars['PUSHER_APP_CLUSTER'],
                'encrypted' => true,
            ]
        );

        try {
            $output = [];
            foreach ($request->get('channel_name') as $channelName) {
                $auth = $pusher->presence_auth(
                    $channelName,
                    $request->input('socket_id'),
                    Auth::user()->id,
                    []
                );
                $output[$channelName] = ['status'=>200, 'data'=>json_decode($auth)];
            }
            return $output;
        } catch (\Exception $exception) {
            return response()->json([
                'code' => '403',
                'data' => [
                    'errors' => [__($exception->getMessage())],
                ], ]);
        }
    }

    /**
     * Method that adds comments to stream chats
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addComment(Request $request){
        $message = $request->get('message');
        $streamId = $request->get('streamId');
        $stream = Stream::where('id',$streamId)->where('status',Stream::IN_PROGRESS_STATUS)->first();

        if(!$stream){
            return response()->json(['success' => false, 'message' => __('Invalid stream')],500);
        }

        // Access checks
        $canWatchStream =  true;
        if($stream->requires_subscription && !PostsHelperServiceProvider::hasActiveSub(Auth::user()->id, $stream->user->id)){
            $canWatchStream =  false;
        }
        if($stream->price > 0 && !StreamsServiceProvider::userPaidForStream(Auth::user()->id, $stream->id)){
            $canWatchStream =  false;
        }
        if(Auth::user()->id == $stream->user->id){
            $canWatchStream =  true;
        }

        if(!$canWatchStream){
            return response()->json(['success' => false, 'message' => __('Stream access denied')],500);
        }

        try {
            $message = StreamMessage::create([
                'message' => $message,
                'stream_id' => $streamId,
                'user_id' => Auth::user()->id
            ]);

            $renderedMessage = View::make('elements.streams.stream-chat-message')->with('message', $message)->with('streamOwnerId',$stream->user_id)->render();

            // Broadcast the message
            broadcast(new NewStreamChatMessage($streamId, $renderedMessage, Auth::user()->id))->toOthers();

            return response()->json([
                'status'=>'success',
                'data'=> $message,
                'dataHtml' => $renderedMessage
            ]);

        } catch (\Exception $exception) {
            return response()->json(['success' => false, 'message' => $exception->getMessage()],500);
        }
    }

    /**
     * Method used for deleting stream messages
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteComment(Request $request){
        $commentId = $request->get('id');
        $comment = StreamMessage::where('id',$commentId)->with(['stream'])->first();
        if(!$comment){
            return response()->json(['success' => false, 'message' => __('Invalid stream')],500);
        }
        if($comment->stream->user_id !== Auth::user()->id){
            return response()->json(['success' => false, 'message' => __('Access denied')],500);
        }
        try {
            $comment->delete();
            return response()->json([
                'status'=>'success',
            ]);
        } catch (\Exception $exception) {
            return response()->json(['success' => false, 'message' => $exception->getMessage()],500);
        }
    }

    /**
     * Method used for uploading stream posters
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function posterUpload(Request $request){
        $file = $request->file('file');
        try {
            $directory = 'streams/posters';
            $s3 = Storage::disk(config('filesystems.defaultFilesystemDriver'));
            $fileId = Uuid::uuid4()->getHex();
            $filePath = $directory.'/'.$fileId.'.'.$file->guessClientExtension();
            $img = Image::make($file);
            $coverWidth = 1920;
            $coverHeight = 960;
            $img->fit($coverWidth, $coverHeight)->orientate();
            $data = ['poster' => $filePath];
            // Resizing the asset
            $img->encode('jpg', 100);
            // Saving to disk
            $s3->put($filePath, $img, 'public');
        } catch (\Exception $exception) {
            return response()->json(['success' => false, 'errors' => ['file'=>$exception->getMessage()]]);
        }
        return response()->json(['success' => true, 'assetSrc' => asset(Storage::url($filePath)), 'assetPath' => $filePath]);
    }

}
