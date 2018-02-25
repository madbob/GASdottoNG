<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use DB;
use Auth;

use App\Movement;
use App\MovementType;

class MovementTypesController extends Controller
{
    public function __construct()
    {
        $this->commonInit([
            'reference_class' => 'App\\MovementType'
        ]);
    }

    public function index()
    {
        $user = Auth::user();
        if ($user->can('movements.types', $user->gas) == false) {
            abort(503);
        }

        return view('movementtypes.admin', ['types' => MovementType::types()]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if ($user->can('movements.types', $user->gas) == false) {
            abort(503);
        }

        DB::beginTransaction();

        $type = new MovementType();
        $type->name = $request->input('name');
        $type->allow_negative = $request->has('allow_negative');
        $type->sender_type = $request->input('sender_type');
        $type->target_type = $request->input('target_type');
        $type->function = '[]';
        $type->save();

        return $this->commonSuccessResponse($type);
    }

    public function show(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->can('movements.types', $user->gas) == false) {
            abort(503);
        }

        $type = MovementType::types($id);
        $type->id = $id;
        return view('movementtypes.edit', ['type' => $type]);
    }

    private function parseRules(&$data, $role, $classname, $request)
    {
        $payments = MovementType::payments();
        $fields = $classname::balanceFields();

        foreach($fields as $f => $fieldname) {
            foreach($payments as $pay_id => $pay) {
                if ($request->has($pay_id) == false)
                    continue;

                $conf = $request->input($classname . '-' . $f . '-' . $pay_id, 'ignore');
                if ($conf != 'ignore') {
                    $cell = null;

                    foreach($data as $d) {
                        if ($d->method == $pay_id) {
                            $cell = $d;
                            break;
                        }
                    }

                    if ($cell == null) {
                        $cell = (object) [
                            'method' => $pay_id,
                            'is_default' => ($request->input('payment_default', null) == $pay_id),
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
                    }

                    $cell->$role->operations[] = (object) [
                        'operation' => $conf,
                        'field' => $f
                    ];
                }
            }
        }
    }

    public function fixVoidMethods(&$data, $request)
    {
        $payments = MovementType::payments();

        foreach($payments as $pay_id => $pay) {
            if ($request->has($pay_id) == false)
                continue;

            $found = false;
            foreach($data as $d) {
                if ($d->method == $pay_id) {
                    $found = true;
                    break;
                }
            }

            if ($found == false) {
                $cell = (object) [
                    'method' => $pay_id,
                    'is_default' => ($request->input('payment_default', null) == $pay_id),
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
            }
        }
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->can('movements.types', $user->gas) == false) {
            abort(503);
        }

        DB::beginTransaction();

        $type = MovementType::findOrFail($id);
        $type->name = $request->input('name');
        $type->allow_negative = $request->has('allow_negative');
        $type->default_notes = $request->input('default_notes');;

        if ($type->system == false) {
            $sender_type = $request->input('sender_type');
            if (!empty($sender_type))
                $type->sender_type = $sender_type;
            else
                $type->sender_type = null;

            $target_type = $request->input('target_type');
            if (!empty($target_type))
                $type->target_type = $target_type;
            else
                $type->target_type = null;
        }

        $data = [];

        if($type->sender_type != null)
            $this->parseRules($data, 'sender', $type->sender_type, $request);
        if($type->target_type != null && $type->target_type != $type->sender_type)
            $this->parseRules($data, 'target', $type->target_type, $request);
        if($type->sender_type != 'App\Gas' && $type->target_type != 'App\Gas')
            $this->parseRules($data, 'master', 'App\Gas', $request);

        /*
            Questo è per permettere l'esistenza di metodo di pagamento che non
            agiscono affatto sui saldi
        */
        $this->fixVoidMethods($data, $request);

        $type->function = json_encode($data);

        $type->save();

        return $this->commonSuccessResponse($type);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        if ($user->can('movements.types', $user->gas) == false) {
            abort(503);
        }

        DB::beginTransaction();

        $type = MovementType::findOrFail($id);
        $existing = Movement::where('type', $id)->count();
        if ($existing == 0)
            $type->forceDelete();
        else
            $type->delete();

        return $this->successResponse();
    }
}
