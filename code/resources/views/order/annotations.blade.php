@if(!empty($summary->notes))
    <div class="row">
        <div class="col-md-12">
            <div class="well">
                <table class="table">
                    <thead>
                        <tr>
                            <th width="20%">Utente</th>
                            <th width="80%">Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($summary->notes as $n)
                            <tr>
                                <td>{{ $n->user }}</td>
                                <td>{{ $n->note }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif
