# UddoktaPay Hosted Gateway for Paymenter (v1+)

Integrate UddoktaPay Hosted into your Paymenter store to accept payments through a secure, redirect-based checkout experience. No need to worry about IPN configuration — everything is handled automatically.

## Features

* Supports Paymenter v1 and above
* Hosted payment flow with automatic redirection
* Auto-verification after successful payment
* No manual IPN setup required
* Quick and easy configuration
* Supports multiple currencies and payment methods via UddoktaPay

## Installation

1. Upload the `UddoktaPay` folder to `extensions/Gateways`
2. Open your Paymenter Admin Panel
3. Go to Gateways > Add New
4. Select "UddoktaPay Hosted"
5. Enter your API key, secret, and hosted URL
6. Save and you're ready to accept payments

## Requirements

* UddoktaPay Hosted account with API access
* Valid API key and Base URL

## How It Works

1. User selects UddoktaPay at checkout
2. Redirects to UddoktaPay hosted payment page
3. After payment, UddoktaPay automatically triggers verification
4. Paymenter updates the order status accordingly

## Support

For any issues or questions, open an issue in this repository or contact your UddoktaPay administrator.