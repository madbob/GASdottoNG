<x-larastrap::modal :title="_i('Aggrega Ordini')">
    <x-larastrap::form method="POST" :action="route('aggregates.store')" id="orderAggregator">
        <input type="hidden" name="update-select" value="category_id">

        @if($orders->isEmpty())
            <p>
                {{ _i('Non ci sono elementi da visualizzare.') }}
            </p>
            <p>
                {{ _i("Una volta aggregati, gli ordini verranno visualizzati come uno solo pur mantenendo ciascuno i suoi attributi. Questa funzione è consigliata per facilitare l'amministrazione di ordini che, ad esempio, vengono consegnati nella stessa data.") }}
            </p>
        @else
            <p>
                {{ _i("Clicca e trascina gli ordini nella stessa cella per aggregarli, o in una cella vuota per disaggregarli.") }}
            </p>
            <p>
                {{ _i("Una volta aggregati, gli ordini verranno visualizzati come uno solo pur mantenendo ciascuno i suoi attributi. Questa funzione è consigliata per facilitare l'amministrazione di ordini che, ad esempio, vengono consegnati nella stessa data.") }}
            </p>

            <hr/>

            <div id="aggregable-list">
                <?php $index = 0 ?>

                @foreach($orders as $order)
                    <?php $order_status = $order->status ?>
                    @if(($order_status == 'shipped' && $order->orders->count() > 1) || ($order_status != 'shipped' && $order_status != 'archived'))
                        @if($index % 5 == 0)
                            <div class="card mb-1" data-aggregate-id="new">
                                <div class="card-body">
                                    <p class="clearfix">
                                        <i class="bi-arrows-fullscreen float-end explode-aggregate"></i>
                                    </p>

                                    <ul>
                                        <!-- Questo viene lasciato deliberatamente vuoto per poter fungere da appoggio per la creazione di un nuovo aggregato -->
                                    </ul>
                                </div>
                            </div>
                        @endif

                        <div class="card mb-1" data-aggregate-id="{{ $order->id }}">
                            <div class="card-body">
                                <p class="clearfix">
                                    <i class="bi-arrows-fullscreen float-end explode-aggregate"></i>
                                </p>

                                <ul>
                                    @foreach($order->orders as $suborder)
                                        <li data-order-id="{{ $suborder->id }}">
                                            {!! $suborder->printableHeader() !!}<br/>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>

                        <?php $index++ ?>
                    @endif
                @endforeach
            </div>
        @endif
    </x-larastrap::form>
</x-larastrap::modal>
