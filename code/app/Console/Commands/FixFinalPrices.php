<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Booking;

class FixFinalPrices extends Command
{
    protected $signature = 'fix:finalprices';
    protected $description = 'Per aggiustare i valori salvati sul DB dei prodotti consegnati';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $bookings = Booking::where('status', 'shipped')->get();
        foreach($bookings as $booking) {
            foreach($booking->products as $product) {
                if ($product->delivered != 0 && $product->final_price == 0) {
                    $product->final_price = $product->deliveredValue();
                    $product->save();
                }

                foreach($product->variants as $variant) {
                    if ($variant->delivered != 0 && $variant->final_price == 0) {
                        $variant->final_price = $variant->deliveredValue();
                        $variant->save();
                    }
                }
            }
        }
    }
}
