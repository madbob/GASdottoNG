<div class="modal fade" id="password-protection-dialog" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form class="form-horizontal creating-form" method="POST" action="{{ url('dashboard/verify') }}">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Conferma Operazione</h4>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        Per confermare questa operazione devi confermarla immettendo la tua password utente
                    </div>

                    <br/>

                    <div class="form-group">
                        <div class="col-md-12">
                            <input type="password" name="password" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-success">Salva</button>
                </div>
            </form>
        </div>
    </div>
</div>
