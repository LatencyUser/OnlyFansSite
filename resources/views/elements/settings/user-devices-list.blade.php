@foreach($devices as $device)
    @if( ($type == 'verified' && $device->verified_at) || ($type == 'unverified' && !$device->verified_at) )
        <span class="list-link d-flex flex-column pt-2 pb-2 pl-3 rounded pointer-cursor">
                                    <div class="d-flex flex-row-no-rtl justify-content-between">
                                        <div>
                                            <h6 class="mb-1 d-flex align-items-center">
                                                <span data-toggle="tooltip" data-placement="top" title="{{__($device->device_type)}}">
                                                @switch($device->device_type)
                                                        @case('Desktop')
                                                        @include('elements.icon',['icon'=>'desktop-outline','classes'=>'mr-2'])
                                                        @break
                                                        @case('Mobile')
                                                        @include('elements.icon',['icon'=>'phone-portrait-outline','classes'=>'mr-2'])
                                                        @break
                                                        @case('Tablet')
                                                        @include('elements.icon',['icon'=>'tablet-portrait-outline','classes'=>'mr-2'])
                                                        @break
                                                    @endswitch
                                                </span>
                                                {{$device->browser}} {{__("on")}} {{$device->platform}}</h6>
                                            <small class="text-muted">{{__("Created at")}}: {{$device->created_at}}</small>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center pr-3">
                                            <span class="h-pill h-pill-accent rounded" onclick="PrivacySettings.showDeviceDeleteConfirmation('{{$device->signature}}')">
                                                @include('elements.icon',['icon'=>'close-outline'])
                                            </span>
                                        </div>
                                    </div>
                                </span>
    @endif
@endforeach
