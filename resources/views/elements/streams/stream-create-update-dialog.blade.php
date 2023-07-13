<div class="modal fade" tabindex="-1" role="dialog" id="stream-update-dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><span class="create-label d-none">{{__('Start a new stream')}}</span> <span class="edit-label d-none">{{__('Edit stream details')}}</span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="{{__('Close')}}">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="username">{{__('Stream name')}}</label>
                    <input class="form-control" id="stream-name" name="stream-name" aria-describedby="name" value="{{$activeStream ? $activeStream->name : ''}}">
                    <span class="invalid-feedback" role="alert">
                            <strong></strong>
                        </span>
                </div>

                <div class="form-group">
                    <label for="username">{{__('Access price')}}</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="amount-label">@include('elements.icon',['icon'=>'cash-outline','variant'=>'medium'])</span>
                        </div>
                        <input class="form-control" id="stream-access_price" name="access_price" aria-describedby="access_price" value="{{$activeStream ? $activeStream->price : ''}}"  type="number">
                    </div>
                    @if($errors->has('access_price'))
                        <span class="invalid-feedback" role="alert">
                                <strong>{{$errors->first('access_price')}}</strong>
                            </span>
                    @endif
                </div>

                <div class="form-group">
                    <label for="username">{{__('Stream poster')}}</label>
                    <div class="card profile-cover-bg" style="background-image: url('{{$activeStream && $activeStream->poster ? $activeStream->poster : asset('/img/live-stream-cover.svg')}}');">
                        <div class="card-img-overlay d-flex justify-content-center align-items-center">
                            <div class="actions-holder d-none">
                                <div class="d-flex">
                                    <span class="h-pill h-pill-accent pointer-cursor mr-1 upload-button" data-toggle="tooltip" data-placement="top" title="{{__('Upload stream poster')}}">
                                         @include('elements.icon',['icon'=>'image','variant'=>'medium'])
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="requires_subscription" name="requires_subscription" {{$activeStream && $activeStream->requires_subscription ? 'checked' : ''}}>
                    <label class="custom-control-label" for="requires_subscription">{{__("Requires a subscription")}}</label>
                </div>

                <div class="custom-control custom-switch mt-1">
                    <input type="checkbox" class="custom-control-input" id="is_public" name="is_public" {{$activeStream ? ( $activeStream->is_public ? 'checked' : '') : 'checked'}}>
                    <label class="custom-control-label" for="is_public">{{__("Is public stream")}}</label>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary stream-save-btn" onclick="Streams.updateStream();">{{__('Save')}}</button>
            </div>
        </div>
    </div>
</div>
