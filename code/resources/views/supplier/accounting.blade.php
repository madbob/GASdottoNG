@if(Gate::check('movements.admin', $currentgas) || Gate::check('movements.view', $currentgas))
    <div class="row">
        <div class="col-md-12">
            <p class="lead">Saldo Corrente: <span id="balance-supplier-{{ $supplier->id }}" data-fetch-url="{{ url('suppliers/' . $supplier->id . '/plain_balance') }}">{{ $supplier->balance }}</span> €</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <h4>Ordini da pagare</h4>

            <?php $orders = $supplier->orders()->where('status', '!=', 'archived')->get() ?>

            @if($orders->isEmpty())
                <div class="alert alert-info" role="alert">
                    Non ci sono elementi da visualizzare.
                </div>
            @else
                <ul class="list-group">
                    @foreach($orders as $order)
                        <a href="{{ $order->aggregate->getDisplayURL() }}" class="list-group-item">
                            {!! $order->printableHeader() !!}
                        </a>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
@endif
