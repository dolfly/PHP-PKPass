<?php

/**
 * Simple PKPassBundle example
 * 
 * This demonstrates how to create a bundle of multiple passes
 * and output them as a single .pkpasses file.
 */

use PKPass\PKPass;
use PKPass\PKPassBundle;

require('../vendor/autoload.php');

// Create a bundle
$bundle = new PKPassBundle();

// Create first pass - boarding pass
$pass1 = new PKPass('../Certificates.p12', 'password');
$pass1->setData([
    'description' => 'Flight to London',
    'formatVersion' => 1,
    'organizationName' => 'Flight Express',
    'passTypeIdentifier' => 'pass.com.includable.pkpass-example',
    'serialNumber' => 'FLIGHT001',
    'teamIdentifier' => '839X4P2FV8',
    'boardingPass' => [
        'primaryFields' => [
            [
                'key' => 'origin',
                'label' => 'San Francisco',
                'value' => 'SFO',
            ],
            [
                'key' => 'destination',
                'label' => 'London',
                'value' => 'LHR',
            ],
        ],
        'transitType' => 'PKTransitTypeAir',
    ],
    'barcodes' => [
        [
            'format' => 'PKBarcodeFormatQR',
            'message' => 'Flight-GateF12-ID6643679AH7B',
            'messageEncoding' => 'iso-8859-1',
        ]
    ],
    'backgroundColor' => 'rgb(32,110,247)'
]);
$pass1->addFile('images/icon.png');

// Create second pass - hotel booking
$pass2 = new PKPass('../Certificates.p12', 'password');
$pass2->setData([
    'description' => 'Hotel Reservation',
    'formatVersion' => 1,
    'organizationName' => 'London Hotel',
    'passTypeIdentifier' => 'pass.com.includable.pkpass-example',
    'serialNumber' => 'HOTEL001',
    'teamIdentifier' => '839X4P2FV8',
    'generic' => [
        'primaryFields' => [
            ['key' => 'hotel', 'label' => 'Hotel', 'value' => 'London Grand']
        ],
        'secondaryFields' => [
            ['key' => 'checkin', 'label' => 'Check-in', 'value' => 'Nov 7, 2024']
        ]
    ],
    'barcodes' => [['format' => 'PKBarcodeFormatQR', 'message' => 'HOTEL001']],
    'backgroundColor' => 'rgb(220,20,60)'
]);
$pass2->addFile('images/icon.png');

// Add passes to bundle
$bundle->add($pass1);
$bundle->add($pass2);

// Output the bundle to browser
$bundle->output();
