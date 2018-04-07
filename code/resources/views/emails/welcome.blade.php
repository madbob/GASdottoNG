<p>
    {{ _i('Benvenuto in %s!', $user->gas->name) }}
</p>
<p>
    {{ _i('In futuro potrai accedere usando il link qui sotto, lo username "%s" e la password da te scelta.', $user->username) }}
</p>
<p>
    <a href="{{ route('login') }}">{{ route('login') }}</a>
</p>
<p>
    {{ _i("Una mail di notifica è stata inviata agli amministratori.") }}
</p>
