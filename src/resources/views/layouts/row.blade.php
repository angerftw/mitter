<div class="form-group row" {{$extraAttributes}}>
    <label for="{{$name}}" class="col-sm-3 control-label">{{$title}}</label>
    <div class="col-sm-9 row">
        <div class="col-sm-{{$width}}">
            {!! $element !!}
        </div>
    </div>
</div>@if(!$continuous)<hr/>@endif