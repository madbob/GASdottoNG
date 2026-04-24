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
    private function cacheKey()
    {
        $id = inlineId($this);
        return 'forced-price-' . $id;
    }

    public function setPrice($price)
    {
        $key = $this->cacheKey();
        app()->make('TempCache')->put($key, $price);
    }

    public function getPrice($rectify = false)
    {
        $key = $this->cacheKey();
        $hard = app()->make('TempCache')->get($key);

        if (is_null($hard)) {
            return $this->realPrice($rectify);
        }
        else {
            return $hard;
        }
    }

    public function copyPrice($obj)
    {
        $cache = app()->make('TempCache');;

        $original_key = $obj->cacheKey();
        $price = $cache->get($original_key);

        $this_key = $this->cacheKey();
        $cache->put($this_key, $price);
    }

    abstract public function realPrice($rectify);
}
