<?php

namespace App\Services;

use DB;

use App\Gas;
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
                'operations' => [],
            ],
            'target' => (object) [
                'operations' => [],
            ],
            'master' => (object) [
                'operations' => [],
            ],
        ];

        array_push($data, $cell);

        return $cell;
    }

    private function parseRules(&$data, $role, $classname, $request)
    {
        $payments = paymentTypes();
        $valid_payments = array_intersect_key($request, $payments);
        $fields = (new $classname())->balanceFields();

        foreach (array_keys($fields) as $f) {
            foreach (array_keys($valid_payments) as $pay_id) {
                $conf = $request[$role . '-' . $classname . '-' . $f . '-' . $pay_id] ?? 'ignore';
                if ($conf != 'ignore') {
                    $cell = null;

                    foreach ($data as $d) {
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
                        'field' => $f,
                    ];
                }
            }
        }
    }

    private function fixVoidMethods(&$data, $request)
    {
        $payments = paymentTypes();
        $valid_payments = array_intersect_key($request, $payments);

        foreach (array_keys($valid_payments) as $pay_id) {
            $exists = array_filter($data, function ($d) use ($pay_id) {
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

        if (!$type->system) {
            $type->sender_type = empty($request['sender_type']) ? null : $request['sender_type'];
            $type->target_type = empty($request['target_type']) ? null : $request['target_type'];
        }

        $data = [];

        if ($type->sender_type != null) {
            $this->parseRules($data, 'sender', $type->sender_type, $request);
        }

        if ($type->target_type != null) {
            $this->parseRules($data, 'target', $type->target_type, $request);
        }

        if ($type->sender_type != Gas::class && $type->target_type != Gas::class) {
            $this->parseRules($data, 'master', Gas::class, $request);
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
