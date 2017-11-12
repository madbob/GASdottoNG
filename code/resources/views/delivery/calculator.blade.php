<?php $rand = rand() ?>

<div class="input-group-addon inline-calculator-trigger" data-toggle="modal" data-target="#calculator-modal-{{ $rand }}">
    <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
</div>

<div class="modal fade inline-calculator" id="calculator-modal-{{ $rand }}" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Calcola Quantità</h4>
            </div>
            <div class="modal-body">
                @for($i = 0; $i < $pieces; $i++)
                    <div class="form-group">
                        <div class="col-md-12">
                            <div class="input-group">
                                <input type="text" class="form-control number" autocomplete="off" value="0">
                                <div class="input-group-addon">{{ $measure }}</div>
                            </div>
                        </div>
                    </div>
                @endfor
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
                <button type="submit" class="btn btn-success">Salva</button>
            </div>
        </div>
    </div>
</div>
