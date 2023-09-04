<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;
use Log;

use FeedIo\FeedIo;
use FeedIo\Feed;
use FeedIo\Feed\Item\Author;
use FeedIo\Factory\Builder\GuzzleClientBuilder;

use App\Services\OrdersService;
use App\Services\BookingsService;
use App\Printers\Order as Printer;
use App\Product;
use App\Order;
use App\Booking;
use App\Aggregate;

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

class OrdersController extends BackedController
{
    public function __construct(OrdersService $service)
    {
        $this->service = $service;

        $this->commonInit([
            'reference_class' => 'App\\Order',
            'service' => $service,
        ]);
    }

    public function rss(Request $request)
    {
        $aggregates = getOrdersByStatus(null, 'open');

        $feed = new Feed();
        $feed->setTitle(_i('Ordini Aperti'));
        $feed->setDescription(_i('Ordini Aperti'));
        $feed->setLink($request->url());

        if ($aggregates->isEmpty() == false) {
            $feed->setLastModified(new \DateTime());
        }

        foreach($aggregates as $aggregate) {
            if ($aggregate->gas->isEmpty()) {
                continue;
            }

            $summary = '';

            foreach($aggregate->orders as $order) {
                $summary .= $order->printableName() . "\n";

                foreach($order->products as $product) {
                    $summary .= $product->printableName() . "\n";
                }

                $summary .= "\n";
            }

			$author = new Author();
			$author->setName($aggregate->gas->first()->printableName());

			$item = $feed->newItem();
            $item->setTitle($aggregate->printableName());
            $item->setAuthor($author);
            $item->setLink($aggregate->getBookingURL());
            $item->setLastModified($aggregate->updated_at);
            $item->setContent($summary);
			$feed->add($item);
        }

		$feedIo = new FeedIo((new GuzzleClientBuilder())->getClient(), Log::getLogger());
        return $feedIo->getPsrResponse($feed, 'rss');
    }

    public function ical()
    {
        $events = [];

        $orders = defaultOrders(false);
        foreach($orders as $o) {
            if ($o->start && $o->end) {
				$event = (new \Eluceo\iCal\Domain\Entity\Event())
					->setSummary($o->printableName())
					->setOccurrence(
						new \Eluceo\iCal\Domain\ValueObject\MultiDay(
							new \Eluceo\iCal\Domain\ValueObject\Date(new \DateTime($o->start)),
							new \Eluceo\iCal\Domain\ValueObject\Date(new \DateTime($o->end)),
						)
					);

				$events[] = $event;
            }
        }

        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="ordini.ics"');

        $calendar = new \Eluceo\iCal\Domain\Entity\Calendar($events);
        $componentFactory = new \Eluceo\iCal\Presentation\Factory\CalendarFactory();
        $calendarComponent = $componentFactory->createCalendar($calendar);
        return (string) $calendarComponent;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $orders = defaultOrders(!$user->can('order.view', $user->gas));

        return view('pages.orders', [
            'orders' => $orders,
            'has_old' => $this->oldOrders($user)->count() != 0,
        ]);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();
        $format = $request->input('format', 'summary');
        $order = $this->service->show($id);

        if ($format == 'summary') {
            $master_summary = $order->aggregate->reduxData();

            if ($request->user()->can('supplier.orders', $order->supplier)) {
                return view('order.summary', ['order' => $order, 'master_summary' => $master_summary]);
            }
            else {
                return view('order.summary_ro', ['order' => $order, 'master_summary' => $master_summary]);
            }
        }
        else {
            abort(404);
        }
    }

    private function oldOrders($user)
    {
        $supplier_id = [];

        foreach($user->targetsByAction('supplier.modify') as $supplier) {
            $supplier_id[] = $supplier->id;
        }

        foreach($user->targetsByAction('supplier.orders') as $supplier) {
            $supplier_id[] = $supplier->id;
        }

        $supplier_id = array_unique($supplier_id);

        return easyFilterOrders($supplier_id, '1970-01-01', date('Y-m-d', strtotime('-1 years')), ['open', 'closed']);
    }

    /*
        Questa funzione è usata per aggiornare manualmente le quantità
        di un certo prodotto all'interno di un ordine
    */
    public function getFixes(Request $request, $id, $product_id)
    {
        $order = $this->service->show($id, true);

        $product = Product::findOrFail($product_id);
        if ($order->hasProduct($product) == false) {
            abort(404);
        }

        return view('order.fixes', ['order' => $order, 'product' => $product]);
    }

    public function postFixes(Request $request, $id)
    {
        DB::beginTransaction();

        $order = $this->service->show($id, true);
        $product_id = $request->input('product', []);
        $bookings = $request->input('booking', []);
        $quantities = $request->input('quantity', []);
        $notes = $request->input('notes') ?? '';
        $order->products()->updateExistingPivot($product_id, ['notes' => $notes]);

        for ($i = 0; $i < count($bookings); ++$i) {
            $booking_id = $bookings[$i];

            $booking = Booking::find($booking_id);
            if (is_null($booking) || $booking->order->id != $id) {
                continue;
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

    /*
        Questa funzione viene eventualmente attivata da
        AggregatesController::postFeedback()
    */
    public function getFixModifiers(Request $request, $id)
    {
        $order = $this->service->show($id, true);
        return view('order.fixemodifiers', ['order' => $order]);
    }

    public function postFixModifiers(Request $request, $id)
    {
        $action = $request->input('action');
        $this->service->fixModifiers($id, $action);
        return $this->successResponse();
    }

    public function search(Request $request)
    {
        $startdate = decodeDate($request->input('startdate'));
        $enddate = decodeDate($request->input('enddate'));
        $status = $request->input('status');
        $supplier_id = $request->input('supplier_id');
        $orders = easyFilterOrders($supplier_id, $startdate, $enddate, $status);

        return view('commons.loadablelist', [
            'identifier' => !empty($supplier_id) ? 'order-list-' . $supplier_id : 'order-list',
            'items' => $orders,
            'legend' => (object)[
                'class' => Aggregate::class
            ],
            'sorting_rules' => [
                'supplier_name' => _i('Fornitore'),
                'start' => _i('Data Apertura'),
                'end' => _i('Data Chiusura'),
                'shipping' => _i('Data Consegna'),
            ],
        ]);
    }

    public function exportModal(Request $request, $id, $type)
    {
        $order = $this->service->show($id);
        return view('order.export' . $type, ['order' => $order]);
    }

    public function document(Request $request, $id, $type)
    {
        $printer = new Printer();
        $order = Order::findOrFail($id);
        return $printer->document($order, $type, $request->all());
    }
}
