<div class='btn-group' data-test="{!! $htmlTag !!}-datatable-actions">
    <a href="{{ url('bots/'.$bot) }}" class='btn btn-warning btn-xs show-button' data-test="show-button">
        <i class="fa fa-smile-o"></i>
    </a>
    <a href="{{ route($link.'.show', $id) }}" class='btn btn-info btn-xs show-button' data-test="show-button">
        <i class="glyphicon glyphicon-eye-open"></i>
    </a>
    <a href="{{ url('/category/fromEmptyResponse/'.$id) }}" class='btn btn-primary btn-xs show-button' data-test="show-button">
        <i class="fa fa-plus"></i>
    </a>
    <a class='btn btn-danger btn-xs delete-button openDeleteDataTableModal' data-id="{!! $id !!}"  data-message="{!! $title !!} ID: {!! $id !!}" data-test="delete-button">
        <i class="glyphicon glyphicon-trash"></i>
    </a>
    {!! Form::hidden('rowId', $id, ['class'=>'rowId'] ) !!}
</div>
