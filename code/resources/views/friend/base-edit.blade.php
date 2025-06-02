<x-larastrap::text name="username" tlabel="auth.username" required :pattern="usernamePattern()" tpophelp="auth.help.username" />
<x-larastrap::text name="firstname" tlabel="user.firstname" required />
<x-larastrap::text name="lastname" tlabel="user.lastname" required />

@include('commons.passwordfield', [
    'obj' => $user,
    'name' => 'password',
    'label' => __('auth.password'),
    'mandatory' => true,
])
