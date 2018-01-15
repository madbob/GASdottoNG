@extends($theme_layout)

@section('content')

@if($notifications->isEmpty() == false)
    <div class="row" id="home-notifications">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h2 class="panel-title">{{ _i('Notifiche') }}</h2>
                </div>
                <div class="panel-body">
                    <ul class="list-group">
                        @foreach($notifications as $notify)
                            <li class="list-group-item alert alert-info">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <input type="hidden" name="notification_id" value="{{ $notify->id }}" />
                                {!! nl2br($notify->content) !!}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endif

<div class="row">
    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h2 class="panel-title">{{ _i('Prenotazioni Aperte') }}</h2>
            </div>
            <div class="panel-body">
                @if(count($opened) == 0)
                    <div class="alert alert-info" role="alert">
                        {{ _i('Non ci sono prenotazioni aperte.') }}
                    </div>
                @else
                    @include('order.homelist', ['orders' => $opened])
                @endif
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h2 class="panel-title">{{ _i('Ordini in Consegna') }}</h2>
            </div>
            <div class="panel-body">
                @if(count($shipping) == 0)
                    <div class="alert alert-info" role="alert">
                        {{ _i('Non ci sono ordini in consegna.') }}
                    </div>
                @else
                    @include('order.homelist', ['orders' => $shipping])
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-6">
        @if($currentuser->isFriend() == false)
            <?php

            $current_balance = $currentuser->current_balance_amount;
            $to_pay = $currentuser->pending_balance;

            ?>

            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="alert {{ $current_balance >= $to_pay ? 'alert-success' : 'alert-danger' }} text-right">
                        <p class="lead">{{ _i('Credito Attuale') }}: {{ printablePrice($current_balance) }} €</p>
                        <p class="lead">{{ _i('Da Pagare') }}: {{ printablePrice($to_pay) }} €</p>
                    </div>
                </div>
            </div>

            @if($currentgas->attachments->isEmpty() == false)
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h2 class="panel-title">{{ _i('File Condivisi') }}</h2>
                    </div>
                    <div class="panel-body">
                        <div class="list-group">
                            @foreach($currentgas->attachments as $attachment)
                                <a href="{{ $attachment->download_url }}" class="list-group-item">{{ $attachment->name }}</a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>

@endsection
