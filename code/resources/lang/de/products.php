<?php

return array (
  'prices' => 
  array (
    'unit' => 'Stückpreis',
    'unit_no_vat' => 'Einzelpreis (ohne Mwst.)',
    'package' => 'Preis der Verpackungseinheit',
  ),
  'name' => 'Produkt',
  'code' => 'Artikelnummer Lieferant',
  'bookable' => 'Bestellbar',
  'vat_rate' => 'MwSt-Satz',
  'portion_quantity' => 'Gebindegröße',
  'multiple' => 'Vielfaches',
  'min_quantity' => 'Minimum',
  'max_quantity' => 'Empfohlenes Maximum',
  'available' => 'Verfügbar',
  'help' => 
  array (
    'unit_no_vat' => 'Zu benutzen in Kombination mit dem jeweiligen Mehrwertsteuersatz',
    'package_price' => 'Wenn genauer angegeben, wird der Einheitspreis als Preis für die Verpackungseinheit / Gebindegröße berechnet',
    'importing_categories_and_measures' => 'Nicht aufgelistete Kategorien und Maßeinheiten werden erstellt.',
    'imported_notice' => 'Importierte Produkte',
    'available_explain' => '',
    'bookable' => '',
    'pending_orders_change_price' => '',
    'pending_orders_change_price_second' => '',
    'discrete_measure_selected_notice' => '',
    'measure' => 'Die dem Produkt zugewiesene Maßeinheit. Achtung: dies kann verschiedene Variablen des Produktes beeinflussen, siehe den Parameter „diskrete Einheit“ im Administrationsoberfläche unter „Maßeinheiten verwalten“ (einsehbar nur für Nutzer mit entsprechenden Rechten)',
    'portion_quantity' => 'Wenn Wert > 0 versteht sich jede Einheit in dieser angegebenen Größe. Zum Beispiel:<ul><li>Maßeinheit: Kilogramm</li><li>Stückzahl: 0.3</li><li>Stück/Gebindepreis: 10 Euro</li><li>bestellte Menge: 1 (die also zu verstehen ist als "1 Stück à 0.3 Kilo")</li><li>Kosten: 1 x 0.3 x 10 = 3 Euro</li></ul>Nützlich um Produkte zu verwalten, die in Einzelstücken verteilt werden, welche von den Nutzern vorbestellt werden können, aber beim Lieferant als Gesamtmenge bezahlt werden',
    'package_size' => 'Wenn das Produkt in Abpackungen von n Stücken ausgegeben wird, wird hier der Wert von n angezeigt. Die Bestellungen an den Lieferanten müssen die Anzahl der Verpackungen enthalten, d.h. die Anzahl der bestellten Stücke / die Anzahl der Stücke in der Verpackung. Wenn die Gesamtmenge der bestellten Stücke nicht ein Vielfaches dieser Anzahl ist, ist das Produkt in der Tabelle, die die Zusammenfassung der bestellten Produkte enthält, mit einem roten icon gekennzeichnet. Aus dieser Tabelle lassen sich die Mengen systematisieren, indem - wo erforderlich - hinzugefügt und weggenommen wird.',
    'multiple' => 'Wenn ungleich null, ist das Produkt nur vorbestellbar durch die Vervielfachung dieses Wertes. Nützlich für Produkte, die vorverpackt sind, aber individuell vorbestellt werden. Nicht zu verwechseln mit dem Attribut \'Verpackung\'',
    'min_quantity' => 'Wenn ungleich null, ist das Produkt nur bestellbar für eine Anzahl, die größer ist als die hier angezeigte',
    'max_quantity' => 'Wenn ungleich null, wird eine Warnung angezeigt, wenn eine höhere Anzahl als die hier Angegebene bestellt wird',
    'available' => 'Wenn ungleich null, ist dieses die maximale Anzahl der Produkte, die insgesamt in einer Bestellung vorgemerkt werden können. In der Bestellphase sehen die Nutzer, was bisher bestellt wurde',
    'global_min' => 'Wenn ungleich null, ist dieses die maximale Anzahl der Produkte, die insgesamt in einer Bestellung vorgemerkt werden können. In der Bestellphase sehen die Nutzer, was bisher bestellt wurde',
    'variants' => 'Jedes Produkt kann mehrere Varianten haben, z.B. in der Größe oder Farbe der Wäscheteile. In der Phase der Bestellung können die Nutzer die gewünschte Anzahl für jede Kombination der Varianten angeben. Die Varianten können darüberhinaus einen eigenen Preis haben, der abhängig vom Gebindepreis des Produktes angegeben wird (z.B. +1 Euro oder -0.80 Euro)',
    'duplicate_notice' => 'Mit dem Duplizieren eines Produkts werden auch die Varianten des Originalprodukts kopiert bzw. dupliziert. Nach dem Speichern des Duplikats können die Varianten verändert werden.',
    'unit_price' => '',
    'vat_rate' => '',
    'notice_removing_product_in_orders' => '',
  ),
  'weight_with_measure' => '',
  'list' => 'Produkte',
  'sorting' => '',
  'variant' => 
  array (
    'matrix' => 'Variante hinzufügen/verändern',
    'help' => 
    array (
      'code' => '',
      'price_difference' => '',
    ),
    'price_difference' => 'Preisdifferenz',
    'weight_difference' => 'Gewichtsunterschied',
  ),
  'package_size' => 'Verpackung',
  'global_min' => 'Gesamtbestellung',
  'variants' => 'Veränderbar',
  'remove_confirm' => '',
  'removing' => 
  array (
    'keep' => '',
    'leave' => '',
  ),
);
