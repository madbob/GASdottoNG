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
    <div class="col-md-12">
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
                <ul class="list-group">
                    @foreach($opened as $open)
                        <a href="{{ $open->getDisplayURL() }}" class="list-group-item">
                            {!! $open->printableHeader() !!}
                        </a>
                    @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
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
                    <ul class="list-group">
                        @foreach($shipping as $ship)
                            <a href="{{ $ship->getDisplayURL() }}" class="list-group-item">
                                {{ $ship->printableHeader() }}
                            </a>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
