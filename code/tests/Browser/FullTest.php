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
            ->mainScreenshot('profilo');

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
            ->mainScreenshot('utenti')
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
        /*
            Devono essere in ordine alfabetico, ovvero nell'ordine in cui
            appaiono nel pannello della prenotazione
        */
        return [
            ['Finocchi', 3.00, 'chili', 'verdura'],
            ['Melanzane', 2.00, 'chili', 'verdura'],
            ['Peperoncino piccante', 0.50, 'pezzi', 'verdura'],
            ['Zucchine', 2.50, 'chili', 'verdura'],
        ];
    }

    private function disableEnableSupplier($browser, $supplier_name)
    {
        $supplier_id = Str::slug($supplier_name);

        $browser->visitRoute('suppliers.index')
            ->waitForText('Amministra Categorie')
            ->click('.accordion-item[data-element-id="' . $supplier_id . '"]')->waitForText('Dettagli')
            ->mainScreenshot('fornitore')
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

        // $this->disableEnableSupplier($browser, $suppliers[0][0]);

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
                    $browser->scrollIntoView('#category_admin');

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
                        $browser->mainScreenshot('prodotto');
                    }
                }
            });
    }

    private function testSuppliersTools($browser)
    {
        /*
            Amministrazione categorie
        */
        $browser->visitRoute('suppliers.index')
            ->waitForText('Amministra Categorie')
            ->press('#category_admin')->waitForText('Clicca e trascina le categorie nell\'elenco')
            ->mainScreenshot('categorie')
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
            ->mainScreenshot('unita_misura')
            ->click('.add-row')
            ->pause(100)
            ->typeAtXPath('//*/table/tbody/tr[last() - 1]/td/input', 'Pippo')
            ->press('Salva')
            ->pause(500)
            ->click('#unit_measure_admin')->waitForText('Unità Discreta')
            ->assertInputValueAtXPath('//*/input[@value="pippo"]/following-sibling::td/input', 'Pippo');
    }

    private function createOrders($browser)
    {
        $suppliers = $this->suppliers();
        $supplier_name = $suppliers[0][0];
        $supplier_id = Str::slug($supplier_name);

        $browser->visitRoute('orders.index')
            ->waitForText('Aggrega Ordini')
            ->press('Crea Nuovo Ordine')->waitForText('Fornitore')
            ->type('start', printableDate(date('Y-m-d')))
            ->type('end', printableDate(date('Y-m-d', strtotime('+10 days'))))
            ->type('shipping', printableDate(date('Y-m-d', strtotime('+15 days'))))
            ->select('supplier_id', $supplier_id)
            ->press('Salva')
            ->waitForText('Consegne');
    }

    private function doBookings($browser)
    {
        $products = $this->products();

        $total = 0;
        $quantities = [];
        foreach($products as $product) {
            $quantities[] = rand(0, 5);
        }

        /*
            Salvataggio prenotazione, e controllo
        */

        $browser->visitRoute('bookings.index')
            ->waitForText('Puoi modificare')
            ->clickAtXPath('//*/div[contains(@class, "accordion-item")][1]/h2/button')
            ->waitForText('La Mia Prenotazione')
            ->with('.accordion-collapse.show', function($panel) use ($products, $quantities, &$total) {
                foreach($quantities as $index => $quantity) {
                    $product_total = sprintf('%.02f', $products[$index][1] * $quantity);
                    $panel->pause(200)
                        ->typeAtXPath('//*/table/tbody/tr[contains(@class, "booking-product")][' . ($index + 1) . ']/td//input[@type="text"]', $quantity)
                        ->pause(100)
                        ->assertSeeAtXPath('//*/table/tbody/tr[contains(@class, "booking-product")][' . ($index + 1) . ']/td//label[contains(@class, "booking-product-price")]/span', $product_total);
                    $total += $product_total;
                }

                $panel->assertSeeIn('.booking-total', sprintf('%.02f', $total));
            })
            ->mainScreenshot('prenotazione')
            ->press('Salva')
            ->pause(500);

        $browser->visitRoute('orders.index')
            ->waitForText('Aggrega Ordini')
            ->clickAtXPath('//*/div[contains(@class, "accordion-item")][1]/h2/button')->waitForText('Consegne')
            ->mainScreenshot('ordine')
            ->scrollIntoView('.order-summary-order-price')->assertSeeIn('.order-summary-order-price', sprintf('%.02f €', $total));

        /*
            Annullamento prenotazione, e controllo
        */

        $browser->visitRoute('bookings.index')
            ->waitForText('Puoi modificare')
            ->clickAtXPath('//*/div[contains(@class, "accordion-item")][1]/h2/button')
            ->waitForText('La Mia Prenotazione')
            ->press('Annulla Prenotazione')
            ->acceptDialog()
            ->press('Salva')
            ->pause(500);

        $browser->visitRoute('orders.index')
            ->waitForText('Aggrega Ordini')
            ->clickAtXPath('//*/div[contains(@class, "accordion-item")][1]/h2/button')->waitForText('Consegne')
            ->scrollIntoView('.order-summary-order-price')->assertSeeIn('.order-summary-order-price', '0.00 €');
    }

    private function createNotification($browser)
    {
        $browser->visitRoute('notifications.index')
            ->waitForText('Crea Nuovo Notifica')
            ->press('Crea Nuovo Notifica')->waitForText('Tipo')
            ->type('content', 'Questa è una prova')
            ->type('start_date', printableDate(date('Y-m-d')))
            ->type('end_date', printableDate(date('Y-m-d', strtotime('+15 days'))))
            ->press('Salva')
            ->waitForText('Questa è una prova');
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
            $this->testSuppliersTools($browser);
            $this->createOrders($browser);
            $this->doBookings($browser);
            $this->createNotification($browser);
        });
    }
}
