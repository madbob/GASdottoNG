<?php

use Digitick\Sepa\TransferFile\Factory\TransferFileFacadeFactory;
use Digitick\Sepa\PaymentInformation;

$directDebit = TransferFileFacadeFactory::createDirectDebit(md5(time() + rand()), $currentgas->name);

$directDebit->addPaymentInfo('1', array(
    'id'                    => '1',
    'dueDate'               => new DateTime('now +30 days'),
    'creditorName'          => $currentgas->name,
    'creditorAccountIBAN'   => $currentgas->iban,
    'seqType'               => PaymentInformation::S_ONEOFF,
    'creditorId'            => $currentgas->iban
));

foreach($users as $user) {
    $amount = $user->current_balance_amount;

    if ($amount < 0 && !empty($user->iban)) {
        $directDebit->addTransfer('1', array(
            'amount'                => round($amount * -1, 2),
            'debtorIban'            => $user->iban,
            'debtorName'            => $user->printableName(),
            'debtorMandate'         => 'AB12345',
            'debtorMandateSignDate' => '13.10.2012',
            'remittanceInformation' => 'Versamento GAS ' . $currentgas->name,
            'endToEndId'            => $user->id . '::' . $user->username
        ));
    }
}

echo $directDebit->asXML();
