<div class="modal fade" id="delete-confirm-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form class="form-horizontal {{ $password_protected ? 'password-protected' : '' }}" method="POST" action="" id="form-delete-confirm-modal">
                <input type="hidden" name="_method" value="delete">
                @include('commons.extrafields')

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Elimina</h4>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        Sei sicuro di voler eliminare questo elemento?
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-danger">Conferma</button>
                </div>
            </form>
        </div>
    </div>
</div>
