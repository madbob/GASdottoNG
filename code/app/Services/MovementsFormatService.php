<?php

/*
    Servizio interamente dedicato alla formattazione dei movimenti contabili,
    nelle loro numerose forme
*/

namespace App\Services;

use App\Gas;

class MovementsFormatService extends BaseService
{
    private function balanceKey($peer, $operation, $classmap)
    {
        $class = get_class($peer->getActualObject());
        if ($class != Gas::class && isset($classmap[$class])) {
            $balance_type = $classmap[$class];
        }
        else {
            $balance_type = $operation->field;
        }

        return sprintf('%s_%s', $operation->operation, $balance_type);
    }

    private function formatMovementAsBalance($movement, $gas, $classmap, $reference_row)
    {
        $ret = $reference_row;
        $ops = $movement->operations();

        foreach (['sender', 'target'] as $peer) {
            foreach ($ops->$peer->operations as $op) {
                $key = $this->balanceKey($movement->$peer, $op, $classmap);
                if (isset($ret[$key])) {
                    $ret[$key] = $movement->amount;
                }
            }
        }

        if (! empty($ops->master->operations)) {
            foreach ($ops->master->operations as $op) {
                $key = $this->balanceKey($gas, $op, $classmap);
                if (isset($ret[$key])) {
                    $ret[$key] = $movement->amount;
                }
            }
        }

        return $ret;
    }

    public function formatAsBalance($movements)
    {
        $filename = sanitizeFilename(__('export.balance_csv_filename', ['date' => date('d/m/Y')]));

        $headers = [
            __('movements.registration_date'),
            __('movements.execution_date'),
            __('generic.type'),
            __('generic.payment'),
            __('generic.identifier'),
            __('generic.notes'),
            __('movements.paying'),
            __('movements.payed'),
        ];

        $balance_type = [];
        $gas = currentAbsoluteGas();
        $reference_row = [];
        $classmap = [];

        $fields = $gas->extendedBalanceFields();
        foreach ($fields as $field_id => $field_meta) {
            $classmap[$field_meta->class] = $field_id;

            $headers[] = __('movements.formatted_revenues', ['name' => $field_meta->label]);
            $reference_row['increment_' . $field_id] = '';
            $headers[] = __('movements.formatted_expenses', ['name' => $field_meta->label]);
            $reference_row['decrement_' . $field_id] = '';
        }

        return output_csv($filename, $headers, $movements, function ($mov) use ($gas, $classmap, $reference_row) {
            $row = [];
            $row[] = $mov->registration_date;
            $row[] = $mov->date;
            $row[] = $mov->printableType();
            $row[] = $mov->printablePayment();
            $row[] = $mov->identifier;
            $row[] = $mov->sender ? $mov->sender->printableName() : '';
            $row[] = $mov->target ? $mov->target->printableName() : '';
            $row[] = $mov->notes;

            $row = array_merge($row, $this->formatMovementAsBalance($mov, $gas, $classmap, $reference_row));

            return $row;
        });
    }
}
