<?php

namespace App\Services;

use DB;

use App\Exceptions\AuthException;

use App\Movement;
use App\MovementType;

class MovementTypesService extends BaseService
{
    public function show($id)
    {
        $this->ensureAuth(['movements.types' => 'gas']);
        $type = movementTypes($id);
        $type->id = $id;
        return $type;
    }

    public function store(array $request)
    {
        $this->ensureAuth(['movements.types' => 'gas']);

        DB::beginTransaction();
        $type = new MovementType();
        $this->setIfSet($type, $request, 'name');
        $this->boolIfSet($type, $request, 'allow_negative');
        $this->setIfSet($type, $request, 'sender_type');
        $this->setIfSet($type, $request, 'target_type');
        $type->function = '[]';
        $type->save();
        DB::commit();
        movementTypes('VOID');
        return $type;
    }

    private function newFunction($pay_id, $request, &$data)
    {
        $cell = (object) [
            'method' => $pay_id,
            'is_default' => (($request['payment_default'] ?? null) == $pay_id),
            'sender' => (object) [
                'operations' => []
            ],
            'target' => (object) [
                'operations' => []
            ],
            'master' => (object) [
                'operations' => []
            ],
        ];

        array_push($data, $cell);
        return $cell;
    }

    private function parseRules(&$data, $role, $classname, $request)
    {
        $payments = paymentTypes();
        $fields = $classname::balanceFields();

        foreach($fields as $f => $fieldname) {
            foreach($payments as $pay_id => $pay) {
                if (isset($request[$pay_id]) == false) {
                    continue;
                }

                $conf = $request[$classname . '-' . $f . '-' . $pay_id] ?? 'ignore';
                if ($conf != 'ignore') {
                    $cell = null;

                    foreach($data as $d) {
                        if ($d->method == $pay_id) {
                            $cell = $d;
                            break;
                        }
                    }

                    if (is_null($cell)) {
                        $cell = $this->newFunction($pay_id, $request, $data);
                    }

                    $cell->$role->operations[] = (object) [
                        'operation' => $conf,
                        'field' => $f
                    ];
                }
            }
        }
    }

    private function fixVoidMethods(&$data, $request)
    {
        $payments = paymentTypes();

        foreach($payments as $pay_id => $pay) {
            if (isset($request[$pay_id]) == false) {
                continue;
            }

            $exists = array_filter($data, function($d) use ($pay_id) {
                return $d->method == $pay_id;
            });

            if (empty($exists)) {
                $this->newFunction($pay_id, $request, $data);
            }
        }
    }

    public function update($id, array $request)
    {
        $this->ensureAuth(['movements.types' => 'gas']);

        DB::beginTransaction();

        $type = MovementType::findOrFail($id);
        $this->setIfSet($type, $request, 'name');
        $this->boolIfSet($type, $request, 'allow_negative');
        $this->setIfSet($type, $request, 'default_notes');

        if ($type->system == false) {
            $sender_type = $request['sender_type'];
            if (!empty($sender_type))
                $type->sender_type = $sender_type;
            else
                $type->sender_type = null;

            $target_type = $request['target_type'];
            if (!empty($target_type))
                $type->target_type = $target_type;
            else
                $type->target_type = null;
        }

        $data = [];

        if ($type->sender_type != null) {
            $this->parseRules($data, 'sender', $type->sender_type, $request);
        }

        if ($type->target_type != null && $type->target_type != $type->sender_type) {
            $this->parseRules($data, 'target', $type->target_type, $request);
        }

        if ($type->sender_type != 'App\Gas' && $type->target_type != 'App\Gas') {
            $this->parseRules($data, 'master', 'App\Gas', $request);
        }

        /*
            Questo è per permettere l'esistenza di metodo di pagamento che non
            agiscono affatto sui saldi
        */
        $this->fixVoidMethods($data, $request);

        $type->function = json_encode($data);
        $type->save();
        movementTypes('VOID');
        return $type;
    }

    public function destroy($id)
    {
        $this->ensureAuth(['movements.types' => 'gas']);

        DB::beginTransaction();

        $type = MovementType::findOrFail($id);
        $existing = Movement::where('type', $id)->count();
        if ($existing == 0) {
            $type->forceDelete();
        }
        else {
            $type->delete();
        }

        DB::commit();
        movementTypes('VOID');
        return $type;
    }
}
