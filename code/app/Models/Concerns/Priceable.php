<?php

/*
    Questa classe fornisce un livello di astrazione nei confronti di prodotti e
    varianti. Nella maggior parte dei casi funge da mero wrapper per le relative
    funzioni delle relative classi, ma può essere utilizzata per sovrascriverli
    temporaneamente (e.g. se sto rivedendo le consegne di un ordine chiuso, i
    cui prezzi dei prodotti sono nel frattempo cambiati).
    È importante che il ciclo di trattamento delle riduzioni e dei modificatori
    utilizzi la funzione getPrice() di questa classe per ottenere il prezzo
    dell'oggetto desiderato e adottare effettivamente questa astrazione.
    Per le funzioni relative all'accesso dei prezzi storicizzati nel contesto di
    un Order, si consulti l'implementazione della funzione realPrice() dei
    modelli che usano questo trait
*/

namespace App\Models\Concerns;

trait Priceable
{
    private $hard_price = null;

    public function setPrice($price)
    {
        $this->hard_price = $price;
    }

    public function getPrice($rectify = false)
    {
        if (is_null($this->hard_price)) {
            return $this->realPrice($rectify);
        }
        else {
            return $this->hard_price;
        }
    }

    public function copyPrice($obj)
    {
        $this->hard_price = $obj->hard_price;
    }

    public abstract function realPrice($rectify);
}
