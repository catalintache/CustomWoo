<?php
/**
 * globals.php
 *
 * Acest fișier definește variabilele globale care sunt folosite în plugin:
 * - $GLOBALS['produse']: date despre produse (greutate, incadrare etc.)
 * - $GLOBALS['calcul_transport']: tarife de transport pe județ
 * - $GLOBALS['mapare_judet']: mapping județ - capitală
 * - Funcția grPaleti() pentru calculul numărului de paleți pe baza greutății
 */

$GLOBALS['produse'] = [
    [
        'nume' => 'Cărbuni presați, tip ou, pentru grătar – 10 kg',
        'id' => 20106,
        'cota_tva' => '19%',
        'tip_taxare' => 'TI',
        'tip_livrare' => 'livrare gratuită',
        'unitate_masura' => 'saci',
        'greutate_unitar' => 10, // kg
        'incadrare' => 'regula20', // pierdere 40kg/transport
        'greutate_palet' => 360
    ],
    [
        'nume' => 'Cărbune brichetat hexagon pentru grătar 10 kg',
        'id' => 20112,
        'cota_tva' => '19%',
        'tip_taxare' => 'TI',
        'tip_livrare' => 'livrare gratuită',
        'unitate_masura' => 'saci',
        'greutate_unitar' => 10,
        'incadrare' => 'regula20',
        'greutate_palet' => 360
    ],
    [
        'nume' => 'Cărbuni pentru grătar 10kg mix (fag, carpen, stejar)',
        'id' => 16209,
        'cota_tva' => '19%',
        'tip_taxare' => 'TI',
        'tip_livrare' => 'livrare gratuită',
        'unitate_masura' => 'saci',
        'greutate_unitar' => 10,
        'incadrare' => 'regula20',
        'greutate_palet' => 360
    ],
    [
        'nume' => 'Cărbuni pentru grătar 10kg carpen 100%',
        'id' => 16152,
        'cota_tva' => '19%',
        'tip_taxare' => 'TI',
        'tip_livrare' => 'livrare gratuită',
        'unitate_masura' => 'saci',
        'greutate_unitar' => 10,
        'incadrare' => 'regula20',
        'greutate_palet' => 360
    ],
    [
        'nume' => 'Cărbuni pentru grătar 3kg arde',
        'id' => 16211,
        'cota_tva' => '0%',
        'tip_taxare' => 'TI',
        'tip_livrare' => 'livrare gratuită',
        'unitate_masura' => 'saci',
        'greutate_unitar' => 3,
        'incadrare' => 'full light',
        'greutate_palet' => 390
    ],
    [
        'nume' => 'Cărbuni pentru grătar 3kg Grill Mania',
        'id' => 16220,
        'cota_tva' => '19%',
        'tip_taxare' => 'TI',
        'tip_livrare' => 'livrare gratuită',
        'unitate_masura' => 'saci',
        'greutate_unitar' => 3,
        'incadrare' => 'full light',
        'greutate_palet' => 390
    ],
    [
        'nume' => 'Cărbuni pentru grătar 3kg Master Grill',
        'id' => 16927,
        'cota_tva' => '19%',
        'tip_taxare' => 'TI',
        'tip_livrare' => 'livrare gratuită',
        'unitate_masura' => 'saci',
        'greutate_unitar' => 3,
        'incadrare' => 'full light',
        'greutate_palet' => 390
    ],
    [
        'nume' => 'Cărbuni pentru grătar 3kg',
        'id' => 16934,
        'cota_tva' => '19%',
        'tip_taxare' => 'TI',
        'tip_livrare' => 'livrare gratuită',
        'unitate_masura' => 'saci',
        'greutate_unitar' => 3,
        'incadrare' => 'full light',
        'greutate_palet' => 390
    ],
    [
        'nume' => 'Brichete cărbuni tip ou 2kg',
        'id' => 16224,
        'cota_tva' => '19%',
        'tip_taxare' => 'TN',
        'tip_livrare' => 'livrare gratuită',
        'unitate_masura' => 'saci',
        'incadrare' => 'full light',
        'greutate_palet' => 400, // kg
        'greutate_unitar' => 2  // kg
    ],
    [
        'nume' => 'Brichete cărbuni tip ou 2.5kg',
        'id' => 16939,
        'cota_tva' => '19%',
        'tip_taxare' => 'TN',
        'tip_livrare' => 'livrare gratuită',
        'unitate_masura' => 'saci',
        'incadrare' => 'full light',
        'greutate_unitar' => 2.5, // kg
        'greutate_palet' => 400   // kg
    ],
    [
        'nume' => 'Brichete din rumeguș cilindru 10kg',
        'id' => 16231,
        'cota_tva' => '5%',
        'tip_taxare' => 'TN',
        'tip_livrare' => 'livrare prin pallex',
        'unitate_masura' => 'saci',
        'greutate_unitar' => 10,
        'greutate_palet' => 400,
        'incadrare' => 'mixt',
    ],
    [
        'nume' => 'Brichete din rumeguș RUF 10kg',
        'id' => 16236,
        'cota_tva' => '5%',
        'tip_taxare' => 'TN',
        'tip_livrare' => 'livrare prin pallex',
        'unitate_masura' => 'saci',
        'greutate_unitar' => 10,
        'greutate_palet' => 400,
        'incadrare' => 'mixt',
    ],
    [
        'nume' => 'Brichete din rumeguș hexagonale pini-kay 50kg',
        'id' => 16241,
        'cota_tva' => '5%',
        'tip_taxare' => 'TN',
        'tip_livrare' => 'livrare prin pallex',
        'unitate_masura' => 'saci',
        'greutate_unitar' => 50,
        'greutate_palet' => 400,
        'incadrare' => 'mixt',
    ],
    [
        'nume' => 'Peleți hs timber (schweighofer) 15kg (rășinoase)',
        'id' => 16245,
        'cota_tva' => '5%',
        'tip_taxare' => 'TN',
        'tip_livrare' => 'livrare prin pallex',
        'unitate_masura' => 'saci',
        'greutate_unitar' => 15,
        'greutate_palet' => 990,
        'incadrare' => 'megafull',
    ],
    [
        'nume' => 'Peleți forest technology 15kg (rășinoase)',
        'id' => 16259,
        'cota_tva' => '5%',
        'tip_taxare' => 'TN',
        'tip_livrare' => 'livrare prin pallex',
        'unitate_masura' => 'saci',
        'greutate_unitar' => 15,
        'greutate_palet' => 990,
        'incadrare' => 'megafull',
    ],
    [
        'nume' => 'Peleți pell up 15kg (rășinoase)',
        'id' => 16249,
        'cota_tva' => '5%',
        'tip_taxare' => 'TN',
        'tip_livrare' => 'livrare prin pallex',
        'unitate_masura' => 'saci',
        'greutate_unitar' => 15,
        'greutate_palet' => 990,
        'incadrare' => 'megafull',
    ],
    [
        'nume' => 'Peleți barlinek 15 kg (rășinoase)',
        'id' => 16256,
        'cota_tva' => '5%',
        'tip_taxare' => 'TN',
        'tip_livrare' => 'livrare prin pallex',
        'unitate_masura' => 'saci',
        'greutate_unitar' => 15,
        'greutate_palet' => 990,
        'incadrare' => 'megafull',
    ],
    [
        'nume' => 'Peleți wood pellets 15 kg (rășinoase)',
        'id' => 16252,
        'cota_tva' => '5%',
        'tip_taxare' => 'TN',
        'tip_livrare' => 'livrare prin pallex',
        'unitate_masura' => 'saci',
        'greutate_unitar' => 15,
        'greutate_palet' => 990,
        'incadrare' => 'megafull',
    ],
    [
        'nume' => 'Lemnuțe pentru grătar 2.5kg',
        'id' => 16262,
        'cota_tva' => '19%',
        'tip_taxare' => 'TI',
        'tip_livrare' => 'livrare prin pallex',
        'unitate_masura' => 'saci',
        'greutate_unitar' => 2.5,
        'greutate_palet' => 240,
        'incadrare' => 'half',
    ],
    [
        'nume' => 'Lemne de foc esență tare',
        'id' => 16268,
        'cota_tva' => '5%',
        'tip_taxare' => 'TI',
        'tip_livrare' => 'livrare gratuită',
        'unitate_masura' => 'metru linear',
        'greutate_unitar' => 800,
        'greutate_palet' => 800,
        'incadrare' => 'full'
    ]
];

$GLOBALS['calcul_transport'] = [
    'ALBA' => [
         'mini quarter' => 123,
         'quarter' => 163,
         'half' => 177,
         'fl1' => 211,
         'fl23' => 206,
         'fl4' => 204,
         'full1' => 219,
         'full23' => 214,
         'full4' => 210,
         'mf13' => 251,
         'mf4' => 247,
         'mfmax13' => 322,
         'mfmax4' => 304
    ],
    'ARAD' => [
         'mini quarter' => 123,
         'quarter' => 176,
         'half' => 191,
         'fl1' => 230,
         'fl23' => 225,
         'fl4' => 223,
         'full1' => 238,
         'full23' => 233,
         'full4' => 229,
         'mf13' => 270,
         'mf4' => 264,
         'mfmax13' => 341,
         'mfmax4' => 323
    ],
    'ARGES' => [
         'mini quarter' => 119,
         'quarter' => 138,
         'half' => 143,
         'fl1' => 174,
         'fl23' => 170,
         'fl4' => 168,
         'full1' => 182,
         'full23' => 178,
         'full4' => 174,
         'mf13' => 190,
         'mf4' => 185,
         'mfmax13' => 273,
         'mfmax4' => 260
    ],
    // ... Adaugă alte județe după cum este necesar
];

$GLOBALS['mapare_judet'] = [
    '0'  => ['judet' => 'ALBA',           'capitala_judet' => 'ALBA IULIA'],
    '1'  => ['judet' => 'ARAD',           'capitala_judet' => 'ARAD'],
    '2'  => ['judet' => 'ARGES',          'capitala_judet' => 'PITESTI'],
    '3'  => ['judet' => 'BACAU',          'capitala_judet' => 'BACAU'],
    '4'  => ['judet' => 'BIHOR',          'capitala_judet' => 'ORADEA'],
    '5'  => ['judet' => 'BISTRITA-NASAUD','capitala_judet' => 'BISTRITA'],
    '6'  => ['judet' => 'BOTOSANI',       'capitala_judet' => 'BOTOSANI'],
    '7'  => ['judet' => 'BRAILA',         'capitala_judet' => 'BRAILA'],
    '8'  => ['judet' => 'BRASOV',         'capitala_judet' => 'BRASOV'],
    '9'  => ['judet' => 'BUCURESTI',      'capitala_judet' => 'BUCURESTI'],
    '10' => ['judet' => 'BUZAU',          'capitala_judet' => 'BUZAU'],
    '11' => ['judet' => 'CALARASI',       'capitala_judet' => 'CALARASI'],
    '12' => ['judet' => 'CARAS-SEVERIN',  'capitala_judet' => 'RESITA'],
    '13' => ['judet' => 'CLUJ',           'capitala_judet' => 'CLUJ-NAPOCA'],
    '14' => ['judet' => 'CONSTANTA',      'capitala_judet' => 'CONSTANTA'],
    '15' => ['judet' => 'COVASNA',        'capitala_judet' => 'SFANTU GHEORGHE'],
    '16' => ['judet' => 'DAMBOVITA',      'capitala_judet' => 'TARGOVISTE'],
    '17' => ['judet' => 'DOLJ',           'capitala_judet' => 'CRAIOVA'],
    '18' => ['judet' => 'GALATI',         'capitala_judet' => 'GALATI'],
    '19' => ['judet' => 'GIURGIU',        'capitala_judet' => 'GIURGIU'],
    '20' => ['judet' => 'GORJ',           'capitala_judet' => 'TARGU JIU'],
    '21' => ['judet' => 'HARGHITA',       'capitala_judet' => 'MIERCUREA CIUC'],
    '22' => ['judet' => 'HUNEDOARA',      'capitala_judet' => 'DEVA'],
    '23' => ['judet' => 'IALOMITA',       'capitala_judet' => 'SLOBOZIA'],
    '24' => ['judet' => 'IASI',           'capitala_judet' => 'IASI'],
    '25' => ['judet' => 'ILFOV',          'capitala_judet' => 'BUFTEA'],
    '26' => ['judet' => 'MARAMURES',      'capitala_judet' => 'BAIA MARE'],
    '27' => ['judet' => 'MEHEDINTI',      'capitala_judet' => 'DROBETA-TURNU SEVERIN'],
    '28' => ['judet' => 'MURES',          'capitala_judet' => 'TARGU MURES'],
    '29' => ['judet' => 'NEAMT',          'capitala_judet' => 'PIATRA NEAMT'],
    '30' => ['judet' => 'OLT',            'capitala_judet' => 'SLATINA'],
    '31' => ['judet' => 'PRAHOVA',        'capitala_judet' => 'PLOIESTI'],
    '32' => ['judet' => 'SALAJ',          'capitala_judet' => 'ZALAU'],
    '33' => ['judet' => 'SATU MARE',      'capitala_judet' => 'SATU MARE'],
    '34' => ['judet' => 'SIBIU',          'capitala_judet' => 'SIBIU'],
    '35' => ['judet' => 'SUCEAVA',        'capitala_judet' => 'SUCEAVA'],
    '36' => ['judet' => 'TELEORMAN',      'capitala_judet' => 'ALEXANDRIA'],
    '37' => ['judet' => 'TIMIS',          'capitala_judet' => 'TIMISOARA'],
    '38' => ['judet' => 'TULCEA',         'capitala_judet' => 'TULCEA'],
    '39' => ['judet' => 'VALCEA',         'capitala_judet' => 'RAMNICU VALCEA'],
    '40' => ['judet' => 'VASLUI',         'capitala_judet' => 'VASLUI'],
    '41' => ['judet' => 'VRANCEA',        'capitala_judet' => 'FOCSANI']
];