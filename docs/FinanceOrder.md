# FinanceOrder API Documentation

The `FinanceOrder` class extends `PKPass` to create Apple Wallet Orders for financial transactions. It inherits all functionality from PKPass but generates orders with the `.order` extension and uses SHA-256 hashing instead of SHA-1.

## Table of Contents

- [Overview](#overview)
- [Constructor](#constructor)
- [Inherited Methods](#inherited-methods)
- [Constants](#constants)
- [Key Differences from PKPass](#key-differences-from-pkpass)
- [Usage Example](#usage-example)
- [Error Handling](#error-handling)

## Overview

The FinanceOrder class is designed for creating Apple Wallet Orders, which are used for financial transactions like purchases, invoices, and receipts. Orders differ from passes in that they use:

- SHA-256 hashing algorithm (instead of SHA-1)
- `order.json` as the payload file (instead of `pass.json`)
- `.order` file extension (instead of `.pkpass`)
- `application/vnd.apple.finance.order` MIME type

## Constructor

### `__construct($certificatePath = null, $certificatePassword = null)`

Creates a new FinanceOrder instance. Same parameters as PKPass.

**Parameters:**
- `$certificatePath` (string|bool, optional): Path to the P12 certificate file
- `$certificatePassword` (string|bool, optional): Password for the certificate

**Example:**
```php
use PKPass\FinanceOrder;

// Create with certificate
$order = new FinanceOrder('/path/to/certificate.p12', 'password');

// Create without certificate (set later)
$order = new FinanceOrder();
```

## Inherited Methods

The FinanceOrder class inherits all public methods from PKPass:

### Configuration Methods
- `setCertificatePath($path)` - Sets the path to the certificate file
- `setCertificatePassword($password)` - Sets the certificate password
- `setWwdrCertificatePath($path)` - Sets the path to the WWDR certificate
- `setTempPath($path)` - Sets the temporary directory path

### Data Management
- `setData($data)` - Sets the order data (JSON content)
- `setName($name)` - Sets the filename for the generated order
- `getName()` - Gets the filename for the generated order

### File Management
- `addFile($path, $name = null)` - Adds a file to the order package
- `addRemoteFile($url, $name = null)` - Adds a file from a URL
- `addFileContent($content, $name)` - Adds file content directly as a string

### Localization
- `addLocaleStrings($language, $strings = [])` - Adds localized strings
- `addLocaleFile($language, $path, $name = null)` - Adds a localized file
- `addLocaleRemoteFile($language, $url, $name = null)` - Adds a localized file from URL
- `addLocaleFileContent($language, $content, $name)` - Adds localized file content

### Order Creation
- `create($output = false)` - Creates the actual `.order` file

## Constants

### Class Constants

- `FILE_TYPE` - 'order' - The type identifier for orders
- `FILE_EXT` - 'order' - The file extension for orders
- `MIME_TYPE` - 'application/vnd.apple.finance.order' - The MIME type for orders
- `PAYLOAD_FILE` - 'order.json' - The main payload file name
- `HASH_ALGO` - 'sha256' - The hashing algorithm used

**Example:**
```php
echo FinanceOrder::MIME_TYPE; // 'application/vnd.apple.finance.order'
echo FinanceOrder::HASH_ALGO; // 'sha256'
```

## Key Differences from PKPass

1. **File Extension**: Orders use `.order` instead of `.pkpass`
2. **MIME Type**: `application/vnd.apple.finance.order` instead of `application/vnd.apple.pkpass`
3. **Payload File**: `order.json` instead of `pass.json`
4. **Hash Algorithm**: SHA-256 instead of SHA-1 for better security
5. **Locale Files**: Uses `order.strings` instead of `pass.strings` for localization

## Usage Example

```php
use PKPass\FinanceOrder;

try {
    // Create order
    $order = new FinanceOrder('/path/to/certificate.p12', 'password');
    
    // Set order data
    $orderData = [
        'schemaVersion' => '1.0',
        'orderTypeIdentifier' => 'order.com.mycompany.myorder',
        'orderIdentifier' => 'ORDER12345',
        'webServiceURL' => 'https://example.com/orders/',
        'authenticationToken' => 'vxwxd7J8AlNNFPS8k0a0FfUFtq0ewzFdc',
        'merchant' => [
            'merchantIdentifier' => 'merchant.com.mycompany',
            'displayName' => 'My Company Store'
        ],
        'orderNumber' => 'ORD001',
        'createdAt' => '2024-01-01T12:00:00Z',
        'orderState' => 'open',
        'payment' => [
            'summaryItems' => [
                [
                    'label' => 'Subtotal',
                    'amount' => '10.00'
                ],
                [
                    'label' => 'Tax',
                    'amount' => '0.80'
                ],
                [
                    'label' => 'Total',
                    'amount' => '10.80'
                ]
            ]
        ]
    ];
    
    $order->setData($orderData);
    
    // Add required files
    $order->addFile('/path/to/icon.png');
    $order->addFile('/path/to/logo.png');
    
    // Add localization
    $order->addLocaleStrings('en', [
        'ORDER_TOTAL' => 'Total',
        'ORDER_DATE' => 'Order Date'
    ]);
    
    // Create and save the order
    $orderContent = $order->create(false);
    file_put_contents('myorder.order', $orderContent);
    
    // Or output directly to browser
    // $order->create(true);
    
} catch (PKPassException $e) {
    echo 'Error creating order: ' . $e->getMessage();
}
```

## Error Handling

FinanceOrder inherits the same error handling as PKPass. All methods that can fail will throw a `PKPassException`. Always wrap critical operations in try-catch blocks:

```php
try {
    $order->setData($orderData);
    $order->addFile('/path/to/icon.png');
    $orderContent = $order->create(false);
} catch (PKPassException $e) {
    echo 'Error: ' . $e->getMessage();
}
```

### Common Errors

- **Missing certificate**: Ensure certificate path and password are correct
- **Invalid order data**: Order data must be valid JSON or array
- **Missing icon.png**: Orders require an icon.png file
- **File not found**: Check that all file paths exist before adding them
- **Invalid localization**: Locale strings must be a non-empty array

## Required Files

Like passes, every order must include an `icon.png` file. The class will throw a `PKPassException` if this file is missing.

**Recommended files:**
- `icon.png` (required) - 29×29 points
- `icon@2x.png` - 58×58 points  
- `icon@3x.png` - 87×87 points
- `logo.png` - 160×50 points (max)
- `logo@2x.png` - 320×100 points (max)
- `logo@3x.png` - 480×150 points (max)
