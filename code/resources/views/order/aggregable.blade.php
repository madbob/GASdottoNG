<?php $index = 0 ?>

@foreach($orders as $order)
    @if(($order->status == 'shipped' && count($order->orders) > 1) || ($order->status != 'shipped' && $order->status != 'archived'))
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
