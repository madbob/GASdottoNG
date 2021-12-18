<?php

namespace App\Parameters\Constraints;

abstract class Constraint
{
    public function hardContraint()
    {
        return true;
    }

    /*
        I Constraints possono essere hard o soft. Nel secondo caso sono dei
        semplici suggerimenti, che comportano la visualizzazione di un messaggio
        ma non l'interruzione dell'operazione (e.g. quantità prenotate oltre il
        massimo consigliato).
        Pertanto qui li ordino affinché possano essere valutati prima quelli
        hard e poi, se non sono state sollevate eccezioni bloccanti, quelli soft
    */
    public static function sortedContraints()
    {
        $constraints = systemParameters('Constraints');

        $sorted_contraints = [
            0 => [],
            1 => [],
        ];

        foreach($constraints as $constraint) {
            if ($constraint->hardContraint()) {
                $sorted_contraints[0][] = $constraint;
            }
            else {
                $sorted_contraints[1][] = $constraint;
            }
        }

        return $sorted_contraints;
    }

    public abstract function printable($product, $order);
    public abstract function test($booked, $quantity);
}
