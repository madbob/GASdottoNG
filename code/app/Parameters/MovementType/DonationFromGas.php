<?php

namespace App\Parameters\MovementType;

class DonationFromGas extends GasExpense
{
    public function identifier()
    {
        return 'donation-from-gas';
    }

    public function initNew($type)
    {
        $type = parent::initNew($type);
        $type->name = __('movements.defaults.donation_from');

        return $type;
    }
}
