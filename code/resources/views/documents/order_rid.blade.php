<?php

$gas_abi = iban_split($currentgas->iban, 'abi');
$gas_cab = iban_split($currentgas->iban, 'cab');
$gas_account = iban_split($currentgas->iban, 'account');
$stream_id = sprintf('%s%s%s%s', $currentgas->rid_code, $gas_abi, date('dmy'), strtoupper(str_random(20)));

/*
    I pagamenti scadono dopo 30 giorni
*/
$expiry = date ('dmy', time () + (30 * 24 * 60 * 60));

$supplier_name = strtoupper($order->supplier->printableName());

$rows = 1;
$block = 0;
$block_head = sprintf('%07d', $block);
$gran_total = 0;

echo ' IR' . $stream_id . str_pad('', 67) . '00000  E ' . $gas_abi . "\n";

foreach($order->bookings as $booking) {
    if($booking->payment == null && !empty($booking->user->iban)) {
        try {
            $block++;
            $block_head = sprintf('%07d', $block);

            $total = $booking->total_value * 100;
            $gran_total += $total;

            $user = $booking->user;

            if (empty($user->sepa_first)) {
                $seq_id = 'FRST';
                $user->sepa_first = date('Y-m-d');
                $user->save();
            }
            else {
                $seq_id = "RCUR";
            }

            $user_abi = iban_split($user->iban, 'abi');
            $user_cab = iban_split($user->iban, 'cab');

            echo ' 10' . $block_head . str_pad('', 12) . $expiry . '50000' . sprintf('%013d', $total * 100) . '-' . $gas_abi . $gas_cab . $gas_account . $user_abi . $user_cab . str_pad('', 12) . $currentgas->rid_code . '4' . str_pad ($user->id, 16, '0', STR_PAD_LEFT) . str_pad('', 6) . 'E' . "\n";
            echo ' 17' . $block_head . str_pad(strtoupper($user->iban), 27) . $seq_id . date('dmy', strtotime($user->sepa_first)) . str_pad('', 73) . "\n";
            echo ' 20' . $block_head . str_pad(strtoupper($currentgas->rid_name), 110) . "\n";
            echo ' 30' . $block_head . str_pad(strtoupper($user->printableName()), 110 ) . "\n";

            list($street, $city, $cap) = $user->getAddress();
            echo ' 40' . $block_head . str_pad(strtoupper($street), 30) . str_pad(strtoupper($cap), 5) . str_pad(strtoupper($city), 25) . str_pad('', 50) . "\n";

            echo ' 50' . $block_head . str_pad('PAGAMENTO ORDINE ' . $supplier_name, 110) . "\n";
            echo ' 70' . $block_head . str_pad('', 110) . "\n";

            $rows += 6;
        }
        catch(\Exception $e) {
            Log::error('Impossibile generare riga per RID/SEPA: ' . $e->getMessage());
            $block--;
        }
    }
}

echo ' EF' . $stream_id . str_pad('', 6) . $block_head . sprintf('%015d', $gran_total * 100) . sprintf('%022d', $rows + 1) . str_pad('', 24) . 'E' . str_pad('', 6) . "\n";
