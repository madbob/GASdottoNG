<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\MovementTypesService;

class MovementTypesController extends BackedController
{
    public function __construct(MovementTypesService $service)
    {
        $this->commonInit([
            'reference_class' => 'App\\MovementType',
            'service' => $service
        ]);
    }

    public function show($id)
    {
        return $this->easyExecute(function() use ($id) {
            $type = $this->service->show($id);
            return view('movementtypes.edit', ['type' => $type]);
        });
    }

	public function brokenModifier(Request $request, $id)
	{
		return view('movementtypes.modal_broken_modifier', [
			'id' => $id,
		]);
	}

	public function postFeedback(Request $request, $id)
    {
        $ret = [];

		if ($id == 'booking-payment') {
			foreach(movementTypes() as $type) {
				if ($type->hasBrokenModifier()) {
					$ret[] = route('movtypes.notifybrokenmodifier', ['id' => 'booking-payment']);
					break;
				}
			}
		}
		else {
			$type = $this->service->show($id);
			if ($type->hasBrokenModifier()) {
				$ret[] = route('movtypes.notifybrokenmodifier', ['id' => $type]);
			}
        }

        return response()->json($ret);
    }
}
