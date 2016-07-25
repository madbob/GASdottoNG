<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Auth;
use DB;
use Theme;

use App\Movement;
use App\Aggregate;

class MovementsController extends Controller
{
        public function __construct()
	{
		$this->middleware('auth');
	}

        private function basicReadFromRequest($request)
	{
                $user = Auth::user();

                $id = $request->input('id');
                if (empty($id)) {
                        $obj = new Movement();
                }
                else {
                        $obj = Movement::find($id);
                        if ($obj == null)
                                $obj = new Movement();
                }

                $obj->registration_date = date('Y-m-d G:i:s');
                $obj->registerer_id = $user->id;
                $obj->sender_type = $request->input('sender_type');
                $obj->sender_id = $request->input('sender_id');
                $obj->target_type = $request->input('target_type');
                $obj->target_id = $request->input('target_id');
                $obj->amount = $request->input('amount');
                $obj->method = $request->input('method');
                $obj->type = $request->input('type');
                $obj->identifier = $request->input('identifier');
                $obj->notes = $request->input('notes');
                $obj->parseRequest($request);

                return $obj;
	}

        public function index(Request $request)
        {
                if ($request->has('start'))
                        $filtered = true;
                else
                        $filtered = false;

                if ($request->has('start'))
                        $start = $this->decodeDate($request->input('start'));
                else
                        $start = date('Y-m-d', strtotime('-1 months'));

                if ($request->has('end'))
                        $end = $this->decodeDate($request->input('end'));
                else
                        $end = date('Y-m-d');

                $data['movements'] = Movement::where('registration_date', '<=', $end)->where('registration_date', '>=', $start)->orderBy('registration_date', 'desc')->get();

                if ($filtered == false)
                        return Theme::view('pages.movements', $data);
                else
                        return Theme::view('movement.list', $data);
        }

        public function create(Request $request)
        {
                $type = $request->input('type');
                if ($type == 'none')
                        return '';

                $metadata = Movement::types($type);
                $data = [];

                $payments = [];
                $all_payments = Movement::payments();
                foreach($metadata->methods as $identifier => $info)
                        $payments[$identifier] = $all_payments[$identifier];

                $data['payments'] = $payments;
                $data['fixed'] = $metadata->fixed_value;

                $data['sender_type'] = $metadata->sender_type;
                if ($metadata->sender_type != null)
                        $data['senders'] = $metadata->sender_type::all();
                else
                        $data['senders'] = [];

                $data['target_type'] = $metadata->target_type;
                if ($metadata->target_type != null) {
                        if ($type == 'booking-payment') {
                                $data['targets'] = Aggregate::getByStatus('archived', true);
                                $data['target_type'] = 'App\Aggregate';
                        }
                        else {
                                $data['targets'] = $metadata->target_type::all();
                        }
                }
                else {
                        $data['targets'] = [];
                }

                return Theme::view('movement.selectors', $data);
        }

        public function store(Request $request)
	{
		DB::beginTransaction();

		$user = Auth::user();
		if ($user->gas->userCan('movements.admin') == false)
			return $this->errorResponse('Non autorizzato');

                $m = $this->basicReadFromRequest($request);
		$m->save();

		return $this->successResponse([
			'id' => $m->id,
                        'registration_date' => $m->printableDate('registration_date')
		]);
	}
}
