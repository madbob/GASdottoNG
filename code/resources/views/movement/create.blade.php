<x-larastrap::modal>
    <x-larastrap::iform classes="movement-modal" method="POST" :action="route('movements.store')">
        <input type="hidden" name="void-form" value="1">
        <input type="hidden" name="test-feedback" value="1">
        <input type="hidden" name="close-modal" value="1">
        <input type="hidden" name="update-list" value="movement-list">
        <input type="hidden" name="reload-portion" value=".balance-summary">
        <input type="hidden" name="post-saved-function[]" value="refreshFilter">
        @include('movement.base-edit', ['movement' => null])
    </x-larastrap::iform>
</x-larastrap::modal>
