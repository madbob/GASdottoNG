<div class="row">
    <div class="col">
        <?php

        $more_orders = ($aggregate->orders->count() > 1);

        ?>

        <x-larastrap::tabs>
            @foreach($aggregate->gas as $index => $gas)
                <?php

                /*
                    Per ogni GAS coinvolto ricarico gli ordini dal database
                    (altrimenti restano quelli dell'esecuzione precedente, con
                    le precedenti prenotazioni) e rieseguo la riduzione dei dati
                */
                App::make('GlobalScopeHub')->setGas($gas->id);
                $aggregate->load('orders');
                $master_summary = $aggregate->reduxData();

                ?>

                <x-larastrap::tabpane :active="$index == 0" :label="$gas->printableName()" icon="bi-list-task">
                    <div class="row">
                        <div class="col-md-4 offset-md-8 mb-2">
                            @include('aggregate.files', ['aggregate' => $aggregate, 'managed_gas' => $gas->id])
                        </div>

                        <div class="col-md-12">
                            @foreach($aggregate->orders as $order)
                                @if($more_orders)
                                    <h4>{{ $order->supplier->printableName() }}</h4>
                                @endif

                                @include('order.summary_ro', ['order' => $order, 'master_summary' => $master_summary])
                            @endforeach
                        </div>
                    </div>
                </x-larastrap::tabpane>
            @endforeach

            <x-larastrap::tabpane tlabel="orders.totals.total" icon="bi-plus-circle">
                <div class="row">
                    <div class="col-md-4 offset-md-8 mb-2">
                        @include('aggregate.files', ['aggregate' => $aggregate, 'managed_gas' => 0])
                    </div>

                    <?php

                    App::make('GlobalScopeHub')->enable(false);
                    $aggregate->load('orders');
                    $master_summary = $aggregate->reduxData();

                    ?>

                    <div class="col-md-12">
                        @foreach($aggregate->orders as $order)
                            @if($more_orders)
                                <h4>{{ $order->supplier->printableName() }}</h4>
                            @endif

                            @include('order.summary_ro', ['order' => $order, 'master_summary' => $master_summary])
                        @endforeach
                    </div>
                </div>
            </x-larastrap::tabpane>
        </x-larastrap::tabs>
    </div>
</div>
