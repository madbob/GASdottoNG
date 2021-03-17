<div>
    <div id="orderAggregator">
        <form class="form-horizontal" method="POST" action="{{ route('aggregates.store') }}" data-toggle="validator">
            <input type="hidden" name="update-select" value="category_id">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">{{ _i('Aggrega Ordini') }}</h4>
            </div>
            <div class="modal-body">
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
                                    <div class="well" data-aggregate-id="new">
                                        <span class="glyphicon glyphicon-fullscreen pull-right explode-aggregate" aria-hidden="true"></span>

                                        <ul>
                                            <!-- Questo viene lasciato deliberatamente vuoto per poter fungere da appoggio per la creazione di un nuovo aggregato -->
                                        </ul>
                                    </div>
                                @endif

                                <div class="well" data-aggregate-id="{{ $order->id }}">
                                    <p class="clearfix">
                                        <span class="glyphicon glyphicon-fullscreen pull-right explode-aggregate" aria-hidden="true"></span>
                                    </p>

                                    <ul>
                                        @foreach($order->orders as $suborder)
                                            <li data-order-id="{{ $suborder->id }}">
                                                {!! $suborder->printableHeader() !!}<br/>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>

                                <?php $index++ ?>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ _i('Annulla') }}</button>
                <button type="submit" class="btn btn-success">{{ _i('Salva') }}</button>
            </div>
        </form>
    </div>
</div>
