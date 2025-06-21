<?php

/*
    Questa classe è strettamente legata a CreditableTrait, anche se
    rappresentano due cose leggermente diverse.

    PayableTrait viene usato da tutti i soggetti che possono essere soggetti di
    un movimento contabile (Movement), e wrappa l'accesso a suddetti movimenti.
    CreditableTrait viene usato da tutti i soggetti di cui viene tenuta traccia
    di un bilancio (Balance), e wrappa l'accesso a suddetti bilanci.
    Tutti i CreditableTrait sono anche PayableTrait, ma non viceversa.

    In fase di editing dei tipi di movimento contabile devo interagire
    contemporaneamente con bilanci e destinazioni dei movimenti contabili. Per
    far quadrare i conti, le classi visualizzate sono spesso quelle che usano
    PayableTrait anche se impropriamente le uso come se fossero come
    CreditableTrait. Questo è il motivo percui Booking o Order implementano
    funzioni di CreditableTrait pur non usando quel tratto
*/

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;

use App\Movement;

trait PayableTrait
{
    public function movements(): MorphMany
    {
        return $this->morphMany(Movement::class, 'target');
    }

    public function deleteMovements()
    {
        foreach ($this->movements as $mov) {
            $mov->delete();
        }
    }

    private function initQueryMovements($query)
    {
        if (is_null($query)) {
            $query = Movement::orderBy('created_at', 'desc');
        }

        return $query;
    }

    public function queryMovements($query = null, $type = 'all')
    {
        $id = $this->id;
        $class = get_class($this);
        $query = $this->initQueryMovements($query);

        switch ($type) {
            case 'all':
                $query->where(function ($query) use ($id, $class) {
                    $query->where(function ($query) use ($id, $class) {
                        $query->where('sender_type', $class)->where('sender_id', $id);
                    })->orWhere(function ($query) use ($id, $class) {
                        $query->where('target_type', $class)->where('target_id', $id);
                    });
                });
                break;

            case 'sender':
                $query->where(function ($query) use ($id, $class) {
                    $query->where('sender_type', $class)->where('sender_id', $id);
                });
                break;

            case 'target':
                $query->where(function ($query) use ($id, $class) {
                    $query->where('target_type', $class)->where('target_id', $id);
                });
                break;

            default:
                throw new \InvalidArgumentException("Tipo di filtro non riconosciuto per ricerca movimenti contabili: " . $type);
        }

        return $query;
    }
}
