<label class="checkbox-inline">
    <input type="hidden" name="{{$name}}" value="0">
    <input type="checkbox" name="{{$name}}" id="{{$name}}" {{$value or $options['default']?'checked="true"':''}} value="1" >
    <span class="custom-checkbox"></span>{{$title}}
</label>