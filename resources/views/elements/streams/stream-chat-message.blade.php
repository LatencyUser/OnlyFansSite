<div class="d-flex {{$streamOwnerId == Auth::user()->id ? '' : 'mb-1'}} stream-chat-message align-items-center {{isset($message) ? ' ' : 'stream-chat-message-template'}}" data-commentid="{{$message->id}}">
    <div class="chat-message-action mr-1 d-none p-1">
        <span class="h-pill h-pill-accent rounded " onClick="Stream.deleteComment({{$message->id}})">
            @include('elements.icon',['icon'=>'close-outline', 'variant' => 'xsmall'])
        </span>
    </div>
    <div>
        <span class="{{$streamOwnerId == $message->user->id ? 'text-success text-bold' : 'text-orange'}} chat-message-user"><a href="{{route('profile',['username'=>$message->user->username])}}">{{isset($message) ? $message->user->username : ''}}</a></span>
        <span class="mr-1">:</span>
        <span class="chat-message-content">{{isset($message) ? $message->message : ''}}</span>
    </div>
</div>
