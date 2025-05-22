@if($currentuser->id == $user->id && $user->gas->hasFeature('satispay'))
    <div class="row">
        <div class="col">
            @if($user->gas->hasFeature('satispay'))
                <x-larastrap::mbutton tlabel="user.satispay.reload" triggers_modal="#satispayCredit" />

                <x-larastrap::modal id="satispayCredit">
                    <x-larastrap::form classes="direct-submit" method="POST" :action="route('payment.do')">
                        <input type="hidden" name="type" value="satispay">

                        <p>{{ __('user.help.satispay') }}</p>

                        <x-larastrap::text name="mobile" tlabel="generic.phone" required />
                        <x-larastrap::price name="amount" tlabel="generic.value" required />
                        <x-larastrap::text name="description" tlabel="generic.description" />
                    </x-larastrap::form>
                </x-larastrap::modal>
            @endif
        </div>
    </div>

    <hr/>
@endif

@include('movement.targetlist', ['target' => $user])
