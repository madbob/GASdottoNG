<x-larastrap::modal size="lg">
	<x-larastrap::iform classes="form-inline iblock" :action="route('movements.deletebalance', $id)" :buttons="[['color' => 'danger', 'tlabel' => 'generic.remove', 'attributes' => ['type' => 'submit']]]">
		<input type="hidden" name="reload-whole-page" value="1">
		<input type="hidden" name="pre-saved-function" value="passwordProtected">

		<div class="alert alert-danger">
			<p>
				{{ __('texts.movements.help.removing_balance_warning') }}
			</p>
		</div>
	</x-larastrap::iform>
</x-larastrap::modal>
