<div class="row">
    <div class="col">
        <?php

        $merged = [];
        $more_orders = ($aggregate->orders->count() > 1);

        foreach($aggregate->orders as $order) {
            $merged[$order->id] = null;
        }

        ?>

        <x-larastrap::tabs>
            @foreach($aggregate->gas as $index => $gas)
                <x-larastrap::tabpane :active="$index == 0" :label="$gas->printableName()">
                    <div class="row">
                        <div class="col-md-4 col-md-offset-8">
                            @include('aggregate.files', ['aggregate' => $aggregate, 'managed_gas' => $gas->id])
                        </div>

                        <div class="col-md-12">
                            @foreach($aggregate->orders as $order)
                                @if($more_orders)
                                    <h4>{{ $order->supplier->printableName() }}</h4>
                                @endif

                                <?php

                                App::make('GlobalScopeHub')->setGas($gas->id);
                                $summary = $order->reduxData();
                                $merged[$order->id] = $order->mergeReduxData($merged[$order->id], $summary);

                                ?>

                                @include('order.summary_ro', ['order' => $order, 'summary' => $summary])
                            @endforeach
                        </div>
                    </div>
                </x-larastrap::tabpane>
            @endforeach

            <x-larastrap::tabpane :label="_i('Totale')">
                <div class="row">
                    <div class="col-md-4 col-md-offset-8">
                        @include('aggregate.files', ['aggregate' => $aggregate, 'managed_gas' => 0])
                    </div>

                    <div class="col-md-12">
                        @foreach($aggregate->orders as $order)
                            @if($more_orders)
                                <h4>{{ $order->supplier->printableName() }}</h4>
                            @endif

                            @include('order.summary_ro', ['order' => $order, 'summary' => $merged[$order->id]])
                        @endforeach
                    </div>
                </div>
            </x-larastrap::tabpane>
        </x-larastrap::tabs>
    </div>
</div>
