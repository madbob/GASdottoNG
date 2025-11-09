@extends('app')

@section('content')

@if($notifications->isEmpty() == false)
    <div class="row mb-3" id="home-notifications">
        <div class="col">
            <div class="card shadow">
                <div class="card-header">
                    {{ __('texts.generic.menu.notifications') }}
                </div>
                <div class="card-body">
                    @foreach($notifications as $notify)
                        <x-larastrap::suggestion classes="alert-dismissible fade show">
                            @if($notify->permanent == false)
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                <input type="hidden" name="notification_id" value="{{ $notify->id }}" />
                            @endif

                            {!! $notify->formattedContent($currentuser) !!}

                            @if($notify->attachments->isEmpty() == false)
                                <hr>
                                @foreach($notify->attachments as $attachment)
                                    <a class="btn btn-info" href="{{ $attachment->download_url }}">
                                        {{ $attachment->name }} <i class="bi-download"></i>
                                    </a>
                                @endforeach
                            @endif
                        </x-larastrap::suggestion>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endif

<div class="row">
    <div class="col-md-6">
        @if(Gate::check('supplier.book', null))
            <div class="card shadow mb-3">
                <div class="card-header">
                    <p class="float-end m-0">
                        <a target="_blank" href="{{ url('ordini.xml') }}"><i class="bi-rss"></i></a>
                        <a target="_blank" href="{{ url('ordini.ics') }}"><i class="bi-calendar"></i></a>
                    </p>
                    {{ __('texts.orders.list_open') }}
                </div>
                @if(count($opened) == 0)
                    <div class="card-body">
                        <x-larastrap::suggestion>
                            {{ __('texts.orders.help.no_opened') }}
                        </x-larastrap::suggestion>
                    </div>
                @else
                    @include('order.homelist', ['orders' => $opened])
                @endif
            </div>

            <div class="card shadow mb-3">
                <div class="card-header">
                    {{ __('texts.orders.list_delivering') }}
                </div>
                @if(count($shipping) == 0)
                    <div class="card-body">
                        <x-larastrap::suggestion>
                            {{ __('texts.orders.help.no_delivering') }}
                        </x-larastrap::suggestion>
                    </div>
                @else
                    @include('order.homelist', ['orders' => $shipping])
                @endif
            </div>
        @endif

        @include('attachment.partials.downloadable', [
            'obj' => $currentgas,
        ])
    </div>

    <div class="col-md-6">
        @if($currentuser->isFriend() == false)
            @php
            $configuration = $currentgas->credit_home;
            @endphp

            @if($configuration['current_credit'] || $configuration['to_pay'])
                @php

                $balances = [];
                $default_currency = defaultCurrency();
                $currencies = App\Currency::enabled();

                if ($configuration['current_credit']) {
                    foreach ($currencies as $currency) {
                        $balances[$currency->id] = $currentuser->currentBalanceAmount($currency);
                    }
                }
                else {
                    $balances[$default_currency->id] = 0;
                }

                if ($configuration['to_pay']) {
                    $to_pay = $currentuser->pending_balance;
                    $to_pay_friend = [];

                    foreach($currentuser->friends as $friend) {
                        $tpf = $friend->pending_balance;
                        if ($tpf != 0) {
                            $to_pay += $tpf;
                            $to_pay_friend[$friend->printableName()] = printablePrice($tpf);
                        }
                    }
                }
                else {
                    $to_pay = 0;
                }

                @endphp

                <div class="alert shadow {{ $balances[$default_currency->id] >= $to_pay ? 'alert-success' : 'alert-danger' }} text-right">
                    @if($configuration['current_credit'])
                        @foreach($currencies as $curr)
                            <p class="d-flex align-items-center justify-content-start">
                                <x-larastrap::pophelp classes="me-2" ttext="movements.help.current_balance" />
                                <span class="lead">{{ __('texts.movements.current_credit') }}: {{ printablePriceCurrency($balances[$curr->id], '.', $curr) }}</span>
                            </p>
                        @endforeach
                    @endif

                    @if($configuration['to_pay'])
                        <p class="d-flex align-items-center justify-content-start">
                            <x-larastrap::pophelp classes="me-2" ttext="movements.help.pending_bookings_to_pay" />
                            <span class="lead">{{ __('texts.movements.to_pay') }}: {{ printablePriceCurrency($to_pay) }}</span>
                        </p>
                        @if(!empty($to_pay_friend))
                            <p>{{ __('texts.generic.split') }}</p>
                            @foreach($to_pay_friend as $friend_name => $friend_amount)
                                <p>{{ $friend_name }} {{ printablePriceCurrency($friend_amount) }}</p>
                            @endforeach
                        @endif
                    @endif
                </div>

                <br>
            @endif
        @endif

        <div class="panel panel-default">
            <div class="panel-body">
                @include('dates.calendar')
            </div>
        </div>
    </div>
</div>

@endsection
