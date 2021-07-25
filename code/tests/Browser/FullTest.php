<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class FullTest extends DuskTestCase
{
    use DatabaseMigrations;

    /*
        Questo è per eseguire lo unit test Dusk solo quando espressamente
        richiesto (ovvero: eseguendo il comando "php artisan dusk")
    */
    private function checkEnv()
    {
        return (env('DUSK_TESTING', false));
    }

    public function setUp(): void
    {
        if ($this->checkEnv() == false) {
            return;
        }

        parent::setUp();
        $this->artisan('db:seed');
    }

    public function testAll()
    {
        if ($this->checkEnv() == false) {
            $this->assertNotNull(true);
            return;
        }

        /*
            Questo serve a generare le stringhe delle date in italiano, per la
            corretta formattazione da parte di printableDate()
        */
        setlocale(LC_TIME, 'it_IT.UTF-8');

        $this->browse(function (Browser $browser) {
            /*
                Login
            */
            $browser->visitRoute('login')
                ->waitForText('GASdotto')
                ->type('username', 'root')
                ->type('password', 'root')
                ->press('Login')
                ->waitForLocation('/dashboard')
                ->assertPathIs('/dashboard')
                ->assertSee('Prenotazioni Aperte');

            /*
                Modifica profilo
            */
            $browser->visitRoute('profile')
                ->waitForText('Anagrafica')
                ->type('firstname', 'Mario')
                ->type('lastname', 'Rossi')
                ->type('birthday', 'Giovedì 01 Gennaio 1970')
                ->type('taxcode', 'RSSMRA70A01L219K')
                ->type('family_members', '2')
                ->clickLink('Aggiungi Nuovo')
                ->select('contact_type[]', 'email')
                ->type('contact_value[]', 'mario@mailinator.com')
                ->pressAndWaitFor('Salva');

            /*
                Controlla profilo
            */
            $browser->visitRoute('profile')
                ->waitForText('Anagrafica')
                ->assertInputValue('firstname', 'Mario')
                ->assertInputValue('lastname', 'Rossi')
                ->assertInputValue('taxcode', 'RSSMRA70A01L219K')
                ->screenshot('profilo');

            /*
                Modifica permessi
            */
            $browser->visit('/gas/senza-nome/edit')
                ->waitForText('Amministratore')
                ->press('Amministratore')
                ->waitForText('Avere sotto-utenti')
                ->pause(1000)
                ->click('input[type="checkbox"][data-action="users.subusers"]')
                ->pause(1000);

            /*
                Creazione amico
            */

            $browser->visitRoute('profile')
                ->waitForText('Amici')
                ->press('Amici')
                ->waitForText('Crea Nuovo Amico')
                ->press('Crea Nuovo Amico')
                ->pause(500)
                ->waitForText('Username')
                ->with('.modal.show', function($panel) {
                    $panel->typeSlowly('username', 'amico_di_mario')
                        ->typeSlowly('firstname', 'di Mario')
                        ->typeSlowly('lastname', 'Amico')
                        ->typeSlowly('password', 'amico_di_mario')
                        ->press('Salva');
                })
                ->waitForText('Amico di Mario');

            /*
                Creazione utenti
            */
            $users = [
                ['garibaldi', 'Giuseppe', 'Garibaldi'],
                ['verdi', 'Giuseppe', 'Verdi'],
                ['mazzini', 'Giuseppe', 'Mazzini'],
                ['azeglio', 'Massimo', "D'Azeglio"],
                ['pisacane', 'Carlo', 'Pisacane'],
                ['bandiera', 'Attilio', 'Bandiera'],
                ['benso', 'Camillo', 'Benso'],
            ];

            foreach($users as $user) {
                $browser->visitRoute('users.index')
                    ->waitForText('Importa CSV')
                    ->press('Crea Nuovo Utente')
                    ->pause(500)
                    ->waitForText('Username')
                    ->typeSlowly('username', $user[0])
                    ->typeSlowly('firstname', $user[1])
                    ->typeSlowly('lastname', $user[2])
                    ->uncheck('sendmail')
                    ->typeSlowly('password', $user[0])
                    ->press('Salva')
                    ->waitForText('Anagrafica');
            }

            /*
                Controllo utente
            */
            $browser->visitRoute('users.index')
                ->waitForText('Importa CSV')
                ->screenshot('utenti')
                ->click('.accordion-item[data-element-id="garibaldi"]')
                ->waitForText('Anagrafica')
                ->with('.accordion-item[data-element-id="garibaldi"]', function($panel) {
                    $panel->assertInputValue('firstname', 'Giuseppe')
                        ->assertInputValue('lastname', 'Garibaldi')
                        ->waitForText('Salva')
                        ->click('@status-deleted')
                        ->pause(500)
                        ->press('Salva');
                })
                ->waitUntilMissing('Anagrafica');

            /*
                Controllo cessazione utente
            */
            $browser->visitRoute('users.index')
                ->waitForText('Importa CSV')
                ->assertDontSee('Garibaldi')
                ->press('button[data-filter-attribute="deleted_at"]')
                ->waitForText('Garibaldi')
                ->assertSee('Garibaldi');

            /*
                Creazione fornitori
            */
            $suppliers = [
                ['Mele e Pere', '', '', '', ''],
                ['La Zucchina Dorata', 'Verdure di stagione, prenotazioni settimanali', 'Bonifico bancario IBAN IT01234567890', 'Mandare una mail con le prenotazioni a Luisa: zucchina@example.com', 'ITIT01234567890'],
                ['Panetteria da Pasquale', '', '', '', ''],
                ['Luigi il Macellaio', '', '', '', ''],
            ];

            foreach($suppliers as $supplier) {
                $browser->visitRoute('suppliers.index')
                    ->waitForText('Amministra Categorie')
                    ->press('Crea Nuovo Fornitore')
                    ->pause(500)
                    ->waitForText('Nome')
                    ->typeSlowly('name', $supplier[0])
                    ->typeSlowly('description', $supplier[1])
                    ->typeSlowly('payment_method', $supplier[2])
                    ->typeSlowly('order_method', $supplier[3])
                    ->typeSlowly('vat', $supplier[4])
                    ->press('Salva')
                    ->waitForText('Dettagli');
            }

            $browser->visitRoute('suppliers.index')
                ->waitForText('Amministra Categorie')
                ->click('.accordion-item[data-element-id="la-zucchina-dorata"]')
                ->waitForText('Dettagli')
                ->scrollIntoView('.navbar-expand-lg')
                ->pause(2000)
                ->screenshot('fornitore')
                ->with('.accordion-item[data-element-id="la-zucchina-dorata"]', function($panel) {
                    $panel->assertInputValue('name', 'La Zucchina Dorata')
                        ->waitForText('Salva')
                        ->click('@status-deleted')
                        ->pause(500)
                        ->press('Salva');
                })
                ->waitUntilMissing('Dettagli');

            /*
                Controllo cessazione fornitore
            */
            $browser->visitRoute('suppliers.index')
                ->waitForText('Amministra Categorie')
                ->assertDontSee('Zucchina')
                ->press('button[data-filter-attribute="deleted_at"]')
                ->waitForText('Zucchina')
                ->assertSee('Zucchina');

            echo date('Y-m-d') . ' = ' . printableDate(date('Y-m-d')) . "\n";
            echo date('Y-m-d', strtotime('+10 days')) . ' = ' . printableDate(date('Y-m-d', strtotime('+10 days'))) . "\n";
            echo date('Y-m-d', strtotime('+15 days')) . ' = ' . printableDate(date('Y-m-d', strtotime('+15 days'))) . "\n";

            /*
                Creazione ordini
            */
            foreach($suppliers as $supplier) {
                $browser->visitRoute('orders.index')
                    ->waitForText('Aggrega Ordini')
                    ->press('Crea Nuovo Ordine')
                    ->pause(500)
                    ->waitForText('Fornitore')
                    ->type('start', printableDate(date('Y-m-d')))
                    ->type('end', printableDate(date('Y-m-d', strtotime('+10 days'))))
                    ->type('shipping', printableDate(date('Y-m-d', strtotime('+15 days'))))
                    ->select('supplier_id', Str::slug($supplier[0]))
                    ->screenshot('ordine')
                    ->press('Salva')
                    ->waitForText('Consegne');
            }
        });
    }
}
