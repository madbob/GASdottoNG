{!! join(';', $data->headers) !!}
@foreach($data->contents as $row)
{!! join(';', $row) !!}
@endforeach
