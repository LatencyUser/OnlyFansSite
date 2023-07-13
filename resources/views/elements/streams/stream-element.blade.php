<div class="list-item">
     <span class="list-link d-flex flex-column pt-2 pb-2 pl-3 rounded">
         <div class="d-flex flex-row-no-rtl justify-content-between">
             <div class="text-truncate overflow-hidden">

                 <div class="d-flex align-items-center" >
                    <div class="mr-2">
                         <img src="{{$stream->poster}}" class="rounded stream-cover {{$isLive ? 'active-stream-poster' : ''}}"/>
                     </div>
                     <div class="d-flex flex-column overflow-hidden text-truncate mr-3">
                         <h6 class="mb-1 d-flex pr-2 {{$isLive ? 'active-stream-name' : ''}} text-truncate">
                             <span class="text-truncate">
                                 @if($isLive)
                                     <a href="{{route('public.stream.get',['streamID'=>$stream->id,'slug'=>$stream->slug])}}" class="text-{{(Cookie::get('app_theme') == null ? (getSetting('site.default_user_theme') == 'dark' ? 'white' : 'dark') : (Cookie::get('app_theme') == 'dark' ? 'white' : 'dark'))}}">{{$stream->name}}</a>
                                 @else
                                     @if($stream->settings['dvr'] && $stream->vod_link)
                                         <a href="{{route('public.vod.get',['streamID'=>$stream->id,'slug'=>$stream->slug])}}" class="text-{{(Cookie::get('app_theme') == null ? (getSetting('site.default_user_theme') == 'dark' ? 'white' : 'dark') : (Cookie::get('app_theme') == 'dark' ? 'white' : 'dark'))}}">{{$stream->name}}</a>
                                     @else
                                         {{$stream->name}}
                                     @endif
                                 @endif
                             </span>
                         </h6>
                         <small class="text-muted">{{__("Created at")}}: {{$stream->created_at_short}}  @if(!$isLive)• {{__('Length')}}: {{$stream->duration}} {{trans_choice('minute',$stream->duration)}}.@endif</small>
                     </div>
                 </div>

             </div>
             <div class="d-flex justify-content-between align-items-center pr-3">

                 @if($isLive)
                     <span class="h-pill h-pill-accent rounded mr-2 show-stream-details-label" onclick="Streams.showStreamDetailsDialog({{$stream->id}},'{{$stream->rtmp_server}}','{{$stream->rtmp_key}}')">
                     @include('elements.icon',['icon'=>'server-outline'])
                 </span>
                 @endif

                 @if($isLive)
                     <span class="h-pill h-pill-accent rounded mr-2" onclick="Streams.showStreamEditDialog('edit',{{$stream->id}})">
                     @include('elements.icon',['icon'=>'create-outline'])
                 </span>
                 @endif

                 @if($isLive)
                     <a class="h-pill h-pill-accent rounded mr-2" href="{{route('public.stream.get',['streamID'=>$stream->id,'slug'=>$stream->slug])}}">
                          @include('elements.icon',['icon'=>'eye-outline'])
                      </a>
                 @else
                     @if(($stream->status == 'ended' && $stream->settings['dvr'] && $stream->vod_link))
                         <a class="h-pill h-pill-accent rounded mr-2" href="{{route('public.vod.get',['streamID'=>$stream->id,'slug'=>$stream->slug])}}">
                                  @include('elements.icon',['icon'=>'eye-outline'])
                              </a>
                     @else
                         <span class="h-pill h-pill-accent rounded mr-2" data-toggle="tooltip" data-placement="top" title="{{__('Stream VOD unavailable')}}">
                          @include('elements.icon',['icon'=>'eye-off-outline'])
                      </span>
                     @endif
                 @endif

                 @if($isLive)
                     <span class="h-pill h-pill-accent rounded" onclick="Streams.showStreamStopDialog({{$stream->id}})">
                          @include('elements.icon',['icon'=>'stop-circle-outline'])
                      </span>
                 @else
                     <span class="h-pill h-pill-accent rounded" onclick="Streams.showStreamDeleteDialog({{$stream->id}})">
                     @include('elements.icon',['icon'=>'close-outline'])
                 </span>
                 @endif

             </div>
         </div>
     </span>
</div>
