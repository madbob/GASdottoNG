@extends($theme_layout)

@section('content')

@if($notifications->isEmpty() == false)
    <div class="row" id="home-notifications">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h2 class="panel-title">Notifiche</h2>
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
                <h2 class="panel-title">Ordini Aperti</h2>
            </div>
            <div class="panel-body">
                @if(count($opened) == 0)
                    <div class="alert alert-info" role="alert">
                        Non ci sono ordini aperti.
                    </div>
                @else
                    @include('order.homelist', ['orders' => $opened])
                @endif
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h2 class="panel-title">Ordini in Consegna</h2>
            </div>
            <div class="panel-body">
                @if(count($shipping) == 0)
                    <div class="alert alert-info" role="alert">
                        Non ci sono ordini in consegna.
                    </div>
                @else
                    @include('order.homelist', ['orders' => $shipping])
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="text-right">
            <p class="lead">Credito Corrente: {{ printablePrice($currentuser->current_balance_amount) }} €</p>
            <p class="lead">Da Pagare: {{ printablePrice($currentuser->pending_balance) }} €</p>
        </div>

        @if($currentgas->attachments->isEmpty() == false)
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h2 class="panel-title">Files Condivisi</h2>
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
    </div>
</div>

@endsection
