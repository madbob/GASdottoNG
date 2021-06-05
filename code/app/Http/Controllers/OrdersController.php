<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Arr;

use DB;
use Log;
use App;
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

    private function resetOlderDates($order)
    {
        $last_date = $order->shipping ? $order->shipping : $order->end;

        Date::where('target_type', 'App\Supplier')->where('target_id', $order->supplier_id)->where('date', '<=', $last_date)->delete();

        $recurrings = Date::where('target_type', 'App\Supplier')->where('target_id', $order->supplier_id)->where('recurring', '!=', '')->get();
        foreach($recurrings as $d) {
            $data = json_decode($d->recurring);
            if ($data) {
                $data->from = date('Y-m-d', strtotime($last_date . ' +1 days'));
                if ($data->to <= $data->from) {
                    $d->delete();
                }
                else {
                    $d->recurring = json_encode($data);
                    $d->save();
                }
            }
            else {
                Log::error('Broken date description: ' . $d->recurring);
            }
        }
    }

    public function rss(Request $request)
    {
        $aggregates = Aggregate::getByStatus(null, 'open');

        $feed = App::make("feed");
        $feed->title = _i('Ordini Aperti');
        $feed->description = _i('Ordini Aperti');
        $feed->link = $request->url();
        $feed->setDateFormat('datetime');

        if ($aggregates->isEmpty() == false)
            $feed->pubdate = date('Y-m-d G:i:s');
        else
            $feed->pubdate = '1970-01-01 00:00:00';

        foreach($aggregates as $aggregate) {
            $summary = '';

            foreach($aggregate->orders as $order) {
                $summary .= $order->printableName() . "<br>\n";
                foreach($order->products as $product)
                    $summary .= $product->printableName() . "<br>\n";
                $summary .= "<br>\n";
            }

            $feed->add(
                $aggregate->printableName(),
                $aggregate->gas->first()->printableName(),
                $aggregate->getBookingURL(),
                $aggregate->updated_at,
                nl2br($summary),
                ''
            );
        }

        return $feed->render('rss');
    }

    public function ical()
    {
        $calendar = new \Eluceo\iCal\Component\Calendar(currentAbsoluteGas()->printableName());

        $orders = Aggregate::defaultOrders(false);
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

    public function index(Request $request)
    {
        $user = $request->user();
        $orders = Aggregate::defaultOrders(!$user->can('order.view', $user->gas));
        return view('pages.orders', ['orders' => $orders]);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        $a = new Aggregate();
        $suppliers = Arr::wrap($request->input('supplier_id'));

        if (count($suppliers) == 1) {
            $order_comment = $request->input('comment');
        }
        else {
            $order_comment = '';
            $a->comment = $request->input('comment');
        }

        $a->save();

        $deliveries = array_filter($request->input('deliveries', []));
        $start = decodeDate($request->input('start'));
        $end = decodeDate($request->input('end'));
        $shipping = decodeDate($request->input('shipping'));
        $keep_open_packages = $request->input('keep_open_packages');

        foreach($suppliers as $supplier_id) {
            $supplier = Supplier::findOrFail($supplier_id);
            if ($request->user()->can('supplier.orders', $supplier) == false) {
                continue;
            }

            $o = new Order();
            $o->supplier_id = $supplier->id;

            $now = date('Y-m-d');
            $o->comment = $order_comment;
            $o->start = $start;
            $o->end = $end;
            $o->shipping = $shipping;
            $o->status = $request->input('status');
            $o->keep_open_packages = $keep_open_packages;

            $o->aggregate_id = $a->id;
            $o->save();

            foreach($supplier->modifiers as $mod) {
                $new_mod = $mod->replicate();
                $new_mod->target_id = $o->id;
                $new_mod->target_type = get_class($o);
                $new_mod->save();
            }

            $o->deliveries()->sync($deliveries);
            $o->products()->sync($supplier->products()->where('active', '=', true)->get());

            $this->resetOlderDates($o);
        }

        return $this->commonSuccessResponse($a);
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        $order = Order::findOrFail($id);
        if ($request->user()->can('supplier.orders', $order->supplier) == false) {
            return $this->errorResponse(_i('Non autorizzato'));
        }

        if ($request->has('comment')) {
            $order->comment = $request->input('comment');
        }
        if ($request->has('start')) {
            $order->start = decodeDate($request->input('start'));
        }
        if ($request->has('end')) {
            $order->end = decodeDate($request->input('end'));
        }
        if ($request->has('shipping')) {
            $order->shipping = decodeDate($request->input('shipping'));
        }

        $order->deliveries()->sync(array_filter($request->input('deliveries', [])));
        $order->users()->sync($request->input('users', []));

        /*
            Se un ordine viene riaperto, modifico artificiosamente la sua data
            di chiusura. Questo per evitare che venga nuovamente automaticamente
            chiuso
        */
        $status = $request->input('status');
        if ($order->status != $status) {
            $today = date('Y-m-d');
            if ($status == 'open' && $order->end < $today) {
                $order->end = $today;
            }

            $order->status = $status;
        }

        $order->keep_open_packages = $request->input('keep_open_packages');

        $order->save();

        $new_products = [];
        $enabled = $request->input('enabled', []);
        $prices = $request->input('product_price', []);
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
                if ($prod->price != $prices[$i] || $prod->max_available != $availables[$i]) {
                    $prod->price = $prices[$i];
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
                if ($booking->products->isEmpty()) {
                    $booking->delete();
                }
            }

            $order->products()->sync($new_products);
        }

        if ($order->shipping) {
            Date::where('target_type', 'App\Supplier')->where('target_id', $order->supplier_id)->where('date', '<=', $order->shipping)->delete();
        }

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
        if ($order->hasProduct($product) == false) {
            abort(404);
        }

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

    public function exportModal(Request $request, $id, $type)
    {
        $order = Order::findOrFail($id);
        return view('order.export' . $type, ['order' => $order]);
    }

    public function document(Request $request, $id, $type)
    {
        $order = Order::findOrFail($id);

        switch ($type) {
            case 'shipping':
                $send_mail = $request->has('send_mail');
                $subtype = $request->input('format', 'pdf');
                $status = $request->input('status');
                $required_fields = $request->input('fields', []);
                $fields = splitFields($required_fields);

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

                $shipping_place = $request->input('shipping_place', 'all_by_place');
                if ($shipping_place == 'all_by_place') {
                    $shipping_place = null;
                }

                if ($send_mail) {
                    $temp_file_path = $order->document('summary', $subtype, 'save', $required_fields, $status, $shipping_place);
                    $this->sendDocumentMail($request, $temp_file_path);
                }
                else {
                    return $order->document('summary', $subtype, 'return', $required_fields, $status, $shipping_place);
                }

                break;

            case 'table':
                $status = $request->input('status', 'booked');
                $shipping_place = $request->input('shipping_place', 0);

                $contents = [];

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
