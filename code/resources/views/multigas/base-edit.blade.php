<x-larastrap::text name="name" tlabel="generic.multigas_name" required />

<hr>

<x-larastrap::field label="">
    <div class="form-text">
        {{ __('texts.generic.help.multigas_admin_instructions') }}
    </div>
</x-larastrap::field>

<x-larastrap::text name="username" tlabel="auth.username" required />
<x-larastrap::text name="firstname" tlabel="user.firstname" required />
<x-larastrap::text name="lastname" tlabel="user.lastname" required />
<x-larastrap::password name="password" tlabel="auth.password" required />
