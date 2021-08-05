<x-larastrap::modal :title="_i('Conferma Operazione')" id="password-protection-dialog" size="md">
    <x-larastrap::form method="POST" :action="url('dashboard/verify')">
        <div class="alert alert-danger">
            {{ _i('Per confermare questa operazione devi immettere la tua password utente') }}
        </div>

        <br/>

        <div class="form-group">
            <div class="col-md-12">
                <input type="password" name="password" class="form-control">
            </div>
        </div>
    </x-larastrap::form>
</x-larastrap::modal>
