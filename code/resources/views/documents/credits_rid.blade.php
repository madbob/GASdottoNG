<?php

use Digitick\Sepa\TransferFile\Factory\TransferFileFacadeFactory;
use Digitick\Sepa\PaymentInformation;

$directDebit = TransferFileFacadeFactory::createDirectDebit(md5(time() + rand()), $currentgas->name);

$directDebit->addPaymentInfo('1', array(
    'id'                    => '1',
    'dueDate'               => new DateTime('now +30 days'),
    'creditorName'          => $currentgas->name,
    'creditorAccountIBAN'   => $currentgas->rid['iban'],
    'seqType'               => PaymentInformation::S_ONEOFF,
    'creditorId'            => $currentgas->rid['id'],
));

foreach($users as $user) {
    $amount = $user->current_balance_amount;

    if ($amount < 0 && !empty($user->rid['iban'])) {
        $directDebit->addTransfer('1', array(
            'amount'                => round($amount * -1, 2),
            'debtorIban'            => $user->rid['iban'],
            'debtorName'            => $user->printableName(),
            'debtorMandate'         => $user->rid['id'],
            'debtorMandateSignDate' => date('d.m.Y', strtotime($user->rid['date'])),
            'remittanceInformation' => 'Versamento GAS ' . $currentgas->name,
            'endToEndId'            => $user->id . '::' . $user->username
        ));
    }
}

echo $directDebit->asXML();
