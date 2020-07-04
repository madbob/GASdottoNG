@if($obj && $obj->getROShowURL() != null)
    <button type="button" class="btn btn-xs btn-default object-details" data-show-url="{{ $obj->getROShowURL() }}">
        <span class="glyphicon glyphicon-zoom-in" aria-hidden="true"></span>
    </button>
@endif
