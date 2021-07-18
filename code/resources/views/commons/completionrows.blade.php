<ul
    class="list-group completion-rows"
    data-completion-source="{{ $source }}"
    data-callback-add="{{ $add_callback }}"
    data-callback-remove="{{ $remove_callback }}"

    @if(isset($extras))
        @foreach($extras as $k => $v)
            data-{{ $k }}="{{ $v }}"
        @endforeach
    @endif
>

    @foreach($objects as $obj)
        <li class="list-group-item" data-object-id="{{ $obj->id }}">
            {{ $obj->printableName() }}

            <div class="btn btn-xs btn-danger float-end">
                <i class="bi-x-lg"></i>
            </div>
        </li>
    @endforeach

    <li class="list-group-item">
        <input type="text" class="form-control" placeholder="{{ $adding_label }}">
    </li>
</ul>
