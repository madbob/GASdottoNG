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
            ->typeSlowly('firstname', 'Mario', 50)
            ->typeSlowly('lastname', 'Rossi', 50)
            ->typeSlowly('birthday', 'Sabato 03 Gennaio 1970', 50)
            ->typeSlowly('taxcode', 'RSSMRA70A01L219K', 50)
            ->typeSlowly('family_members', '2', 50)
            ->clickLink('Aggiungi Nuovo')->select('contact_type[]', 'email')->typeSlowly('contact_value[]', 'mario@mailinator.com', 50)
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
                $panel->typeSlowly('username', 'amico_di_mario', 50)
                    ->typeSlowly('firstname', 'di Mario', 50)
                    ->typeSlowly('lastname', 'Amico', 50)
                    ->typeSlowly('password', 'amico_di_mario', 50)
                    ->press('Salva');
            })
            ->waitForText('Amico di Mario');
    }

    private function createUsers($browser)
    {
        $users = [
            ['garibaldi', 'Giuseppe', 'Garibaldi'],
            ['verdi', 'Giuseppe', 'Verdi'],
            // ['mazzini', 'Giuseppe', 'Mazzini'],
            // ['azeglio', 'Massimo', "D'Azeglio"],
            // ['pisacane', 'Carlo', 'Pisacane'],
            // ['bandiera', 'Attilio', 'Bandiera'],
            // ['benso', 'Camillo', 'Benso'],
        ];

        /*
            Creazione utenti
        */

        foreach($users as $user) {
            $browser->visitRoute('users.index')
                ->waitForText('Importa CSV')
                ->press('Crea Nuovo Utente')->waitForText('Username')
                ->typeSlowly('username', $user[0], 50)
                ->typeSlowly('firstname', $user[1], 50)
                ->typeSlowly('lastname', $user[2], 50)
                ->uncheck('sendmail')
                ->typeSlowly('password', $user[0], 50)
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
            ['La Zucchina Dorata', 'Verdure di stagione, prenotazioni settimanali', 'Bonifico bancario IBAN IT01234567890', 'Mandare una mail con le prenotazioni a Luisa: zucchina@example.com', 'IT01234567890'],
            ['Mele e Pere', '', '', '', ''],
            // ['Panetteria da Pasquale', '', '', '', ''],
            // ['Luigi il Macellaio', '', '', '', ''],
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

    private function quantities()
    {
        static $quantities = null;

        if (is_null($quantities)) {
            $quantities = [];
            $products = $this->products();
            foreach($products as $product) {
                $quantities[] = rand(0, 5);
            }
        }

        return $quantities;
    }

    private function disableEnableSupplier($browser, $supplier_name)
    {
        $supplier_id = Str::slug($supplier_name);

        $browser->visitRoute('suppliers.index')
            ->waitForText('Amministra Categorie')
            ->click('.accordion-item[data-element-id="' . $supplier_id . '"]')->waitForText('Dettagli')
            ->mainScreenshot('fornitore')
            ->with('.accordion-item[data-element-id="' . $supplier_id . '"]', function($panel) use ($supplier_name) {
                $panel->waitForText('Salva')
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
                ->typeSlowly('name', $supplier[0], 50)
                ->typeSlowly('description', $supplier[1], 50)
                ->typeSlowly('payment_method', $supplier[2], 50)
                ->typeSlowly('order_method', $supplier[3], 50)
                ->typeSlowly('vat', $supplier[4], 50)
                ->press('Salva')
                ->pause(1000)
                ->with('.accordion-collapse.collapse.show', function($panel) use ($supplier) {
                    $panel->assertInputValue('name', $supplier[0])
                        ->assertInputValue('payment_method', $supplier[2])
                        ->assertInputValue('order_method', $supplier[3])
                        ->assertInputValue('vat', $supplier[4]);
                });
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
                    $browser->scrollIntoView('#category_admin')->pause(500);

                    $panel->press('Crea Nuovo Prodotto')->waitForText('Prezzo Unitario')
                        ->with('.modal.show', function($panel) use ($product, $default_vat_rate) {
                            $panel->typeSlowly('name', $product[0], 50)
                                ->typeSlowly('price', $product[1], 50)
                                ->select('measure_id', $product[2])
                                ->select('category_id', $product[3])
                                ->select('vat_rate_id', $default_vat_rate->id)
                                ->press('Salva');
                        })
                        ->waitForText($product[0])->pause(500);

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
            ->typeSlowly('input[name=new_category]', 'Pippo', 50)->click('.dynamic-tree-add')
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
            ->typeSlowly('start', printableDate(date('Y-m-d')), 50)
            ->typeSlowly('end', printableDate(date('Y-m-d', strtotime('+7 days'))), 50)
            ->typeSlowly('shipping', printableDate(date('Y-m-d', strtotime('+14 days'))), 50)
            ->select('supplier_id', $supplier_id)
            ->press('Salva')
            ->waitForText('Consegne');
    }

    private function doBookings($browser)
    {
        $products = $this->products();

        $total = 0;
        $quantities = $this->quantities();

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
            ->clickAtXPath('//*/div[contains(@class, "accordion-item")][1]/h2/button')->waitForText('Consegne')->pause(1000)
            ->scrollIntoView('.order-summary-order-price')
            ->assertSeeIn('.order-summary-order-price', '0.00');
    }

    private function modifiedProducts($browser)
    {
        $suppliers = $this->suppliers();
        $supplier_name = $suppliers[0][0];
        $supplier_id = Str::slug($supplier_name);

        $browser->visitRoute('suppliers.index')
            ->waitForText('Amministra Categorie')
            ->click('.accordion-item[data-element-id="' . $supplier_id . '"]')->waitForText('Dettagli')
            ->press('Prodotti')
            ->pause(500)
            ->clickAtXPath('//*/div[@data-element-id="' . $supplier_id . '"]//div[contains(@class, "active")]//div[contains(@class, "accordion-item")][1]/h2/button')->waitForText('Sconto')->pause(1000)
            ->clickAtXPath('//*/div[@data-element-id="' . $supplier_id . '"]//div[contains(@class, "active")]//div[contains(@class, "accordion-item")][1]//label[normalize-space()="Sconto"]/following-sibling::div/button')
            ->waitForText('Misura su cui applicare le soglie')

            ->click('@applies_type-quantity')
            ->click('@value-percentage')
            ->click('@arithmetic-sub')
            ->click('@applies_target-booking')
            ->click('@scale-major')
            ->clickLink('Aggiungi Soglia')
            ->typeAtXPath('//*/table/tbody/tr[1]/td[2]/div/input', '5')->typeAtXPath('//*/table/tbody/tr[1]/td[4]/div/input', '10')
            ->typeAtXPath('//*/table/tbody/tr[2]/td[2]/div/input', '15')->typeAtXPath('//*/table/tbody/tr[2]/td[4]/div/input', '20')
            ->mainScreenshot('modificatore_soglie')

            ->click('@applies_type-quantity')
            ->click('@value-price')
            ->click('@applies_target-order')
            ->click('@scale-major')
            ->typeAtXPath('//*/table/tbody/tr[1]/td[2]/div/input', '20')->typeAtXPath('//*/table/tbody/tr[1]/td[4]/div/input', '2.50')
            ->typeAtXPath('//*/table/tbody/tr[2]/td[2]/div/input', '40')->typeAtXPath('//*/table/tbody/tr[2]/td[4]/div/input', '2.20')
            ->mainScreenshot('modificatore_prezzo_unitario')

            ->click('@applies_type-none')
            ->click('@value-percentage')
            ->click('@arithmetic-sub')
            ->typeSlowly('simplified_amount', '5', 50)
            ->mainScreenshot('modificatore_semplice')

            ->press('Salva')
            ->pause(500)
            ->script('window.scrollTo(0,document.body.scrollHeight)');

        $browser->press('Salva')->pause(500);
    }

    private function createNotification($browser)
    {
        $browser->visitRoute('notifications.index')
            ->waitForText('Crea Nuovo Notifica')
            ->press('Crea Nuovo Notifica')->waitForText('Tipo')
            ->with('.modal.show', function($panel) {
                $panel->typeSlowly('content', 'Solo una prova', 50)
                    ->typeSlowly('start_date', printableDate(date('Y-m-d')), 50)
                    ->typeSlowly('end_date', printableDate(date('Y-m-d', strtotime('+7 days'))), 50)
                    ->press('Salva');
            })
            ->waitForText('Solo una prova');
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
            $this->modifiedProducts($browser);

            $this->createNotification($browser);
        });
    }
}
