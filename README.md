# My Utility Genius

## Introduction

Library for working with My Utility Genius API.

## Config

You may publish the config file as follows:

```bash
php artisan vendor:publish --tag=mug-config
```

Add the following items to your .env file:

```dotenv
MUG_ENDPOINT="${APP_ENV}"
MUG_CLIENT_ID=
MUG_SECRET=
```

My Utility Genius will supply a Client ID and Secret.

## Usage

Here are how the basic functions of the library work:

```php
// Determine whether a post code is ready to switch
Mug::addressPostcodeReady($this->postCode);

// Retrieve addresses matched by a post code
Mug::addressRecco($this->postCode);

// Retrieve details about a specific address
Mug::addressReccoDetails($address);

// $address attribute is an array which must contain
// mpancore and xoserveAddressCode elements.

// You may supply an entry from the collection
// returned by Mug::addressRecco()
Mug::addressReccoDetails(
    Mug::addressRecco($this->postCode)
        ->first()
);

```

## Security Vulnerabilities

Please [e-mail security vulnerabilities directly to me](mailto:matt@mralston.co.uk).

## Licence

PDF is open-sourced software licenced under the [MIT license](LICENSE.md).
