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

    public function test_printing_order()
    {
        $this->actingAs($this->userAdmin);

        $order = $this->initOrder(null);
        $this->populateOrder($order);

        $printer = new OrderPrinter();

        foreach(['pdf', 'csv'] as $format) {
            $params = [
                'action' => 'save',
                'format' => $format,
                'fields' => ['lastname', 'firstname', 'name', 'quantity', 'price'],
                'status' => 'booked',
                'isolate_friends' => false,
            ];

            foreach(['shipping', 'summary', 'table'] as $type) {
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

        $printer = new AggregatePrinter();

        foreach(['pdf', 'csv'] as $format) {
            $params = [
                'action' => 'save',
                'format' => $format,
                'fields' => ['lastname', 'firstname', 'name', 'quantity', 'price'],
                'status' => 'booked',
                'isolate_friends' => false,
            ];

            foreach(['shipping', 'summary', 'table'] as $type) {
                $file = $printer->document($order->aggregate, $type, $params);
                $this->assertTrue(file_exists($file));
                @unlink($file);
            }
        }
    }
}
