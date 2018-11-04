<?php

function categoryDescent($category, $toplevel)
{
    echo '<li class="list-group-item" id="' . $category->id . '"><div>';

    if ($category->id != 1)
        echo '<span class="badge pull-right"><span class="glyphicon glyphicon-remove dynamic-tree-remove"></span></span>';

    if ($toplevel)
        echo '<span class="badge pull-left"><span class="glyphicon expanding-icon dynamic-tree-expand"></span></span>';

    echo '<input type="text" class="form-control" value="' . $category->name . '" required></div><ul>';

    foreach($category->children as $c)
        echo categoryDescent($c, false);

    echo '</ul></li>';
}

?>

<form class="form-horizontal dynamic-tree-box" method="PUT" action="{{ url('categories/0') }}">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">{{ _i('Modifica Categorie') }}</h4>
    </div>

    <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
                <p>
                    {{ _i("Clicca e trascina le categorie nell'elenco per ordinarle gerarchicamente.") }}
                </p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div id="categories-editor">
                    <ul class="list-group dynamic-tree">
                        @foreach($categories as $cat)
                            <?php categoryDescent($cat, true) ?>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="form-group dynamic-tree-add-row">
            <div class="col-md-10">
                <input type="text" class="form-control" name="new_category" placeholder="{{ _i('Crea Nuova Categoria') }}" autocomplete="off">
            </div>
            <div class="col-md-2">
                <button class="pull-right btn btn-default dynamic-tree-add">{{ _i('Crea') }}</button>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">{{ _i('Annulla') }}</button>
        <button type="submit" class="btn btn-success">{{ _i('Salva') }}</button>
    </div>
</form>
