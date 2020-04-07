<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;
use Auth;
use PDF;
use Mail;

use App\Supplier;
use App\Product;
use App\Order;
use App\Aggregate;
use App\Date;
use App\Booking;
use App\BookedProduct;
use App\Notifications\GenericOrderShipping;

/*
    Attenzione, quando si maneggia in questo file bisogna ricordare la
    distinzione tra Ordine e Aggregato.
    Un Ordine fa riferimento ad un fornitore (per il quale l'utente può
    avere o no permessi di modifica) e contiene dei prodotti. Un Aggregato è
    un insieme di Ordini.
    Per comodità, qui si assume che tutti gli Ordini siano sempre parte di
    un Aggregato, anche se contiene solo l'Ordine stesso. In alcuni casi gli
    ID passati come parametro alle funzioni fanno riferimento ad un Ordine,
    in altri casi ad un Aggregato.
*/

class OrdersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $this->commonInit([
            'reference_class' => 'App\\Order'
        ]);
    }

    private function defaultOrders($mine)
    {
        if ($mine) {
            $user = Auth::user();
            $supplier_id = [];

            foreach($user->targetsByAction('supplier.modify') as $supplier)
                $supplier_id[] = $supplier->id;
            foreach($user->targetsByAction('supplier.orders') as $supplier)
                $supplier_id[] = $supplier->id;
            foreach($user->targetsByAction('supplier.shippings') as $supplier)
                $supplier_id[] = $supplier->id;

            $supplier_id = array_unique($supplier_id);
        }
        else {
            $supplier_id = 0;
        }

        return Aggregate::easyFilter($supplier_id, date('Y-m-d', strtotime('-1 years')), date('Y-m-d', strtotime('+1 years')), ['open', 'closed', 'shipped', 'suspended']);
    }

    private function resetOlderDates($order)
    {
        $last_date = $order->shipping ? $order->shipping : $order->end;

        Date::where('target_type', 'App\Supplier')->where('target_id', $order->supplier_id)->where('date', '<=', $last_date)->delete();

        $recurrings = Date::where('target_type', 'App\Supplier')->where('target_id', $order->supplier_id)->where('recurring', '!=', '')->get();
        foreach($recurrings as $d) {
            $data = json_decode($d->recurring);
            $data->from = date('Y-m-d', strtotime($last_date . ' +1 days'));
            if ($data->to <= $data->from) {
                $d->delete();
            }
            else {
                $d->recurring = json_encode($data);
                $d->save();
            }
        }
    }

    public function ical()
    {
        $calendar = new \Eluceo\iCal\Component\Calendar('www.example.com');

        $orders = $this->defaultOrders(false);
        foreach($orders as $o) {
            if ($o->start && $o->end) {
                $event = new \Eluceo\iCal\Component\Event();
                $event->setDtStart(new \DateTime($o->start))->setDtEnd(new \DateTime($o->end))->setNoTime(true)->setSummary($o->printableName());
                $calendar->addComponent($event);
            }
        }

        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="ordini.ics"');
        echo $calendar->render();
    }

    public function index()
    {
        $orders = $this->defaultOrders(true);
        return view('pages.orders', ['orders' => $orders]);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        $supplier = Supplier::findOrFail($request->input('supplier_id', -1));
        if ($request->user()->can('supplier.orders', $supplier) == false) {
            return $this->errorResponse(_i('Non autorizzato'));
        }

        $o = new Order();
        $o->supplier_id = $request->input('supplier_id');

        $now = date('Y-m-d');
        $o->comment = $request->input('comment');
        $o->start = decodeDate($request->input('start'));
        $o->end = decodeDate($request->input('end'));
        $o->shipping = decodeDate($request->input('shipping'));
        $o->status = $request->input('status');
        $o->keep_open_packages = $request->has('keep_open_packages') ? true : false;

        $a = new Aggregate();
        $a->save();

        $o->aggregate_id = $a->id;
        $o->save();

        $o->products()->sync($supplier->products()->where('active', '=', true)->get());

        $this->resetOlderDates($o);

        return $this->commonSuccessResponse($a);
    }

    public function show(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $summary = $order->calculateSummary();
        return response()->json($summary);
    }

    public function recalculate(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $final_products = [];
        $enabled = $request->input('enabled', []);
        $prices = $request->input('product_price', []);
        $transports = $request->input('product_transport', []);
        $availables = $request->input('product_max_available', []);
        $products = $request->input('productid');

        for ($i = 0; $i < count($products); ++$i) {
            $id = $products[$i];

            foreach ($enabled as $en) {
                if ($en == $id) {
                    $prod = Product::find($id);
                    $prod->price = $prices[$i];
                    $prod->transport = $transports[$i];
                    $prod->max_available = $availables[$i];
                    $final_products[] = $prod;
                    break;
                }
            }
        }

        $summary = $order->calculateSummary($final_products);
        return response()->json($summary);
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        $order = Order::findOrFail($id);
        if ($request->user()->can('supplier.orders', $order->supplier) == false) {
            return $this->errorResponse(_i('Non autorizzato'));
        }

        if ($request->has('comment'))
            $order->comment = $request->input('comment');
        if ($request->has('start'))
            $order->start = decodeDate($request->input('start'));
        if ($request->has('end'))
            $order->end = decodeDate($request->input('end'));
        if ($request->has('shipping'))
            $order->shipping = decodeDate($request->input('shipping'));
        if ($request->has('discount'))
            $order->discount = savingPercentage($request, 'discount');
        if ($request->has('transport'))
            $order->transport = savingPercentage($request, 'transport');

        /*
            Se un ordine viene riaperto, modifico artificiosamente la sua data
            di chiusura. Questo per evitare che venga nuovamente automaticamente
            chiuso
        */
        $status = $request->input('status');
        if ($order->status != $status) {
            $today = date('Y-m-d');
            if ($status == 'open' && $order->end < $today)
                $order->end = $today;

            $order->status = $status;
        }

        $order->keep_open_packages = $request->has('keep_open_packages') ? true : false;

        $order->save();

        $new_products = [];
        $enabled = $request->input('enabled', []);
        $prices = $request->input('product_price', []);
        $transports = $request->input('product_transport', []);
        $availables = $request->input('product_max_available', []);
        $products = $request->input('productid');

        if (count($prices)) {
            for ($i = 0; $i < count($products); ++$i) {
                $id = $products[$i];

                foreach ($enabled as $en) {
                    if ($en == $id) {
                        $new_products[] = $id;
                        break;
                    }
                }

                $prod = Product::find($id);
                if ($prod->price != $prices[$i] || $prod->transport != $transports[$i] || $prod->max_available != $availables[$i]) {
                    $prod->price = $prices[$i];
                    $prod->transport = $transports[$i];
                    $prod->max_available = $availables[$i];
                    $prod->save();
                }
            }

            /*
                Se vengono rimossi dei prodotti dall'ordine, ne elimino tutte le
                relative prenotazioni sinora avvenute
            */
            $removed_products = $order->products()->whereNotIn('id', $new_products)->pluck('id')->toArray();
            foreach($order->bookings as $booking) {
                $booking->products()->whereIn('product_id', $removed_products)->delete();
                if ($booking->products->isEmpty())
                    $booking->delete();
            }

            $order->products()->sync($new_products);
        }

        $discounted = $request->input('discounted', []);
        foreach ($order->products as $product) {
            $dis = false;

            foreach ($discounted as $en) {
                if ($en == $product->id) {
                    $dis = true;
                    break;
                }
            }

            if ($product->pivot->discount_enabled != $dis) {
                $order->products()->updateExistingPivot($product->id, ['discount_enabled' => $dis]);
            }
        }

        if ($order->shipping)
            Date::where('target_type', 'App\Supplier')->where('target_id', $order->supplier_id)->where('date', '<=', $order->shipping)->delete();

        return $this->commonSuccessResponse($order->aggregate);
    }

    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();

        $order = Order::findOrFail($id);
        if ($request->user()->can('supplier.orders', $order->supplier) == false) {
            return $this->errorResponse(_i('Non autorizzato'));
        }

        foreach($order->bookings as $booking)
            $booking->deleteMovements();
        $order->deleteMovements();

        $aggregate_id = $order->aggregate_id;

        $order->delete();

        $aggregate = Aggregate::find($aggregate_id);
        if ($aggregate->orders()->count() <= 0)
            $aggregate->delete();

        return $this->successResponse();
    }

    /*
        Questa funzione è usata per aggiornare manualmente le quantità
        di un certo prodotto all'interno di un ordine
    */
    public function getFixes(Request $request, $id, $product_id)
    {
        $order = Order::findOrFail($id);
        if ($request->user()->can('supplier.orders', $order->supplier) == false) {
            return $this->errorResponse(_i('Non autorizzato'));
        }

        $product = Product::findOrFail($product_id);
        if ($order->hasProduct($product) == false)
            abort(404);

        return view('order.fixes', ['order' => $order, 'product' => $product]);
    }

    public function postFixes(Request $request, $id)
    {
        DB::beginTransaction();

        $order = Order::findOrFail($id);
        if ($request->user()->can('supplier.orders', $order->supplier) == false) {
            return $this->errorResponse(_i('Non autorizzato'));
        }

        $product_id = $request->input('product', []);
        $bookings = $request->input('booking', []);
        $quantities = $request->input('quantity', []);
        $notes = $request->input('notes') ?? '';

        $order->products()->updateExistingPivot($product_id, ['notes' => $notes]);

        for ($i = 0; $i < count($bookings); ++$i) {
            $booking_id = $bookings[$i];

            $booking = Booking::find($booking_id);
            if (is_null($booking)) {
                continue;
            }

            if ($booking->order->id != $id) {
                return $this->errorResponse(_i('Non autorizzato'));
            }

            $product = $booking->getBooked($product_id, true);
            if ($product->exists && empty($quantities[$i])) {
                $product->delete();
            }
            else if (!empty($quantities[$i])) {
                $product->quantity = $quantities[$i];
                $product->save();
            }
        }

        return $this->successResponse();
    }

    public function search(Request $request)
    {
        $startdate = decodeDate($request->input('startdate'));
        $enddate = decodeDate($request->input('enddate'));
        $status = $request->input('status');
        $supplier_id = $request->input('supplier_id');
        $orders = Aggregate::easyFilter($supplier_id, $startdate, $enddate, $status);

        return view('commons.loadablelist', [
            'identifier' => !empty($supplier_id) ? 'order-list-' . $supplier_id : 'order-list',
            'items' => $orders,
            'legend' => (object)[
                'class' => 'Aggregate'
            ],
        ]);
    }

    private function sendDocumentMail($request, $temp_file_path)
    {
        $recipient_mails = $request->input('recipient_mail_value', []);
        if (empty($recipient_mails))
            return;

        $real_recipient_mails = [];
        foreach($recipient_mails as $rm) {
            if (empty($rm))
                continue;
            $real_recipient_mails[] = (object) ['email' => $rm];
        }

        if (empty($real_recipient_mails))
            return;

        $m = Mail::to($real_recipient_mails);
        $subject_mail = $request->input('subject_mail');
        $body_mail = $request->input('body_mail');
        $m->send(new GenericOrderShipping($temp_file_path, $subject_mail, $body_mail));

        @unlink($temp_file_path);
    }

    private function orderTopBookingsByShipping($order, $shipping_place, $status = null)
    {
        $bookings = $order->topLevelBookings($status);
        return Booking::sortByShippingPlace($bookings, $shipping_place);
    }

    public function document(Request $request, $id, $type)
    {
        $order = Order::findOrFail($id);

        switch ($type) {
            case 'shipping':
                $send_mail = $request->has('send_mail');
                $subtype = $request->input('format', 'pdf');
                $required_fields = $request->input('fields', []);
                $fields = splitFields($required_fields);
                $status = 'booked';

                $shipping_place = $request->input('shipping_place', 'all_by_name');
                $data = $order->formatShipping($fields, $status, $shipping_place);

                $title = _i('Dettaglio Consegne ordine %s presso %s', [$order->internal_number, $order->supplier->name]);
                $filename = sanitizeFilename($title . '.' . $subtype);
                $temp_file_path = sprintf('%s/%s', sys_get_temp_dir(), $filename);

                if ($subtype == 'pdf') {
                    $pdf = PDF::loadView('documents.order_shipping_pdf', ['fields' => $fields, 'order' => $order, 'data' => $data]);

                    if ($send_mail) {
                        $pdf->save($temp_file_path);
                    }
                    else {
                        return $pdf->download($filename);
                    }
                }
                else if ($subtype == 'csv') {
                    $flat_contents = [];

                    foreach($data->contents as $c) {
                        foreach($c->products as $p) {
                            $flat_contents[] = array_merge($c->user, $p);
                        }
                    }

                    if ($send_mail) {
                        output_csv($filename, $data->headers, $flat_contents, function($row) {
                            return $row;
                        }, $temp_file_path);
                    }
                    else {
                        return output_csv($filename, $data->headers, $flat_contents, function($row) {
                            return $row;
                        });
                    }
                }

                if ($send_mail) {
                    $this->sendDocumentMail($request, $temp_file_path);
                }

                break;

            case 'summary':
                $send_mail = $request->has('send_mail');
                $subtype = $request->input('format', 'pdf');
                $required_fields = $request->input('fields', []);
                $status = $request->input('status');

                $shipping_place = $request->input('shipping_place', 0);
                if ($shipping_place == '0')
                    $data = $order->formatSummary($required_fields, $status, null);
                else
                    $data = $order->formatSummary($required_fields, $status, $shipping_place);

                $title = _i('Prodotti ordine %s presso %s', [$order->internal_number, $order->supplier->name]);
                $filename = sanitizeFilename($title . '.' . $subtype);
                $temp_file_path = sprintf('%s/%s', sys_get_temp_dir(), $filename);

                if ($subtype == 'pdf') {
                    $pdf = PDF::loadView('documents.order_summary_pdf', ['order' => $order, 'data' => $data]);

                    if ($send_mail) {
                        $pdf->save($temp_file_path);
                    }
                    else {
                        return $pdf->download($filename);
                    }
                }
                else if ($subtype == 'csv') {
                    if ($send_mail) {
                        output_csv($filename, $data->headers, $data->contents, function($row) {
                            return $row;
                        }, $temp_file_path);
                    }
                    else {
                        return output_csv($filename, $data->headers, $data->contents, function($row) {
                            return $row;
                        });
                    }
                }

                if ($send_mail) {
                    $this->sendDocumentMail($request, $temp_file_path);
                }

                break;

            case 'table':
                $status = $request->input('status', 'booked');
                $shipping_place = $request->input('shipping_place', 0);

                if ($status == 'booked') {
                    $bookings = self::orderTopBookingsByShipping($order, $shipping_place);
                    $contents = view('documents.order_table_booked', ['order' => $order, 'bookings' => $bookings])->render();
                }
                else if ($status == 'delivered') {
                    $bookings = self::orderTopBookingsByShipping($order, $shipping_place);
                    $contents = view('documents.order_table_delivered', ['order' => $order, 'bookings' => $bookings])->render();
                }
                else if ($status == 'saved') {
                    $bookings = self::orderTopBookingsByShipping($order, $shipping_place, 'saved');
                    $contents = view('documents.order_table_saved', ['order' => $order, 'bookings' => $bookings])->render();
                }

                $filename = sanitizeFilename(_i('Tabella Ordine %s presso %s.csv', [$order->internal_number, $order->supplier->name]));
                return output_csv($filename, null, $contents, null, null);
                break;
        }
    }
}
