<div>
    <x-larastrap::mform :obj="$group" classes="form-horizontal main-form group-editor" method="PUT" :action="route('groups.update', $group->id)">
        <div class="row">
            <div class="col-md-12">
                @include('groups.base-edit')

                <x-larastrap::radios tlabel="aggregations.context" name="context" :options="[
                    'user' => __('texts.user.name'),
                    'booking' => __('texts.aggregations.by_booking')
                ]" classes="selective-display" :attributes="['data-target' => '.optional']" tpophelp="aggregations.help.context" />

                <div class="optional" data-type="user">
                    <x-larastrap::radios tlabel="aggregations.cardinality" name="cardinality" :options="['single' => __('texts.aggregations.cardinality_one'), 'many' => __('texts.aggregations.cardinality_many')]" />
                    <x-larastrap::check tlabel="aggregations.user_selectable" name="user_selectable" />
                    <x-larastrap::check tlabel="aggregations.limit_access" name="filters_orders" tpophelp="aggregations.help.limit_access" />
                </div>
            </div>
        </div>
        <hr>
    </x-larastrap::mform>

    <hr>

    <div class="row">
        <div class="col">
            @include('commons.addingbutton', [
                'template' => 'circles.base-edit',
                'typename' => 'circle',
                'typename_readable' => __('texts.aggregations.group'),
                'targeturl' => 'circles',
                'target_update' => 'circle-list-' . $group->id,
                'extra' => [
                    'group_id' => $group->id,
                ]
            ])
        </div>
    </div>

    <div class="row mt-2">
        <div class="col">
            @include('commons.loadablelist', [
                'identifier' => 'circle-list-' . $group->id,
                'items' => $group->circles,
            ])
        </div>
    </div>
</div>

@stack('postponed')
