<div class="modal fade" tabindex="-1" role="dialog" id="stream-details-dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{__('How to stream')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="{{__('Close')}}">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <p>{{__('Your stream server is online. In order to get going, follow the steps bellow:')}}</p>

                <ol>
                    <li>{{__('Download')}} <a href="https://obsproject.com/download" target="_blank">OBS</a> {{__('for desktop or mobile alternatives.')}}</li>
                    <li>{{__('Go to')}} <code>Settings > Stream</code> .</li>
                    <li>{{__('For')}} <code>Service</code>, {{__('select')}} <code>Custom</code>, {{__('then for the')}} <code>Server & Stream key</code>, {{__('use the values below.')}}</li>
                </ol>

                    <div class="form-group row">
                        <label for="colFormLabelSm" class="col-sm-3 col-form-label col-form-label-md">{{__('Stream url')}}</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control form-control-md" id="stream-url" placeholder="{{__('Stream url')}}">
                        </div>
                        <div class="col-sm-auto d-flex align-items-center justify-content-center">
                            <span class="h-pill h-pill-accent rounded mr-2" onclick="Streams.copyStreamData('url')">
                                @include('elements.icon',['icon'=>'copy-outline'])
                            </span>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="colFormLabelSm" class="col-sm-3 col-form-label col-form-label-md">{{__('Stream key')}}</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control form-control-md" id="stream-key" placeholder="{{__('Stream key')}}">
                        </div>
                        <div class="col-sm-auto d-flex align-items-center justify-content-center">
                            <span class="h-pill h-pill-accent rounded mr-2" onClick="Streams.copyStreamData('key');">
                                @include('elements.icon',['icon'=>'copy-outline'])
                            </span>
                        </div>
                    </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">{{__('Got it')}}</button>
            </div>
        </div>
    </div>
</div>
