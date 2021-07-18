<x-larastrap::modal :title="_i('Crea Nuovo Movimento')">
    <x-larastrap::form classes="creating-form movement-modal" method="POST" :action="route('movements.store')">
        <input type="hidden" name="update-list" value="movement-list">
        <input type="hidden" name="post-saved-function[]" value="refreshFilter">
        <input type="hidden" name="post-saved-function[]" value="refreshBalanceView">
        @include('movement.base-edit', ['movement' => null])
    </x-larastrap::form>
</x-larastrap::modal>
