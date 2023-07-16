@if($obj && $obj->getROShowURL() != null)
    {{-- Questo deve essere coerente con la funzione detailsButton() in utils.js --}}
    <button type="button" class="btn btn-xs btn-icon btn-info object-details d-none d-xl-inline-block" data-show-url="{{ $obj->getROShowURL() }}">
        <i class="bi-zoom-in"></i>
    </button>
@endif
