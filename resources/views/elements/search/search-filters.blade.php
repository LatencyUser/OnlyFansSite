<form action="{{ route('search.get')}}" class="search-filters-form w-100" method="GET">

    <div class="card rounded-lg mb-4">
        <div class="card-body">
            <div class="form-group">
                <label for="gender">{{__('Gender')}}</label>
                <select class="form-control" id="gender" name="gender" >
                    <option value="all">{{__("All")}}</option>
                    @foreach($genders as $gender)
                        <option value="{{$gender->gender_name}}" {{$gender->gender_name == $searchFilters['gender'] ? 'selected' : ''}}>{{__($gender->gender_name)}}</option>
                    @endforeach
                </select>
                @if($errors->has('gender'))
                    <span class="invalid-feedback" role="alert">
                    <strong>{{$errors->first('gender')}}</strong>
                </span>
                @endif
            </div>

            <div class="d-flex flex-row w-100">
                <div class="w-50 pr-2">
                    <div class="form-group">
                        <label for="min_age">{{__('Min age')}}</label>
                        <input class="form-control {{ $errors->has('min_age') ? 'is-invalid' : '' }}" id="min_age" name="min_age" placeholder="18" type="number" min="18" value="{{$searchFilters['min_age']}}">
                        @if($errors->has('min_age'))
                            <span class="invalid-feedback" role="alert">
                            <strong>{{$errors->first('min_age')}}</strong>
                        </span>
                        @endif
                    </div>
                </div>
                <div class="w-50 pl-2">
                    <div class="form-group">
                        <label for="max_age">{{__('Max age')}}</label>
                        <input class="form-control {{ $errors->has('max_age') ? 'is-invalid' : '' }}" id="max_age" name="max_age" type="number" min="18" value="{{$searchFilters['max_age']}}">
                        @if($errors->has('max_age'))
                            <span class="invalid-feedback" role="alert">
                            <strong>{{$errors->first('max_age')}}</strong>
                        </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="location">{{__('Location')}}</label>
                <input class="form-control {{ $errors->has('location') ? 'is-invalid' : '' }}" id="location" name="location" value="{{$searchFilters['location']}}">
                @if($errors->has('location'))
                    <span class="invalid-feedback" role="alert">
                    <strong>{{$errors->first('location')}}</strong>
                </span>
                @endif
            </div>

            <input type="hidden" name="query" value="{{isset($searchTerm) && $searchTerm ? $searchTerm : ''}}" />
            <input type="hidden" name="filter" value="{{isset($activeFilter) && $activeFilter !== false ? $activeFilter : 'top'}}" />

        </div>
    </div>
</form>
