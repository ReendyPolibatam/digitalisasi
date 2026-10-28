<?php

use App\Services\DocumentFieldExtractorService;

function vesselName(string $text): ?string
{
    return (new DocumentFieldExtractorService)->extract($text)['vessel_name'];
}

test('vessel name is read from its label', function (string $text, string $expected) {
    expect(vesselName($text))->toBe($expected);
})->with([
    'name of vessel' => ["NAME OF VESSEL : OB PUTRA GANDUS\nVOYAGE : 022/D/P.G", 'OB PUTRA GANDUS'],
    'same line as next column' => ['NAME OF VESSEL : OB Putra Gandus   PORT OF LOADING : Sungai Pakning', 'OB Putra Gandus'],
    'summary data' => ["1. Name's. of Vessel : OB PUTRA GANDUS\nVoyage : 022/P.G", 'OB PUTRA GANDUS'],
    'loading order' => ['To The Master OF: OB PUTRA GANDUS No. : 0054/KPI45120', 'OB PUTRA GANDUS'],
    'misread OB prefix' => ['Vessel No : O08 PUTRA GANDUS', 'OB PUTRA GANDUS'],
]);

test('sentences mentioning a vessel are not taken as its name', function () {
    expect(vesselName('Weight, quantity, quality unknown. Vessel not accountable for Leakage'))->toBeNull();
});

test('vessel name is found by its prefix when the label is unreadable', function () {
    // Manifest hasil foto: label "Name of Vessel" rusak, nama kapal tetap terbaca
    $text = "el: TKG. PT PATRA ZALVA\nPort of Discharge\nDESCRIPTION OF CARGO\nTKG. PT PATRA ZALVA\nTB. OCEAN VENTURE Ill";

    expect(vesselName($text))->toBe('TKG. PT PATRA ZALVA');
});

test('a cargo vessel is preferred over the tug boat stamped on every page', function () {
    $text = "TB. OCEAN VENTURE\nTB. OCEAN VENTURE\nTB. OCEAN VENTURE\nLOADING TO : TKG. PT PATRA ZALVA";

    expect(vesselName($text))->toBe('TKG. PT PATRA ZALVA');
});

test('the tug boat is used when no cargo vessel is found', function () {
    expect(vesselName("Received by\nTB. OCEAN VENTURE Ill\nMaster"))->toBe('TB. OCEAN VENTURE Ill');
});

test('vessel names never run onto the next line', function () {
    expect(vesselName("TKG. PT PATRA ZALVA\nReff No : 1234"))->toBe('TKG. PT PATRA ZALVA');
});
