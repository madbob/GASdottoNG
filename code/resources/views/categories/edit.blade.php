<?php

function descent($category)
{
    echo '<li class="list-group-item" id="' . $category->id . '"><div>';

    if ($category->id != 1)
        echo '<span class="badge pull-right"><span class="glyphicon glyphicon-remove dynamic-tree-remove"></span></span>';

    echo '<input type="text" class="form-control" value="' . $category->name . '"></div><ul>';

    foreach($category->children as $c)
        echo descent($c);

    echo '</ul></li>';
}

?>

<form class="form-horizontal dynamic-tree-box" method="PUT" action="{{ url('categories/0') }}">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Modifica Categorie</h4>
    </div>

    <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
                <p>
                    Clicca e trascina le categorie nell'elenco per ordinarle gerarchicamente.
                </p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div id="categories-editor">
                    <ul class="list-group dynamic-tree">
                        @foreach($categories as $cat)
                            <?php descent($cat) ?>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="form-group dynamic-tree-add-row">
            <div class="col-md-10">
                <input type="text" class="form-control" name="new_category" placeholder="Crea Nuova Categoria" autocomplete="off">
            </div>
            <div class="col-md-2">
                <button class="pull-right btn btn-default dynamic-tree-add">Crea</button>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
        <button type="submit" class="btn btn-success">Salva</button>
    </div>
</form>
