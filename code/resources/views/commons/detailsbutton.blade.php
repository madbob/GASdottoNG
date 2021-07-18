@if($obj && $obj->getROShowURL() != null)
    <button type="button" class="btn btn-xs btn-info object-details d-none d-md-inline-block" data-show-url="{{ $obj->getROShowURL() }}">
        <i class="bi-zoom-in"></i>
    </button>
@endif
