<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\VatRate;
use App\Product;

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
            ->press('Amministratore')->waitForText('Avere sotto-utenti')->pause(500)
            ->scrollView('input[type="checkbox"][data-action="users.subusers"]')
            ->click('input[type="checkbox"][data-action="users.subusers"]')
            ->pause(2000)
            ->mainScreenshot('permessi');

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
                    ->typeSlowly('firstname', 'Luigi', 50)
                    ->typeSlowly('lastname', 'Verdi', 50)
                    ->typeSlowly('password', 'amico_di_mario', 50)
                    ->press('Salva');
            })
            ->waitForText('Verdi Luigi')
            ->mainScreenshot('amici');
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
            ->click('.accordion-item[data-element-id="bandiera"]')
            ->waitForText('Anagrafica')
            ->with('.accordion-item[data-element-id="bandiera"]', function($panel) {
                $panel->assertInputValue('firstname', 'Attilio')
                    ->assertInputValue('lastname', 'Bandiera')
                    ->scrollView('button[type=submit]')
                    ->press('@status-deleted')
                    ->waitFor('input[name="deleted_at"]')
                    ->scrollView('button[type=submit]')
                    ->press('Salva');
            })
            ->waitUntilMissing('Anagrafica')->pause(500);

        /*
            Controllo cessazione utente
        */
        $browser->visitRoute('users.index')
            ->waitForText('Importa CSV')
            ->assertDontSee('Bandiera')
            ->press('button[data-filter-attribute="deleted_at"]')
            ->waitForText('Bandiera')
            ->assertSee('Bandiera');
    }

    private function suppliers()
    {
        return [
            ['La Zucchina Dorata', 'Verdure di stagione, prenotazioni settimanali', 'Bonifico bancario IBAN IT01234567890', 'Mandare una mail con le prenotazioni a Luisa: zucchina@example.com', 'IT01234567890'],
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
            (object) [
                'name' => 'Finocchi',
                'price' => 3.00,
                'unit_measure' => 'chili',
                'category' => 'verdura',
                'min_quantity' => 3,
                'variants' => []
            ],
            (object) [
                'name' => 'Melanzane',
                'price' => 2.00,
                'unit_measure' => 'chili',
                'category' => 'verdura',
                'variants' => [
                    'Forma' => ['Tonda', 'Ovale', 'Lunga'],
                    'Colore' => ['Nera', 'Viola']
                ]
            ],
            (object) [
                'name' => 'Peperoncino piccante',
                'price' => 0.50,
                'unit_measure' => 'pezzi',
                'category' => 'verdura',
                'variants' => [
                    'Piccantezza' => ['Poco piccante', 'Molto piccante']
                ]
            ],
            (object) [
                'name' => 'Zucchine',
                'price' => 2.50,
                'unit_measure' => 'chili',
                'category' => 'verdura',
                'max_available' => 3,
                'variants' => []
            ],
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
                $panel->waitForText('Salva')
                    ->click('@status-deleted')
                    ->waitFor('input[name="deleted_at"]')
                    ->scrollView('button[type=submit]')
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
                    ->scrollView('button[type=submit]')
                    ->press('Salva');
            })
            ->waitUntilMissing('Dettagli');
    }

    private function handlingVariants($panel, $browser, $variants, $actual_save)
    {
        foreach($variants as $variant_name => $variant_values) {
            $browser->scrollView('button[type=submit]')->press('Crea Nuova Variante')->waitForText('Valori')
                ->with('.modal.show', function($panel) use ($browser, $variant_name, $variant_values, $actual_save) {
                    $panel->pause(100)->typeSlowly('name', $variant_name, 50);

                    foreach($variant_values as $value) {
                        $panel->clickLink('Aggiungi Nuovo')->pause(200)
                            ->typeAtXPath('//*/table/tbody/tr[last() - 1]/td/input', $value);
                    }

                    if ($actual_save) {
                        $panel->press('Salva');
                    }
                    else {
                        $browser->mainScreenshot('variante');
                        $panel->press('Chiudi');
                    }
                });

            if ($actual_save) {
                $panel->scrollView('button[type=submit]')->waitForText($variant_name);
            }
        }

        $browser->pause(500)->scrollBottom()->press('Modifica Matrice Varianti')->waitForText('Differenza Prezzo')
            ->with('.modal.show', function($panel) use ($browser, $actual_save) {
                if ($actual_save) {
                    $panel->press('Salva')->pause(500);
                }
                else {
                    $browser->mainScreenshot('matrice_varianti');
                    $panel->press('Chiudi')->pause(500);
                }
            });

        $browser->scrollView('button[type=submit]')->waitForText('Elimina');
    }

    private function createSuppliers($browser)
    {
        $suppliers = $this->suppliers();

        /*
            Creazione fornitori
        */
        foreach($suppliers as $index => $supplier) {
            $browser->visitRoute('suppliers.index')
                ->waitForText('Amministra Categorie')
                ->press('Crea Nuovo Fornitore')->waitForText('Nome')->pause(500)
                ->typeSlowly('name', $supplier[0], 50)
                ->typeSlowly('description', $supplier[1], 50)
                ->typeSlowly('payment_method', $supplier[2], 50)
                ->typeSlowly('order_method', $supplier[3], 50)
                ->typeSlowly('vat', $supplier[4], 50)
                ->press('Salva')
                ->pause(1000)->waitForText('Ordini')->pause(1000)
                ->with('.accordion-collapse.collapse.show', function($panel) use ($browser, $index, $supplier) {
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
        $accordion_target = '.accordion-item[data-element-id="' . $supplier_id . '"]';

        $browser->visitRoute('suppliers.index')
            ->waitForText('Amministra Categorie')
            ->click($accordion_target)->pause(500)->waitForText('Dettagli')
            ->press('Prodotti')->pause(500)
            ->with('.accordion-item[data-element-id="' . $supplier_id . '"]', function($panel) use ($browser, $products, $supplier_id, $accordion_target) {
                $variant_screenshot_made = false;

                foreach($products as $index => $product) {
                    $browser->scrollView($accordion_target)->pause(1000);

                    $panel->press('Crea Nuovo Prodotto')->waitForText('Prezzo Unitario')
                        ->with('.modal.show', function($panel) use ($product) {
                            $panel->typeSlowly('name', $product->name, 50)
                                ->typeSlowly('price', $product->price, 50)
                                ->select('measure_id', $product->unit_measure)
                                ->select('category_id', $product->category)
                                ->select('vat_rate_id', VatRate::inRandomOrder()->first()->id)
                                ->press('Salva');
                        })
                        ->waitForText($product->name)->pause(500);

                    $product_obj = Product::where('supplier_id', $supplier_id)->where('name', $product->name)->first();

                    if ($index == 0) {
                        $browser->mainScreenshot('prodotto');
                    }

                    $browser->with('.accordion-item[data-element-id="' . $product_obj->id . '"]', function($panel) use ($variant_screenshot_made, $browser, $product) {
                        $panel->clear('min_quantity')->typeSlowly('min_quantity', $product->min_quantity ?? 0, 50)
                            ->clear('max_available')->typeSlowly('max_available', $product->max_available ?? 0, 50);

                        /*
                            Creazione varianti
                        */
                        if (!empty($product->variants)) {
                            $variants = $product->variants;
                            $this->handlingVariants($panel, $browser, $variants, true);

                            if ($variant_screenshot_made == false) {
                                foreach($variants as $variant_name => $variant_values) {
                                    $this->handlingVariants($panel, $browser, [$variant_name => $variant_values], false);
                                    break;
                                }
                                $variant_screenshot_made = true;
                            }
                        }

                        $panel->scrollView('.save-button')->press('Salva');
                    });
                }
            });
    }

    private function testSuppliersTools($browser)
    {
        /*
            Amministrazione categorie
        */
        $browser->visitRoute('suppliers.index')
            ->waitForText('Amministra Categorie')->press('#category_admin')->waitForText('Clicca e trascina le categorie nell\'elenco')
            ->mainScreenshot('categorie')
            ->typeSlowly('input[name=new_category]', 'Pippo', 50)->click('.dynamic-tree-add')
            ->pause(100)
            ->press('Salva')
            ->waitForText('Amministra Categorie')->press('#category_admin')->waitForText('Clicca e trascina le categorie nell\'elenco')
            ->assertInputValueAtXPath('//*[@id="pippo"]/div/input', 'Pippo');

        /*
            Amministrazione unità di misura
        */
        $browser->visitRoute('suppliers.index')
            ->waitForText('Amministra Categorie')->click('#unit_measure_admin')->waitForText('Unità Discreta')
            ->mainScreenshot('unita_misura')
            ->click('.add-row')
            ->pause(100)
            ->typeAtXPath('//*/table/tbody/tr[last() - 1]/td/input', 'Pippo')
            ->press('Salva')
            ->waitForText('Amministra Categorie')->click('#unit_measure_admin')->waitForText('Unità Discreta')
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

        /*
            Salvataggio prenotazione, e controllo
        */

        $browser->visitRoute('bookings.index')
            ->waitForText('Puoi modificare')
            ->clickAtXPath('//*/div[contains(@class, "accordion-item")][1]/h2/button')
            ->waitForText('La Mia Prenotazione')
            ->with('.accordion-collapse.show', function($panel) use ($products, &$total) {
                foreach($products as $index => $product) {
                    $target_input = '//*/table/tbody/tr[contains(@class, "booking-product")][' . ($index + 1) . ']/td//input[@type="text"]';
                    $target_feedback = '(//*/table/tbody/tr[contains(@class, "booking-product")][' . ($index + 1) . ']/td//div[@class="invalid-feedback"])[1]';
                    $target_price = '//*/table/tbody/tr[contains(@class, "booking-product")][' . ($index + 1) . ']/td//label[contains(@class, "booking-product-price")]/span';

                    $quantity = rand(0, 5);

                    if ($product->min_quantity ?? 0) {
                        $panel->pause(200)
                            ->typeAtXPath($target_input, $product->min_quantity - 1)
                            ->pause(500)
                            ->assertSeeAtXPath($target_feedback, 'Quantità inferiore al minimo consentito');

                        $quantity = $product->min_quantity + 1;
                    }
                    else if ($product->max_available ?? 0) {
                        $panel->pause(200)
                            ->typeAtXPath($target_input, $product->max_available + 1)
                            ->pause(500)
                            ->assertSeeAtXPath($target_feedback, 'Quantità superiore alla disponibilità');

                        $quantity = $product->max_available - 1;
                    }

                    $product_total = sprintf('%.02f', $product->price * $quantity);

                    $panel->pause(200)
                        ->typeAtXPath($target_input, $quantity)
                        ->pause(500)
                        ->assertSeeAtXPath($target_price, $product_total);

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
            ->scrollView('.order-summary-order-price')->assertSeeIn('.order-summary-order-price', sprintf('%.02f €', $total));

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
            ->scrollView('.order-summary-order-price')
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
            ->pause(1000)
            ->waitForText('Modifica Rapida')
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
            ->scrollBottom()
            ->press('Salva')->pause(500);
    }

    private function createNotification($browser)
    {
        $browser->visitRoute('notifications.index')
            ->waitForText('Crea Nuovo Notifica')
            ->press('Crea Nuovo Notifica')->waitForText('Tipo')->pause(100)
            ->with('.modal.show', function($panel) {
                $panel->typeSlowly('content', 'Solo una prova', 50)
                    ->typeSlowly('start_date', printableDate(date('Y-m-d')), 50)
                    ->typeSlowly('end_date', printableDate(date('Y-m-d', strtotime('+7 days'))), 50)
                    ->press('Salva');
            })
            ->waitForText('Solo una prova');

        $browser->visitRoute('notifications.index')
            ->waitForText('Crea Nuovo Notifica')
            ->press('Crea Nuovo Notifica')->waitForText('Tipo')->pause(1000)
            ->with('.modal.show', function($panel) {
                $panel->click('@type-date')
                    ->assertDontSee('Destinatari')
                    ->click('@type-notification')
                    ->assertSee('Destinatari');
            });
    }

    private function createShippingPlace($browser)
    {
        $browser->visit('/gas/senza-nome/edit')
            ->waitForText('Luoghi di Consegna')
            ->press('Luoghi di Consegna')->pause(200)
            ->press('Crea Nuovo Luogo di Consegna')->waitForText('Nome')->pause(1000)
            ->with('.modal.show', function($panel) use ($browser) {
                $panel->typeSlowly('name', 'Luogo Test', 50)
                    ->click('.address')->pause(200);

                    $browser->with('.popover-body', function($popover) {
                        $popover->typeSlowly('street', 'Via Test 42', 50)
                            ->typeSlowly('city', 'Torino', 50)
                            ->typeSlowly('cap', '10100', 50)
                            ->press('.btn-success')->pause(200);
                    });

                    $panel->press('Salva');
            })
            ->waitForText('Luogo Test');

        $browser->click('.accordion-item[data-element-id="luogo-test"]')->pause(1000)
            ->press('@modifier_spese-trasporto')
            ->pause(500)
            ->with('.modal', function($panel) {
                $panel->click('@applies_type-none')
                    ->click('@value-absolute')
                    ->click('@arithmetic-sum')
                    ->click('@applies_target-booking')
                    ->typeSlowly('simplified_amount', '3', 50)
                    ->mainScreenshot('modificatore_luogo')
                    ->press('Salva');
            })
            ->pause(500)
            ->assertSee('3€');
    }

    private function createMovement($browser)
    {
        $browser->visit('/movements')
            ->press('Crea Nuovo Movimento')->waitForText('Tipo')
            ->with('.modal.show', function($panel) use ($browser) {
                $panel->pause(500)
                    ->select('type', 'user-credit')
                    ->waitForText('Valore')
                    ->select('target_id', 'garibaldi')
                    ->typeSlowly('amount', '10')
                    ->press('@method-bank')
                    ->typeSlowly('identifier', '1234567890')
                    ->typeSlowly('notes', 'Versamento credito');

                $browser->mainScreenshot('movimento');
                $panel->press('Salva');
            })
            ->waitForText('Garibaldi');
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
            $browser->maximize();

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
            $this->createShippingPlace($browser);
            $this->createMovement($browser);
        });
    }
}
