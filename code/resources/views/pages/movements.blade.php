@extends($theme_layout)

@section('content')

<div class="row">
    <div class="col-md-12">
        @can('movements.admin', $currentgas)
            @include('commons.addingbutton', [
                'template' => 'movement.base-edit',
                'typename' => 'movement',
                'typename_readable' => 'Movimento',
                'targeturl' => 'movements'
            ])
        @endcan
    </div>

    <div class="clearfix"></div>
    <hr/>
</div>

<div class="row">
    <div class="col-md-6">
        <form class="form-horizontal form-filler" action="{{ url('movements') }}" data-toggle="validator" data-fill-target="#movements-in-range">
            @include('commons.genericdaterange')
            @include('commons.selectmovementtypefield')
            @include('commons.selectobjfield', [
                'obj' => null,
                'name' => 'user_id',
                'label' => 'Utente',
                'objects' => App\User::orderBy('lastname', 'asc')->get(),
                'extra_selection' => [
                    '0' => 'Nessuno'
                ]
            ])
            @include('commons.selectobjfield', [
                'obj' => null,
                'name' => 'supplier_id',
                'label' => 'Fornitore',
                'objects' => App\Supplier::orderBy('name', 'asc')->get(),
                'extra_selection' => [
                    '0' => 'Nessuno'
                ]
            ])
            @include('commons.decimalfield', ['obj' => null, 'name' => 'amountstart', 'label' => 'Importo Minimo', 'postlabel' => '€'])
            @include('commons.decimalfield', ['obj' => null, 'name' => 'amountend', 'label' => 'Importo Massimo', 'postlabel' => '€'])

            <div class="form-group">
                <div class="col-sm-{{ $fieldsize }} col-md-offset-{{ $labelsize }}">
                    <button type="submit" class="btn btn-success">Ricerca</button>
                </div>
            </div>
        </form>
    </div>

    <div class="col-md-3 col-md-offset-3">
        <ul class="list-group">
            <li class="list-group-item">
                Saldo Conto Corrente
                <span class="badge">{{ $balance->bank }} €</span>
            </li>
            <li class="list-group-item">
                Saldo Contanti
                <span class="badge">{{ $balance->cash }} €</span>
            </li>
            <li class="list-group-item">
                Fornitori
                <span class="badge">{{ $balance->suppliers }} €</span>
            </li>
            <li class="list-group-item">
                Depositi
                <span class="badge">{{ $balance->deposits }} €</span>
            </li>
        </ul>

        <div class="pull-right">
            <form class="form-inline iblock inner-form password-protected" method="POST" action="{{ url('') }}">
                <div class="form-group">
                    <button type="submit" class="btn btn-danger">Ricalcola Saldi</button>
                </div>
            </form>
            <form class="form-inline iblock inner-form password-protected" method="POST" action="{{ url('') }}">
                <div class="form-group">
                    <button type="submit" class="btn btn-danger">Chiudi Bilancio</button>
                </div>
            </form>
        </div>
    </div>
</div>

<hr/>

<div class="row">
    <div class="col-md-12" id="movements-in-range">
        @include('movement.list', ['movements' => $movements])
    </div>
</div>

@endsection
