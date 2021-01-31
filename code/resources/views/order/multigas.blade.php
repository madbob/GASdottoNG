<div class="row">
    <div class="col-md-12">
        <ul class="nav nav-tabs" role="tablist">
            @foreach($aggregate->gas as $index => $gas)
                <li role="presentation" class="{{ $index == 0 ? 'active' : '' }}"><a href="#aggregate-gas-{{ $aggregate->id }}-{{ $gas->id }}" role="tab" data-toggle="tab">{{ $gas->printableName() }}</a></li>
            @endforeach

            <li role="presentation"><a href="#aggregate-gas-{{ $aggregate->id }}-total" role="tab" data-toggle="tab">{{ _i('Totale') }}</a></li>
        </ul>

        <?php

        $merged = [];
        $more_orders = ($aggregate->orders->count() > 1);

        foreach($aggregate->orders as $order) {
            $merged[$order->id] = null;
        }

        ?>

        <div class="tab-content">
            @foreach($aggregate->gas as $index => $gas)
                <div role="tabpanel" class="tab-pane {{ $index == 0 ? 'active' : '' }}" id="aggregate-gas-{{ $aggregate->id }}-{{ $gas->id }}">
                    <div class="row">
                        <div class="col-md-4 col-md-offset-8">
                            @include('aggregate.files', ['aggregate' => $aggregate, 'active_gas' => $gas])
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
                </div>
            @endforeach

            <div role="tabpanel" class="tab-pane {{ $index == 0 ? 'active' : '' }}" id="aggregate-gas-{{ $aggregate->id }}-total">
                <div class="row">
                    <div class="col-md-4 col-md-offset-8">
                        @include('aggregate.files', ['aggregate' => $aggregate, 'active_gas' => 0])
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
            </div>
        </div>
    </div>
</div>
