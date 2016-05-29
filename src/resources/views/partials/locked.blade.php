<div class="{{$options['link']?"col-xs-11":""}}">
    <input type="text" name="{{$name}}" id="{{$name}}" class="row-border form-control" value="{{$value}}" placeholder="{{$title}}" title="{{$title}}" locked disabled/>
</div>
@if($options['link'])
<div class="col-xs-1">
    <a class="btn btn-sm btn-info link-to-relation" target="_blank" href="{{$options['link']}}"><i class="fa fa-external-link"></i></a>
</div>
@endif