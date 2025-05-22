<x-larastrap::tabs :id="sprintf('supplier-' . sanitizeId($supplier->id))">
    <x-larastrap::tabpane active="true" tlabel="generic.details" icon="bi-tags">
        <x-larastrap::mform :obj="$supplier" method="PUT" :action="route('suppliers.update', $supplier->id)" classes="supplier-editor" :nodelete="$supplier->orders()->count() > 0">
            <input type="hidden" name="id" value="{{ $supplier->id }}" />

            <div class="row">
                <div class="col-md-6">
                    @include('supplier.base-edit', ['supplier' => $supplier])
                    <hr>
                    @include('commons.contactswidget', ['obj' => $supplier])
                </div>
                <div class="col-md-6">
                    @include('commons.statusfield', ['target' => $supplier])

                    <hr>

                    <x-larastrap::check name="fast_shipping_enabled" tlabel="supplier.enable_fast_shipping" tpophelp="supplier.help.enable_fast_shipping" />
                    <x-larastrap::radiolist name="notify_on_close_enabled" tlabel="supplier.send_notification_on_close" tpophelp="supplier.help.send_notification_on_close" :options="['none' => __('generic.no'), 'shipping' => __('orders.files.order.shipping'), 'summary' => __('orders.files.order.summary'), 'shipping_summary' => __('orders.files.order.shipping_and_summary')]" />

                    @if($currentgas->unmanaged_shipping == '1')
                        <x-larastrap::check name="unmanaged_shipping_enabled" tlabel="supplier.enable_no_quantities" tpophelp="supplier.help.enable_no_quantities" />
                    @endif

                    @include('commons.modifications', [
                        'obj' => $supplier,
                        'suggestion' => __('supplier.help.modifiers_notice')
                    ])

                    @include('commons.permissionseditor', ['object' => $supplier, 'master_permission' => 'supplier.modify', 'editable' => true])
                </div>
            </div>

            <hr/>
        </x-larastrap::mform>
    </x-larastrap::tabpane>

    <x-larastrap::tabpane tlabel="orders.all" icon="bi-list-task">
        @include('supplier.orders', ['supplier' => $supplier])
    </x-larastrap::tabpane>

    <x-larastrap::tabpane tlabel="products.list" icon="bi-cart">
        @include('supplier.products', ['supplier' => $supplier])
    </x-larastrap::tabpane>

    <x-larastrap::tabpane tlabel="supplier.attachments" icon="bi-files">
        @include('supplier.files', ['supplier' => $supplier])
    </x-larastrap::tabpane>

    @if(Gate::check('movements.view', $currentgas) || Gate::check('movements.admin', $currentgas))
        <x-larastrap::tabpane tlabel="generic.menu.accounting" icon="bi-piggy-bank">
            @include('supplier.accounting', ['supplier' => $supplier])
        </x-larastrap::tabpane>
    @endif
</x-larastrap::tabs>
