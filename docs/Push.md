## APNs Setup

To send push notifications to update Wallet passes, you need an APNs Auth Key from your Apple Developer account.

### Steps to obtain the APNs Auth Key

1. **Sign in to the Apple Developer Portal**  
   Go to [Apple Developer Account](https://developer.apple.com/account).

2. **Navigate to "Certificates, Identifiers & Profiles"**  
   Select **Keys** in the left menu.

3. **Create a new key**  
   - Click the **+** button to add a new key.  
   - Enter a **Key Name** (e.g., "Wallet Push Key").  
   - Enable **Apple Push Notifications service (APNs)**.  
   - Click **Continue**, then **Register**.

4. **Download the AuthKey**  
   - After registration, download the `.p8` file (e.g., `AuthKey_XXXXXXXXXX.p8`).  
   - This file can only be downloaded once — keep it safe.

5. **Get your identifiers**  
   - **Team ID** → Available in the top-right corner of your developer account.  
   - **Key ID** → Visible in the **Keys** list.  
   - **Bundle ID / Pass Type Identifier** → The identifier you used when creating your Wallet Pass certificate (e.g., `pass.com.example.demo`).  

6. **Use the AuthKey in your code**  
   Place the `.p8` file in your project and configure the `Push` class with the correct path, `teamId`, `keyId`, and `bundleId`.

---

✅ With this setup, your project will be able to authenticate against APNs and send Wallet push updates.

## Push updates to Wallet (APNs)

Apple Wallet passes can be updated via push notifications through the Apple Push Notification Service (APNs).
This allows you to notify devices that a pass has changed (e.g. new balance, flight delay, order update).

### Usage

The new `push` method can be used to send a notification for a given pass:

```php
use PKPass\Push;

$push = new Push([
    'teamId'        => 'ABCD1234XY',           // Apple Developer Team ID
    'keyId'         => 'XYZ9876543',           // Key ID from Apple Developer portal
    'authKey'       => '/path/AuthKey.p8',     // Path to the AuthKey file
    'bundleId'      => 'pass.com.example.demo' // Your Pass Type Identifier
]);

$deviceToken = 'abcdef1234567890...'; // Obtained when device registers
$title       = '1234567890';          // Title
$body        = '1234567890';          // Body

$push->send($deviceToken, $title, $body);
