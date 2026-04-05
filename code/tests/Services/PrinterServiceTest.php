<?php

namespace Tests\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Printers\Order as OrderPrinter;
use App\Printers\Aggregate as AggregatePrinter;
use App\Printers\PrintParams;
use App\Role;
use App\Supplier;

class PrinterServiceTest extends TestCase
{
    use DatabaseTransactions;

    private function documentTypes()
    {
        return ['shipping', 'summary', 'table'];
    }

    public function test_printing_order()
    {
        $this->actingAs($this->userAdmin);

        $order = $this->initOrder(null);
        $this->populateOrder($order);

        $document_types = $this->documentTypes();

        foreach($document_types as $type) {
            $url = route('orders.export', [
                'id' => $order->id,
                'type' => $type,
            ]);

            $response = $this->actingAs($this->userAdmin)->get($url);
            $response->assertStatus(200);
        }

        $printer = new OrderPrinter();

        foreach(['pdf', 'csv'] as $format) {
            $params = [
                'action' => 'save',
                'format' => $format,
                'fields' => ['lastname', 'firstname', 'name', 'quantity', 'price'],
                'status' => 'booked',
                'isolate_friends' => false,
            ];

            foreach($document_types as $type) {
                $file = $printer->document($order, $type, $params);
                $this->assertTrue(file_exists($file));
                @unlink($file);
            }
        }
    }

    public function test_printing_aggregate()
    {
        $this->actingAs($this->userAdmin);

        $order = $this->initOrder(null);
        $this->populateOrder($order);

        $document_types = $this->documentTypes();

        foreach($document_types as $type) {
            $url = route('aggregates.export', [
                'id' => $order->aggregate->id,
                'type' => $type,
            ]);

            $response = $this->actingAs($this->userAdmin)->get($url);
            $response->assertStatus(200);
        }

        $printer = new AggregatePrinter();

        foreach(['pdf', 'csv'] as $format) {
            $params = [
                'action' => 'save',
                'format' => $format,
                'fields' => ['lastname', 'firstname', 'name', 'quantity', 'price'],
                'status' => 'booked',
                'isolate_friends' => false,
            ];

            foreach($document_types as $type) {
                $file = $printer->document($order->aggregate, $type, $params);
                $this->assertTrue(file_exists($file));
                @unlink($file);
            }
        }
    }
}
