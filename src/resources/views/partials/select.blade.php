<select name="{{$name}}" id="{{$name}}" class="form-control">
    @foreach ((array)@$options['options'] as $key => $val)
        <?php $selected = ''; ?>
        @if(isset($value['name']))
            @if($value['name'] == $val)
                <?php $selected = ' selected="selected" ' ?>
            @endif
        @elseif($value == $key)
            <?php $selected = ' selected="selected" ' ?>
        @endif
        <option value="{{$key}}" {{$selected}}>{{$val}}</option>
    @endforeach
</select>