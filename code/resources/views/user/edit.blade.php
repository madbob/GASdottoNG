@php

if (isset($readonly) && $readonly) {
    $admin_editable = $editable = $personal_details = false;
}
else {
    $readonly = false;
    $admin_editable = $currentuser->can('users.admin', $currentgas);
    $editable = ($admin_editable || ($currentuser->id == $user->id && $currentuser->can('users.self', $currentgas)) || $user->parent_id == $currentuser->id);
    $personal_details = ($currentuser->id == $user->id);
}

$display_page = $display_page ?? false;
$has_accounting = ($admin_editable || $currentuser->id == $user->id || $currentuser->can('movements.admin', $currentgas) || $currentuser->can('movements.view', $currentgas)) && (!$user->isFriend() && someoneCan('movements.admin', $user->gas));
$has_stats = $has_accounting;
$has_bookings = ($currentuser->id == $user->id);
$has_friends = ($admin_editable || $currentuser->id == $user->id) && $user->can('users.subusers', $user->gas);
$has_notifications = $editable && !$user->isFriend() && !$currentgas->getConfig('notify_all_new_orders');

$friend_admin_buttons = [];
if ($user->isFriend() && $admin_editable) {
    $friend_admin_buttons = [
        [
            'label' => __('texts.user.change_friend'),
            'classes' => ['float-start', 'prevent-default', 'me-2'],
            'attributes' => ['data-bs-toggle' => 'modal', 'data-bs-target' => '#change_friend_' . $user->id]
        ]
    ];
}

$groups = $user->eligibleGroups();

@endphp

<x-larastrap::tabs>
    <x-larastrap::tabpane :id="sprintf('profile-%s', sanitizeId($user->id))" label="{{ __('texts.user.personal_data') }}" active="true" classes="mb-2" icon="bi-person">
        @if($admin_editable)
            @if($user->pending)
                <div class="alert alert-warning mb-3 d-flex flex-column flex-md-row align-items-center justify-content-between">
                    <div class="d-inline-block">{{ __('texts.user.help.waiting_approval') }}</div>

                    <div>
                        <x-larastrap::iform :action="route('users.revisioned', $user->id)" :buttons="[['tlabel' => 'user.approve', 'color' => 'success']]" classes="float-end ms-2">
                            <x-larastrap::hidden name="post-saved-function" value="handleUserApproval" />
                            <x-larastrap::hidden name="action" value="approve" />
                        </x-larastrap::iform>

                        <x-larastrap::iform :action="route('users.revisioned', $user->id)" :buttons="[['tlabel' => 'user.do_not_approve', 'color' => 'danger']]" classes="float-end">
                            <x-larastrap::hidden name="post-saved-function" value="handleUserApproval" />
                            <x-larastrap::hidden name="action" value="noapprove" />
                        </x-larastrap::iform>
                    </div>
                </div>
            @endif
        @endif

        <x-larastrap::mform :obj="$user" method="PUT" :action="route('users.update', $user->id)" :nodelete="$display_page || !$user->isFriend()" :nosave="!$editable" :other_buttons="$friend_admin_buttons">
            @if($display_page)
                <x-larastrap::hidden name="post-saved-function" value="savedFeedback" />
            @endif

            <div class="row">
                <div class="col-12 col-md-6">
                    @if(!$user->isFriend())
                        <x-ls::card header="auth.access">
                            <x-larastrap::username name="username" tlabel="auth.username" :required="$editable" :readonly="!$editable" />

                            @if($admin_editable || $personal_details)
                                @include('commons.passwordfield', ['obj' => $user, 'name' => 'password', 'label' => __('texts.auth.password')])
                            @endif

                            @if($admin_editable)
                                @include('commons.statusfield', ['target' => $user])
                            @endif
                        </x-ls::card>

                        @if($editable)
                            <x-ls::card header="user.personal_data">
                                <x-larastrap::text name="firstname" tlabel="user.firstname" />
                                <x-larastrap::text name="lastname" tlabel="user.lastname" />
                                <x-larastrap::text name="birthplace" tlabel="user.birthplace" />
                                <x-larastrap::datepicker name="birthday" tlabel="user.birthdate" />
                                <x-larastrap::text name="taxcode" tlabel="user.taxcode" />
                                <x-larastrap::text name="family_members" tlabel="user.family_members" />

                                @if($editable)
                                    @include('commons.imagefield', ['obj' => $user, 'name' => 'picture', 'label' => __('texts.generic.photo'), 'valuefrom' => 'picture_url'])
                                @else
                                    @include('commons.staticimagefield', ['obj' => $user, 'label' => __('texts.generic.photo'), 'valuefrom' => 'picture_url'])
                                @endif
                            </x-ls::card>

                            @include('commons.contactswidget', ['obj' => $user])
                        @else
                            <x-ls::card header="user.personal_data" readonly>
                                <x-larastrap::text name="firstname" tlabel="user.firstname" />
                                <x-larastrap::text name="lastname" tlabel="user.lastname" />

                                @if($personal_details)
                                    <x-larastrap::datepicker name="birthday" tlabel="user.birthdate" />
                                    <x-larastrap::text name="taxcode" tlabel="user.taxcode" />
                                @endif
                            </x-ls::card>

                            @include('commons.staticcontactswidget', ['obj' => $user])
                        @endif
                    @else
                        {{--
                            Qui assumo che il pannello di amministrazione degli
                            amici sia sempre e solo raggiungibile da utenti
                            autorizzati a modificarli (l'utente principale, o un
                            amministratore, o l'amico stesso)
                        --}}

                        <x-ls::card header="auth.access">
                            <x-larastrap::username name="username" tlabel="auth.username" />
                            @include('commons.passwordfield', ['obj' => $user, 'name' => 'password', 'label' => __('texts.auth.password')])
                        </x-ls::card>

                        <x-ls::card header="user.personal_data">
                            <x-larastrap::text name="firstname" tlabel="user.firstname" />
                            <x-larastrap::text name="lastname" tlabel="user.lastname" />
                        </x-ls::card>

                        @include('commons.contactswidget', ['obj' => $user])
                    @endif
                </div>
                <div class="col-12 col-md-6">
                    @if(!$user->isFriend())
                        <x-ls::card header="generic.gas">
                            <x-larastrap::datepicker name="member_since" tlabel="user.member_since" :readonly="!$admin_editable" />
                            <x-larastrap::text name="card_number" tlabel="user.card_number" :readonly="!$admin_editable" />

                            @if($editable || $personal_details)
                                @include('user.movements', ['editable' => $admin_editable])
                                <x-larastrap::datepicker name="last_login" tlabel="user.last_login" readonly />
                                <x-larastrap::datepicker name="last_booking" tlabel="user.last_booking" readonly />
                            @endif

                            @if($admin_editable)
                                <x-larastrap::radios name="payment_method_id" tlabel="user.payment_method" :options="paymentsSimple()" />

                                @if($user->gas->hasFeature('rid'))
                                    <x-larastrap::field tlabel="user.sepa.intro" tpophelp="user.sepa.help">
                                    <x-larastrap::text name="rid->iban" tlabel="generic.iban" squeeze="true" :value="$user->rid['iban'] ?? ''" tplaceholder="generic.iban" />
                                    <x-larastrap::text name="rid->id" tlabel="user.sepa.identifier" squeeze="true" :value="$user->rid['id'] ?? ''" tplaceholder="user.sepa.identifier" />
                                    <x-larastrap::datepicker name="rid->date" tlabel="user.sepa.date" squeeze="true" :value="$user->rid['date'] ?? ''" />
                                    </x-larastrap::field>
                                @endif
                            @endif
                        </x-ls::card>

                        @if(!$groups->isEmpty())
                            <x-ls::card header="aggregations.all">
                                @foreach($groups as $ug)
                                    @if($admin_editable || $ug->user_selectable)
                                        <x-larastrap::hidden name="groups[]" :value="$ug->id" />
                                        <x-dynamic-component :component="sprintf('larastrap::%s', $ug->cardinality == 'single' ? 'radiolist-model' : 'checklist-model')" :params="['name' => 'circles', 'npostfix' => sprintf('__%s__%s[]', sanitizeId($user->id), sanitizeId($ug->id)), 'label' => $ug->name, 'options' => $ug->circles]" />
                                    @else
                                        <x-dynamic-component :component="sprintf('larastrap::%s', $ug->cardinality == 'single' ? 'radiolist-model' : 'checklist-model')" :params="['name' => 'circles', 'npostfix' => sprintf('__%s__%s[]', sanitizeId($user->id), sanitizeId($ug->id)), 'label' => $ug->name, 'options' => $ug->circles]" readonly />
                                    @endif
                                @endforeach
                            </x-ls::card>
                        @endif

                        @include('commons.permissionsviewer', ['object' => $user, 'editable' => $admin_editable])
                    @else
                        <x-ls::card header="generic.gas">
                        <x-larastrap::datepicker name="member_since" tlabel="user.member_since" readonly />
                        <x-larastrap::datepicker name="last_login" tlabel="user.last_login" readonly />
                        <x-larastrap::datepicker name="last_booking" tlabel="user.last_booking" readonly />
                        </x-ls::card>
                    @endif
                </div>
            </div>

            <hr/>
        </x-larastrap::mform>

        @php
        $removeModalId = sprintf('remove-account-%s', sanitizeId($user->id));
        @endphp

        @if(!$user->isFriend())
            @if($currentuser->can('users.destroy') && $currentuser->id !== $user->id)
                <p class="text-end">
                    <x-larastrap::link color="danger" classes="mt-2" :triggers_modal="$removeModalId" tlabel="user.remove_profile" />
                </p>

                <x-larastrap::modal :id="$removeModalId">
                    <x-larastrap::iform method="DELETE" :action="route('users.destroy', $user->id)" id="user-destroy-modal" :buttons="[['type' => 'submit', 'color' => 'danger', 'tlabel' => 'user.remove_profile']]">
                        <p>{{ __('texts.user.help.remove_profile') }}</p>
                        <input type="hidden" name="pre-saved-function" value="passwordProtected">
                        <input type="hidden" name="post-saved-function" value="closeAllModals">
                        <input type="hidden" name="post-saved-function" value="removeTargetListItem">
                    </x-larastrap::iform>
                </x-larastrap::modal>
            @else
                @if($currentuser->id === $user->id && $currentuser->can('users.selfdestroy'))
                    <p class="text-end">
                        <x-larastrap::link color="danger" classes="float-end mt-2" :triggers_modal="$removeModalId" tlabel="user.remove_profile" />
                    </p>

                    <x-larastrap::modal :id="$removeModalId">
                        <x-larastrap::iform method="DELETE" :action="route('users.destroy', $user->id)" id="user-destroy-modal" :buttons="[['type' => 'submit', 'color' => 'danger', 'tlabel' => 'user.remove_profile']]">
                            <p>{{ __('texts.user.help.remove_profile') }}</p>

                            @if($user->currentBalanceAmount() != 0)
                                <p>
                                    {{ __('texts.user.help.remove_profile_credit_notice') }}
                                </p>
                            @endif

                            <input type="hidden" name="pre-saved-function" value="passwordProtected">
                        </x-larastrap::iform>
                    </x-larastrap::modal>
                @endif
            @endif
        @endif

        @if($user->isFriend() && $admin_editable)
            @push('postponed')
                <x-larastrap::modal :id="sprintf('change_friend_%s', $user->id)">
                    <x-larastrap::accordion>
                        <x-larastrap::accordionitem tlabel="user.promote_friend" active="false">
                            <x-larastrap::mform :action="route('users.promote', $user->id)" keep_buttons="true" nodelete="true">
                                <x-larastrap::hidden name="close-modal" value="1" />
                                <x-larastrap::hidden name="reload-portion" :value="sprintf('#friends-tab-%s', $user->parent_id)" />
                                <x-larastrap::hidden name="append-list" value="user-list" />

                                <p>{{ __('texts.user.help.promote_friend', ['role' => roleByIdentifier('user')->name, 'ex_parent' => $user->parent->printableName()]) }}</p>

                                @if(blank($user->email))
                                    <hr>
                                    <x-larastrap::email tlabel="generic.email" name="email" thelp="user.help.promote_friend_enforce_mail" required />
                                @endif
                            </x-larastrap::mform>
                        </x-larastrap::accordionitem>
                        <x-larastrap::accordionitem tlabel="user.reassign_friend" active="false">
                            <x-larastrap::mform :action="route('users.reassign', $user->id)" keep_buttons="true" nodelete="true">
                                <x-larastrap::hidden name="close-modal" value="1" />
                                <x-larastrap::hidden name="reload-portion" :value="sprintf('#friends-tab-%s', $user->parent_id)" />

                                <p>
                                    {{ __('texts.user.help.reassign_friend', ['ex_parent' => $user->parent->printableName()]) }}
                                </p>

                                <x-larastrap::select-model tlabel="user.change_friend_assignee" name="parent_id" :options="App\User::where('id', '!=', $user->parent_id)->with(['gas'])->topLevel()->sorted()->get()->filter(fn($u) => $u->can('users.subusers', $u->gas))" />
                            </x-larastrap::mform>
                        </x-larastrap::accordionitem>
                    </x-larastrap::accordion>
                </x-larastrap::modal>
            @endpush
        @endif
    </x-larastrap::tabpane>

    @if($has_accounting)
        <x-larastrap::remotetabpane :id="sprintf('accounting-%s', sanitizeId($user->id))" tlabel="generic.menu.accounting" :button_attributes="['data-tab-url' => route('users.accounting', $user->id)]" icon="bi-piggy-bank">
        </x-larastrap::remotetabpane>
    @endif

    @if($has_accounting && $user->gas->hasFeature('extra_invoicing'))
        <x-larastrap::remotetabpane :id="sprintf('receipts-%s', sanitizeId($user->id))" tlabel="generic.menu.receipts" :button_attributes="['data-tab-url' => route('receipts.index', ['user_id' => $user->id])]" icon="bi-graph-up">
        </x-larastrap::remotetabpane>
    @endif

    @if($has_bookings)
        <x-larastrap::remotetabpane :id="sprintf('bookings-%s', sanitizeId($user->id))" tlabel="generic.menu.bookings" :button_attributes="['data-tab-url' => route('users.bookings', $user->id)]" icon="bi-list-task">
        </x-larastrap::remotetabpane>
    @endif

    @if($has_stats)
        <x-larastrap::remotetabpane :id="sprintf('stats-%s', sanitizeId($user->id))" tlabel="generic.menu.stats" :button_attributes="['data-tab-url' => route('users.stats', $user->id)]" icon="bi-graph-up">
        </x-larastrap::remotetabpane>
    @endif

    @if($has_friends)
        <x-larastrap::remotetabpane :id="sprintf('friends-%s', sanitizeId($user->id))" tlabel="generic.menu.friends" :button_attributes="['id' => sprintf('friends-tab-%s', sanitizeId($user->id)), 'data-tab-url' => route('users.friends', $user->id)]" icon="bi-person-add">
        </x-larastrap::remotetabpane>
    @endif

    @if($has_notifications)
        <x-larastrap::tabpane :id="sprintf('notifications-%s', sanitizeId($user->id))" tlabel="generic.menu.notifications" icon="bi-bell">
            <form class="form-horizontal inner-form" method="POST" action="{{ route('users.notifications', $user->id) }}">
                <div class="row">
                    <div class="col-md-4">
                        <p>{{ __('texts.user.help.notifications_instructions') }}</p>
                        <ul class="list-group">
                            @foreach(App\Supplier::orderBy('name', 'asc')->get() as $supplier)
                                <li class="list-group-item">
                                    {{ $supplier->name }}
                                    <span class="float-end">
                                        <x-larastrap::scheck name="suppliers[]" :value="$supplier->id" :checked="$user->suppliers->where('id', $supplier->id)->first() != null" />
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="row mt-2">
                <div class="col">
                <div class="btn-group float-end main-form-buttons" role="group">
                <button type="submit" class="btn btn-success saving-button">{{ __('texts.generic.save') }}</button>
                </div>
                </div>
                </div>
            </form>
        </x-larastrap::tabpane>
    @endif
</x-larastrap::tabs>

<div class="postponed">
    @stack('postponed')
</div>
