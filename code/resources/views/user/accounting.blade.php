@if($currentuser->id == $user->id && $user->gas->hasFeature('satispay'))
    <div class="row">
        <div class="col">
            @if($user->gas->hasFeature('satispay'))
                <x-larastrap::mbutton :label="_i('Ricarica Credito con Satispay')" triggers_modal="#satispayCredit" />

                <x-larastrap::modal id="satispayCredit" :title="_i('Ricarica Credito')">
                    <x-larastrap::form classes="direct-submit" method="POST" :action="route('payment.do')">
                        <input type="hidden" name="type" value="satispay">

                        <p>
                            {{ _i('Da qui puoi ricaricare il tuo credito utilizzando Satispay.') }}
                        </p>
                        <p>
                            {{ _i('Specifica quanto vuoi versare ed eventuali note per gli amministratori; riceverai una notifica sul tuo smartphone per confermare, entro 15 minuti, il versamento.') }}
                        </p>

                        <x-larastrap::text name="mobile" tlabel="generic.phone" required />
                        <x-larastrap::price name="amount" :label="_i('Valore')" required />
                        <x-larastrap::text name="description" :label="_i('Descrizione')" />
                    </x-larastrap::form>
                </x-larastrap::modal>
            @endif
        </div>
    </div>

    <hr/>
@endif

@include('movement.targetlist', ['target' => $user])
