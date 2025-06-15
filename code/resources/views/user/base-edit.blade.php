<x-larastrap::text name="username" tlabel="auth.username" required :pattern="usernamePattern()" tpophelp="auth.help.username" />
<x-larastrap::text name="firstname" tlabel="user.firstname" required />
<x-larastrap::text name="lastname" tlabel="user.lastname" required />

<x-larastrap::check name="sendmail" tlabel="auth.modes.email" checked :attributes="['data-bs-toggle' => 'collapse', 'data-bs-target' => '.alternate_behavior']" />

<x-larastrap::collapse classes="alternate_behavior" open>
    <x-larastrap::suggestion>
        {{ __('texts.auth.help.email_mode') }}
    </x-larastrap::suggestion>

    <x-larastrap::email name="email" tlabel="generic.email" />
</x-larastrap::collapse>

<x-larastrap::collapse classes="alternate_behavior">
    @include('commons.passwordfield', [
        'obj' => $user,
        'name' => 'password',
        'label' => __('texts.auth.password'),
        'classes' => 'required_when_triggered',
    ])
</x-larastrap::collapse>
