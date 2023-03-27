<x-larastrap::modal :title="_i('Elimina Saldo Passato')" size="lg">
	<x-larastrap::iform classes="form-inline iblock" :action="route('movements.deletebalance', $id)" :buttons="[['color' => 'danger', 'label' => _i('Elimina'), 'attributes' => ['type' => 'submit']]]">
		<input type="hidden" name="reload-whole-page" value="1">
		<input type="hidden" name="pre-saved-function" value="passwordProtected">

		<div class="alert alert-danger">
			<p>
				{{ _i("Attenzione! I saldi passati possono essere rimossi ma con prudenza, l'operazione non è reversibile, e non sarà più possibile ricalcolare questi valori in nessun modo!") }}
			</p>
		</div>
	</x-larastrap::iform>
</x-larastrap::modal>
