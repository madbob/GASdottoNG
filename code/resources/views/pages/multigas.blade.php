@extends('app')

@section('content')

<ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active"><a href="#allgas" role="tab" data-toggle="tab">{{ _i('GAS') }}</a></li>
    <li role="presentation"><a href="#allsuppliers" role="tab" data-toggle="tab">{{ _i('Fornitori') }}</a></li>
    <li role="presentation"><a href="#allorders" role="tab" data-toggle="tab">{{ _i('Ordini') }}</a></li>
</ul>

<div class="tab-content">
    <div role="tabpanel" class="tab-pane active" id="allgas">
        <div class="row">
            <div class="col-md-12">
                @include('commons.addingbutton', [
                    'template' => 'multigas.base-edit',
                    'typename' => 'gas',
                    'typename_readable' => _i('GAS'),
                    'targeturl' => 'multigas'
                ])
            </div>
        </div>

        <div class="clearfix"></div>
        <hr/>

        <div class="row">
            <div class="col-md-12">
                @include('commons.loadablelist', [
                    'identifier' => 'gas-list',
                    'items' => $groups,
                    'url' => url('multigas'),
                    'legend' => (object)[
                        'class' => 'Gas'
                    ]
                ])
            </div>
        </div>
    </div>

    <div role="tabpanel" class="tab-pane" id="allsuppliers">
        <div class="row">
            <div class="col-md-12">
                <table class="table multigas-editor">
                    <thead>
                        <tr>
                            <th></th>

                            @foreach($groups as $gas)
                                <th>
                                    {{ $gas->name }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(App\Supplier::orderBy('name', 'asc')->get() as $supplier)
                            <tr>
                                <td>{{ $supplier->name }}</td>

                                @foreach($groups as $gas)
                                    <td>
                                        <input type="checkbox" data-toggle="toggle" data-size="mini" data-gas="{{ $gas->id }}" data-target-type="supplier" data-target-id="{{ $supplier->id }}" {{ $gas->suppliers()->where('suppliers.id', $supplier->id)->first() != null ? 'checked' : '' }}>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div role="tabpanel" class="tab-pane" id="allorders">
        <div class="row">
            <div class="col-md-12">
                <table class="table multigas-editor">
                    <thead>
                        <tr>
                            <th></th>

                            @foreach($groups as $gas)
                                <th>
                                    {{ $gas->name }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(App\Aggregate::whereHas('orders', function($query) {
                            $query->whereIn('status', ['open', 'closed', 'suspended']);
                        })->orderBy('id', 'asc')->get() as $aggregate)
                            <tr>
                                <td>{{ $aggregate->printableName() }}</td>

                                @foreach($groups as $gas)
                                    <td>
                                        <input type="checkbox" data-toggle="toggle" data-size="mini" data-gas="{{ $gas->id }}" data-target-type="aggregate" data-target-id="{{ $aggregate->id }}" {{ $gas->aggregates()->where('aggregates.id', $aggregate->id)->first() != null ? 'checked' : '' }}>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
