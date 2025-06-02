<?php

function categoryDescent($category, $toplevel)
{
    echo '<li class="list-group-item" id="' . $category->id . '"><div>';

    if ($category->id != 1) {
        echo '<div class="btn btn-danger float-end dynamic-tree-remove"><i class="bi-x-lg"></i></div>';
    }

    echo '<input type="text" class="form-control" value="' . $category->name . '" required></div>';

    if ($toplevel) {
        echo '<ul>';

        foreach($category->children as $c) {
            categoryDescent($c, false);
        }

        echo '</ul>';
    }

    echo '</li>';
}

?>

<x-larastrap::modal classes="close-on-submit">
    <x-larastrap::form classes="dynamic-tree-box" method="PUT" :action="url('categories/0')">
        <div class="row">
            <div class="col">
                <p>{{ __('generic.help.categories_instructions') }}</p>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <div id="categories-editor">
                    <ul class="list-group dynamic-tree">
                        @foreach($categories as $cat)
                            <?php categoryDescent($cat, true) ?>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="row mt-2 form-group dynamic-tree-add-row">
            <div class="col-md-10">
                <x-larastrap::text name="new_category" squeeze tplaceholder="generic.create" />
            </div>
            <div class="col-md-2">
                <button class="float-end btn btn-warning dynamic-tree-add">{{ __('generic.create') }}</button>
            </div>
        </div>
    </x-larastrap::form>
</x-larastrap::modal>
