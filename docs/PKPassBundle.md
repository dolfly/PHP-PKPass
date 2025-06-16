# PKPassBundle API Documentation

The `PKPassBundle` class allows you to create a bundle of multiple passes, which can be output as a `.pkpasses` file. This is useful when you need to distribute multiple passes together.

## Table of Contents

- [Constructor](#constructor)
- [Bundle Management](#bundle-management)
- [Output Methods](#output-methods)
- [Constants](#constants)

## Constructor

### `__construct()`

Creates a new PKPassBundle instance.

**Example:**
```php
use PKPass\PKPassBundle;

$bundle = new PKPassBundle();
```

## Bundle Management

### `add(PKPass $pass)`

Adds a pass to the bundle.

**Parameters:**
- `$pass` (PKPass): A PKPass instance to add to the bundle

**Throws:** `InvalidArgumentException` if the parameter is not a PKPass instance

**Example:**
```php
use PKPass\PKPass;
use PKPass\PKPassBundle;

// Create individual passes
$pass1 = new PKPass('/path/to/certificate.p12', 'password');
$pass1->setData($passData1);
$pass1->addFile('/path/to/icon1.png');

$pass2 = new PKPass('/path/to/certificate.p12', 'password');
$pass2->setData($passData2);
$pass2->addFile('/path/to/icon2.png');

// Create bundle and add passes
$bundle = new PKPassBundle();
$bundle->add($pass1);
$bundle->add($pass2);
```

## Output Methods

### `save($path)`

Saves the bundle as a `.pkpasses` file to the filesystem.

**Parameters:**
- `$path` (string): File path where the bundle should be saved

**Throws:** `RuntimeException` if the file cannot be written

**Example:**
```php
$bundle->save('/path/to/my-passes.pkpasses');
```

### `output()`

Outputs the bundle as a `.pkpasses` file directly to the browser for download.

This method sets appropriate HTTP headers and streams the file content directly to the browser, then terminates the script with `exit`.

**Headers set:**
- `Content-Type: application/vnd.apple.pkpasses`
- `Content-Disposition: attachment; filename="passes.pkpasses"`
- `Cache-Control: no-cache, no-store, must-revalidate`
- `Pragma: no-cache`

**Throws:** `RuntimeException` if the stream cannot be created

**Example:**
```php
// This will trigger a download in the browser
$bundle->output();
// Script execution stops here
```

## Complete Example

Here's a complete example showing how to create a bundle with multiple passes:

```php
use PKPass\PKPass;
use PKPass\PKPassBundle;

try {
    // Create first pass (boarding pass)
    $boardingPass = new PKPass('/path/to/certificate.p12', 'password');
    $boardingPass->setData([
        'description' => 'Flight Ticket',
        'formatVersion' => 1,
        'organizationName' => 'Airline Inc',
        'passTypeIdentifier' => 'pass.com.airline.boarding',
        'serialNumber' => 'FLIGHT001',
        'teamIdentifier' => 'TEAM123',
        'boardingPass' => [
            'primaryFields' => [
                [
                    'key' => 'destination',
                    'label' => 'TO',
                    'value' => 'SFO'
                ]
            ]
        ]
    ]);
    $boardingPass->addFile('/path/to/flight-icon.png', 'icon.png');

    // Create second pass (event ticket)
    $eventPass = new PKPass('/path/to/certificate.p12', 'password');
    $eventPass->setData([
        'description' => 'Concert Ticket',
        'formatVersion' => 1,
        'organizationName' => 'Music Venue',
        'passTypeIdentifier' => 'pass.com.venue.event',
        'serialNumber' => 'EVENT001',
        'teamIdentifier' => 'TEAM123',
        'eventTicket' => [
            'primaryFields' => [
                [
                    'key' => 'event',
                    'label' => 'EVENT',
                    'value' => 'Rock Concert'
                ]
            ]
        ]
    ]);
    $eventPass->addFile('/path/to/concert-icon.png', 'icon.png');

    // Create bundle and add passes
    $bundle = new PKPassBundle();
    $bundle->add($boardingPass);
    $bundle->add($eventPass);

    // Save to file
    $bundle->save('/path/to/travel-bundle.pkpasses');

    // Or output directly to browser
    // $bundle->output();

} catch (Exception $e) {
    echo 'Error creating bundle: ' . $e->getMessage();
}
```

## Error Handling

The PKPassBundle class can throw several types of exceptions:

- `InvalidArgumentException`: When trying to add something other than a PKPass instance
- `RuntimeException`: When ZIP operations fail or file operations fail

Always wrap bundle operations in try-catch blocks:

```php
try {
    $bundle = new PKPassBundle();
    $bundle->add($pass1);
    $bundle->add($pass2);
    $bundle->save('/path/to/bundle.pkpasses');
} catch (InvalidArgumentException $e) {
    echo 'Invalid pass object: ' . $e->getMessage();
} catch (RuntimeException $e) {
    echo 'Bundle creation failed: ' . $e->getMessage();
}
```

## File Format

The `.pkpasses` file is a ZIP archive containing multiple `.pkpass` files. Each individual pass in the bundle is a complete, signed pass that could be distributed independently. The bundle format allows iOS to import multiple passes at once when the user opens the `.pkpasses` file.

Note that this format is only supported by iOS Safari. No other browsers or platforms support the `.pkpasses` format for direct import into Apple Wallet.
