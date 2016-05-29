<div class="col-sm-{{$value?10:12}}">
    <input type="file" name="{{$name}}" id="{{$name}}" class="row-border form-control" placeholder="{{$title}}">
</div>
@if($value)
    <div class="col-sm-1 btn" data-toggle="modal" data-target="#{{$name}}-modal">
        <img class="row-border" width="100%" src="{{$value}}" alt="{{$name}}"/>
    </div>
    <div class="col-sm-1 btn-group" data-toggle="buttons">
        <label class="btn btn-danger fa fa-remove" title="remove">
            <input type="checkbox" name="{{$name}}[remove]" id="{{$name}}[remove]" autocomplete="off">
        </label>
    </div>
    <div class="modal fade" id="{{$name}}-modal" tabindex="-1" role="dialog" aria-labelledby="{{$name}}-modal-lable" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="{{$name}}-modal-lable">{{$title}}</h4>
                </div>
                <div class="modal-body">
                    <img class="form-horizontal row-border" width="100%" src="{{$value}}" alt="{{$name}}"/>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endif