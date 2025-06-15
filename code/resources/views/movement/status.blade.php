@include('movement.summary', ['obj' => $obj])

<div class="float-end">
    @if($currentuser->can('movements.admin', $currentgas) || $currentuser->can('movements.view', $currentgas))
        <div class="form-inline iblock inner-form">
            <x-larastrap::ambutton tlabel="movements.balances_history" :data-modal-url="route('movements.history', inlineId($obj))" />
        </div>
    @endif

    @if(get_class($obj) == 'App\Gas')
        <x-larastrap::iform classes="form-inline iblock" id="recalculate-account" method="POST" :action="url('/movements/recalculate')" :buttons="[['attributes' => ['type' => 'submit'], 'color' => 'danger', 'label' => __('texts.movements.recalculate_balances')]]">
            <input type="hidden" name="pre-saved-function" value="passwordProtected">
            <input type="hidden" name="post-saved-function" value="displayRecalculatedBalances">
        </x-larastrap::iform>

        <x-larastrap::modal id="display-recalculated-balance-modal">
            <p>
                {{ __('texts.generic.finished_operation') }}
            </p>
            <div class="hidden alert alert-danger broken">
                <p>
                    {{ __('texts.movements.help.balances_diff') }}
                </p>
                <br>
                <table class="table" id="broken_balances">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('texts.generic.subject') }}</th>
                            <th scope="col">{{ __('texts.generic.before') }}</th>
                            <th scope="col">{{ __('texts.generic.after') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
            <div class="hidden alert alert-success fixed">
                <p>
                    {{ __('texts.movements.help.balances_same') }}
                </p>
            </div>
        </x-larastrap::modal>

        <div class="iblock">
            <div class="form-group">
                <x-larastrap::mbutton tlabel="movements.balances_archive" triggers_modal="#close-balance-modal" classes="btn-danger" />
            </div>

            <x-larastrap::modal id="close-balance-modal">
                <p>{{ __('texts.movements.help.archiviation_notice') }}</p>

                <hr>

                <x-larastrap::iform id="close-balance" :action="url('/movements/close')">
                    <input type="hidden" name="reload-whole-page" value="1">
                    <input type="hidden" name="pre-saved-function" value="passwordProtected">
                    <x-larastrap::datepicker name="date" defaults_now="true" tlabel="generic.closing_date" />
                </x-larastrap::iform>
            </x-larastrap::modal>
        </div>
    @endif
</div>
