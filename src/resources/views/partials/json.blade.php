<input type="hidden" name="{{$name}}"/>
@foreach($value as $key => $data)
    <div class="col-sm-{{$options['col']}}" data-groupkey="{{$key}}">
        <div class="panel box box-primary" data-group="{{$title}}">
            <div class="box-header with-border">
                <h4 class="box-title">
                    <a data-toggle="collapse" href="#{{$key}}">{{$title}}</a>
                </h4>
            </div>
            <div id="{{$key}}" class="panel-collapse collapse in">
                <div class="box-body">
                    @if(@$options["manualKey"])
                        <input type="text" name="{{"$name"."[$key][arraykey]"}}" id="{{$name}}" class="row-border form-control" value="{{@$options['new']?'':$key}}" placeholder="{{$title}} Key"/>
                    @endif
                    @if(isset($options["fields"]))
                        @foreach ($options["fields"] as $fieldName => $fieldTitle)
                            <?php $fieldValue = isset($data[$fieldName]) ? $data[$fieldName] : "" ?>
                            <input type="text" name="{{"$name"."[$key][$fieldName]"}}" id="{{$name}}" class="row-border form-control" value="{{$fieldValue}}" placeholder="{{$title}} Key" {{$fieldTitle}}/>
                        @endforeach
                    @else
                        <input type="text" name="{{"$name"."[$key][arrayvalue]"}}" id="{{$name}}" class="row-border form-control" value="{{$data}}" placeholder="{{$title}} Value"/>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endforeach