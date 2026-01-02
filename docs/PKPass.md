# PKPass API Documentation

The `PKPass` class is the main class for creating Apple Wallet passes. It handles the creation, signing, and packaging of passes as `.pkpass` files according to Apple's Wallet specifications.

## Table of Contents

- [Constructor](#constructor)
- [Configuration Methods](#configuration-methods)
- [Data Management](#data-management)
- [File Management](#file-management)
- [Localization](#localization)
- [Pass Creation](#pass-creation)
- [Constants](#constants)

## Constructor

### `__construct($certificatePath = null, $certificatePassword = null)`

Creates a new PKPass instance.

**Parameters:**
- `$certificatePath` (string|bool, optional): Path to the P12 certificate file
- `$certificatePassword` (string|bool, optional): Password for the certificate

**Example:**
```php
use PKPass\PKPass;

// Create with certificate
$pass = new PKPass('/path/to/certificate.p12', 'password');

// Create without certificate (set later)
$pass = new PKPass();
```

## Configuration Methods

### `setCertificatePath($path)`

Sets the path to the certificate file.

**Parameters:**
- `$path` (string): Path to the P12 certificate file

**Returns:** `bool` - Always returns true

**Example:**
```php
$pass->setCertificatePath('/path/to/certificate.p12');
```

### `setCertificateString($p12_string)`
Sets the certificate from a string.

If specified, this overrides any previously set certificate path.

**Parameters:**
- `$p12_string` (string): The P12 certificate content as a string

**Returns:** `bool` - Always returns true

### `setCertificatePassword($password)`

Sets the certificate password.

**Parameters:**
- `$password` (string): Password for the certificate

**Returns:** `bool` - Always returns true

**Example:**
```php
$pass->setCertificatePassword('mypassword');
```

### `setWwdrCertificatePath($path)`

Sets the path to the Apple WWDR Intermediate certificate.

**Parameters:**
- `$path` (string): Path to the WWDR certificate

**Returns:** `bool` - Always returns true

**Example:**
```php
$pass->setWwdrCertificatePath('/path/to/AppleWWDRCA.pem');
```

### `setTempPath($path)`

Sets the path to the temporary directory for file operations.

**Parameters:**
- `$path` (string): Path to temporary directory

**Example:**
```php
$pass->setTempPath('/tmp/pkpass');
```

## Data Management

### `setData($data)`

Sets the pass data (the main JSON content of the pass).

**Parameters:**
- `$data` (string|array|object): Pass data as JSON string, array, or object

**Throws:** `PKPassException` if data is invalid

**Example:**
```php
$passData = [
    'description' => 'My Store Card',
    'formatVersion' => 1,
    'organizationName' => 'My Company',
    'passTypeIdentifier' => 'pass.com.mycompany.mypass',
    'serialNumber' => '123456',
    'teamIdentifier' => 'TEAM123456',
    'storeCard' => [
        'primaryFields' => [
            [
                'key' => 'balance',
                'label' => 'Balance',
                'value' => '$25.00'
            ]
        ]
    ]
];

$pass->setData($passData);
```

### `setName($name)`

Sets the filename for the generated pass.

**Parameters:**
- `$name` (string): Filename (without extension)

**Example:**
```php
$pass->setName('my-store-card');
```

### `getName()`

Gets the filename for the generated pass.

**Returns:** `string` - The filename with extension

**Example:**
```php
$filename = $pass->getName(); // Returns 'my-store-card.pkpass'
```

## File Management

### `addFile($path, $name = null)`

Adds a file to the pass package.

**Parameters:**
- `$path` (string): Path to the file
- `$name` (string, optional): Name to use in the pass archive (defaults to basename of path)

**Throws:** `PKPassException` if file doesn't exist

**Example:**
```php
$pass->addFile('/path/to/icon.png');
$pass->addFile('/path/to/logo.png', 'logo.png');
```

### `addRemoteFile($url, $name = null)`

Adds a file from a URL to the pass package.

**Parameters:**
- `$url` (string): URL to the file
- `$name` (string, optional): Name to use in the pass archive (defaults to basename of URL)

**Example:**
```php
$pass->addRemoteFile('https://example.com/icon.png');
$pass->addRemoteFile('https://example.com/logo.png', 'custom-logo.png');
```

### `addFileContent($content, $name)`

Adds a file from string content to the pass package.

**Parameters:**
- `$content` (string): File content
- `$name` (string): Filename to use in the pass archive

**Example:**
```php
$svgContent = '<svg>...</svg>';
$pass->addFileContent($svgContent, 'logo.svg');
```

## Localization

### `addLocaleStrings($language, $strings = [])`

Adds localized strings for a specific language.

**Parameters:**
- `$language` (string): Language code (e.g., 'en', 'fr', 'de')
- `$strings` (array): Key-value pairs of translation strings

**Throws:** `PKPassException` if strings are empty or not an array

**Example:**
```php
$pass->addLocaleStrings('en', [
    'BALANCE' => 'Balance',
    'POINTS' => 'Points'
]);

$pass->addLocaleStrings('fr', [
    'BALANCE' => 'Solde',
    'POINTS' => 'Points'
]);
```

### `addLocaleFile($language, $path, $name = null)`

Adds a localized file for a specific language.

**Parameters:**
- `$language` (string): Language code
- `$path` (string): Path to the file
- `$name` (string, optional): Name to use in the pass archive

**Throws:** `PKPassException` if file doesn't exist

**Example:**
```php
$pass->addLocaleFile('en', '/path/to/en/logo.png');
$pass->addLocaleFile('fr', '/path/to/fr/logo.png', 'logo.png');
```

### `addLocaleRemoteFile($language, $url, $name = null)`

Adds a localized file from a URL for a specific language.

**Parameters:**
- `$language` (string): Language code
- `$url` (string): URL to the file
- `$name` (string, optional): Name to use in the pass archive

**Example:**
```php
$pass->addLocaleRemoteFile('en', 'https://example.com/en/logo.png');
$pass->addLocaleRemoteFile('fr', 'https://example.com/fr/logo.png', 'logo.png');
```

### `addLocaleFileContent($language, $content, $name)`

Adds a localized file from string content for a specific language.

**Parameters:**
- `$language` (string): Language code
- `$content` (string): File content
- `$name` (string): Filename to use in the pass archive

**Example:**
```php
$frenchContent = 'Localized content in French';
$pass->addLocaleFileContent('fr', $frenchContent, 'terms.txt');
```

## Pass Creation

### `create($output = false)`

Creates the actual `.pkpass` file.

**Parameters:**
- `$output` (bool, optional): Whether to output directly to browser (true) or return as string (false)

**Returns:** `string` - The pass content as binary string (if $output is false)

**Throws:** `PKPassException` if pass creation fails

**Example:**
```php
// Save to file
$passContent = $pass->create(false);
file_put_contents('mypass.pkpass', $passContent);

// Output directly to browser
$pass->create(true);
```

## Constants

### Class Constants

- `FILE_TYPE` - 'pass' - The type identifier for passes
- `FILE_EXT` - 'pkpass' - The file extension for passes
- `MIME_TYPE` - 'application/vnd.apple.pkpass' - The MIME type for passes

**Example:**
```php
echo PKPass::MIME_TYPE; // 'application/vnd.apple.pkpass'
```

## Required Files

Every pass must include an `icon.png` file. The class will throw a `PKPassException` if this file is missing.

**Recommended files:**
- `icon.png` (required) - 29×29 points
- `icon@2x.png` - 58×58 points  
- `icon@3x.png` - 87×87 points
- `logo.png` - 160×50 points (max)
- `logo@2x.png` - 320×100 points (max)
- `logo@3x.png` - 480×150 points (max)

## Error Handling

All methods that can fail will throw a `PKPassException`. Always wrap critical operations in try-catch blocks:

```php
try {
    $pass->setData($passData);
    $pass->addFile('/path/to/icon.png');
    $passContent = $pass->create(false);
} catch (PKPassException $e) {
    echo 'Error: ' . $e->getMessage();
}
```
