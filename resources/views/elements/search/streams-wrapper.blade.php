@if(count($streams))
    @foreach($streams as $stream)
        @include('elements.streams.stream-element-public',[
                'stream'=>$stream,
                'showLiveIndicators' => isset($showLiveIndicators) && $showLiveIndicators ? true : false,
                'showUsername' => isset($showUsername) && $showUsername == false ? false : true,
                ])
    @endforeach
@else
    <h5 class="text-center mb-2 mt-2">{{__('No streams were found')}}</h5>
@endif
