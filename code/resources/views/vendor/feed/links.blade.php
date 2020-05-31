@foreach($feeds as $name => $config)
    <link rel="alternate" type="application/rss+xml" href="{{ route("feeds.{$name}") }}" title="{{ $config['title'] }}">
@endforeach
