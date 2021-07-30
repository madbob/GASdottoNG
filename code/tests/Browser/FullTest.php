<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\VatRate;

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

    private function testProfile($browser)
    {
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
            ->clickLink('Aggiungi Nuovo')->select('contact_type[]', 'email')->type('contact_value[]', 'mario@mailinator.com')
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
            ->press('Amministratore')->waitForText('Avere sotto-utenti')
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
            ->press('Crea Nuovo Amico')->waitForText('Username')
            ->with('.modal.show', function($panel) {
                $panel->typeSlowly('username', 'amico_di_mario')
                    ->typeSlowly('firstname', 'di Mario')
                    ->typeSlowly('lastname', 'Amico')
                    ->typeSlowly('password', 'amico_di_mario')
                    ->press('Salva');
            })
            ->waitForText('Amico di Mario');
    }

    private function createUsers($browser)
    {
        $users = [
            ['garibaldi', 'Giuseppe', 'Garibaldi'],
            ['verdi', 'Giuseppe', 'Verdi'],
            ['mazzini', 'Giuseppe', 'Mazzini'],
            ['azeglio', 'Massimo', "D'Azeglio"],
            ['pisacane', 'Carlo', 'Pisacane'],
            ['bandiera', 'Attilio', 'Bandiera'],
            ['benso', 'Camillo', 'Benso'],
        ];

        /*
            Creazione utenti
        */

        foreach($users as $user) {
            $browser->visitRoute('users.index')
                ->waitForText('Importa CSV')
                ->press('Crea Nuovo Utente')->waitForText('Username')
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
    }

    private function suppliers()
    {
        return [
            ['La Zucchina Dorata', 'Verdure di stagione, prenotazioni settimanali', 'Bonifico bancario IBAN IT01234567890', 'Mandare una mail con le prenotazioni a Luisa: zucchina@example.com', 'ITIT01234567890'],
            ['Mele e Pere', '', '', '', ''],
            ['Panetteria da Pasquale', '', '', '', ''],
            ['Luigi il Macellaio', '', '', '', ''],
        ];
    }

    private function products()
    {
        return [
            ['Zucchine', 2.50, 'chili', 'verdura'],
            ['Melanzane', 2.00, 'chili', 'verdura'],
            ['Finocchi', 3.00, 'chili', 'verdura'],
            ['Peperoncino piccante', 0.50, 'pezzi', 'verdura'],
        ];
    }

    private function disableEnableSupplier($browser, $supplier_name)
    {
        $supplier_id = Str::slug($supplier_name);

        $browser->visitRoute('suppliers.index')
            ->waitForText('Amministra Categorie')
            ->click('.accordion-item[data-element-id="' . $supplier_id . '"]')->waitForText('Dettagli')
            ->scrollIntoView('.navbar-expand-lg')
            ->pause(1000)
            ->screenshot('fornitore')
            ->with('.accordion-item[data-element-id="' . $supplier_id . '"]', function($panel) use ($supplier_name) {
                $panel->assertInputValue('name', $supplier_name)
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
            ->assertDontSee($supplier_name)
            ->press('button[data-filter-attribute="deleted_at"]')
            ->waitForText($supplier_name)
            ->assertSee($supplier_name);

        $browser->visitRoute('suppliers.index')
            ->waitForText('Amministra Categorie')
            ->press('button[data-filter-attribute="deleted_at"]')
            ->click('.accordion-item[data-element-id="' . $supplier_id . '"]')->waitForText('Dettagli')
            ->with('.accordion-item[data-element-id="' . $supplier_id . '"]', function($panel) use ($supplier_name) {
                $panel->assertInputValue('name', $supplier_name)
                    ->waitForText('Salva')
                    ->click('@status-active')
                    ->pause(500)
                    ->press('Salva');
            })
            ->waitUntilMissing('Dettagli');
    }

    private function createSuppliers($browser)
    {
        $suppliers = $this->suppliers();

        /*
            Creazione fornitori
        */
        foreach($suppliers as $supplier) {
            $browser->visitRoute('suppliers.index')
                ->waitForText('Amministra Categorie')
                ->press('Crea Nuovo Fornitore')->waitForText('Nome')
                ->typeSlowly('name', $supplier[0])
                ->typeSlowly('description', $supplier[1])
                ->typeSlowly('payment_method', $supplier[2])
                ->typeSlowly('order_method', $supplier[3])
                ->typeSlowly('vat', $supplier[4])
                ->press('Salva')
                ->waitForText('Dettagli');
        }

        $this->disableEnableSupplier($browser, $suppliers[0][0]);

        $supplier_name = $suppliers[0][0];
        $supplier_id = Str::slug($supplier_name);

        /*
            Creazione prodotti
        */

        $products = $this->products();

        $browser->visitRoute('suppliers.index')
            ->waitForText('Amministra Categorie')
            ->click('.accordion-item[data-element-id="' . $supplier_id . '"]')->waitForText('Dettagli')
            ->press('Prodotti')
            ->pause(500)
            ->with('.accordion-item[data-element-id="' . $supplier_id . '"]', function($panel) use ($browser, $products) {
                $default_vat_rate = VatRate::where('percentage', 4)->first();

                foreach($products as $index => $product) {
                    $browser->scrollIntoView('.navbar-expand-lg');

                    $panel->press('Crea Nuovo Prodotto')->waitForText('Prezzo Unitario')
                        ->with('.modal.show', function($panel) use ($product, $default_vat_rate) {
                            $panel->typeSlowly('name', $product[0])
                                ->typeSlowly('price', $product[1])
                                ->select('measure_id', $product[2])
                                ->select('category_id', $product[3])
                                ->select('vat_rate_id', $default_vat_rate->id)
                                ->press('Salva');
                        })
                        ->waitForText($product[0]);

                    if ($index == 0) {
                        $browser->scrollIntoView('.navbar-expand-lg')->pause(500)->screenshot('prodotto');
                    }
                }
            });
    }

    private function createOrders($browser)
    {
        $suppliers = $this->suppliers();

        /*
            Creazione ordini
        */
        foreach($suppliers as $supplier) {
            $browser->visitRoute('orders.index')
                ->waitForText('Aggrega Ordini')
                ->press('Crea Nuovo Ordine')->waitForText('Fornitore')
                ->type('start', printableDate(date('Y-m-d')))
                ->type('end', printableDate(date('Y-m-d', strtotime('+10 days'))))
                ->type('shipping', printableDate(date('Y-m-d', strtotime('+15 days'))))
                ->select('supplier_id', Str::slug($supplier[0]))
                ->press('Salva')
                ->waitForText('Consegne');
        }
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

            $this->testProfile($browser);
            $this->createUsers($browser);
            $this->createSuppliers($browser);
            $this->createOrders($browser);

            /*
                Amministrazione categorie
            */
            $browser->visitRoute('suppliers.index')
                ->waitForText('Amministra Categorie')
                ->press('#category_admin')->waitForText('Clicca e trascina le categorie nell\'elenco')
                ->screenshot('categorie')
                ->type('input[name=new_category]', 'Pippo')->click('.dynamic-tree-add')
                ->pause(100)
                ->press('Salva')
                ->pause(500)
                ->press('#category_admin')->waitForText('Clicca e trascina le categorie nell\'elenco')
                ->assertInputValueAtXPath('//*[@id="pippo"]/div/input', 'Pippo');

            /*
                Amministrazione unità di misura
            */
            $browser->visitRoute('suppliers.index')
                ->waitForText('Amministra Categorie')
                ->click('#unit_measure_admin')->waitForText('Unità Discreta')
                ->screenshot('unita_misura')
                ->click('.add-row')
                ->pause(100)
                ->typeAtXPath('//*/table/tbody/tr[last() - 1]/td/input', 'Pippo')
                ->press('Salva')
                ->pause(500)
                ->click('#unit_measure_admin')->waitForText('Unità Discreta')
                ->assertInputValueAtXPath('//*/input[@value="pippo"]/following-sibling::td/input', 'Pippo');
        });
    }
}
