<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class GasFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = 'GAS-' . fake()->name();
        return [
            'name' => $name,
            'message' => "Questa istanza permette di avere una idea del funzionamento di {$name}.\n\nPer accedere:\nUtente amministratore: username: root, password: password\nUtente non privilegiato: username: user, password: password\n\nL'inoltro di messaggi email da questa istanza è deliberatamente disabilitato, per evitare abusi.\n\nQuesta istanza viene quotidianamente rinnovata con le ultimissime modifiche (al contrario delle istanze hostate su gasdotto.net, sulle quali viene condotto qualche test in più prima della pubblicazione). GASdottoNG è un progetto in continua evoluzione: se noti qualcosa che non va, o una funzione che manca, mandaci una mail a info@madbob.org",
        ];
    }
}
