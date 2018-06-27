<?php

if (!isset($active) || $active == null)
    $active = $tabs[0]->id;

?>

<ul class="nav nav-tabs" role="tablist">
    @foreach($tabs as $tab)
        @if(!isset($tab->enabled) || $tab->enabled)
            <li role="presentation" class="{{ $tab->id == $active ? 'active' : '' }}"><a href="#{{ $tab->id }}" role="tab" data-toggle="tab">{{ $tab->label }}</a></li>
        @endif
    @endforeach
</ul>
