<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/*
	Unico scopo di questa classe è fungere da alias nei confronti di
	OrdersController. Essa è routata sul path "aggregates", che funge da
	sinonimo
*/

class AggregatesController extends OrdersController
{
}
