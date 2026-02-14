# PipraPay Gateway Management Guide

## Overview

This guide covers managing payment gateways through PipraPay's centralized payment system. PipraPay supports unlimited payment gateways that can be added and managed through the PipraPay admin panel without any code changes.

## Table of Contents
1. [Understanding Gateways](#understanding-gateways)
2. [Gateway Discovery](#gateway-discovery)
3. [Gateway Configuration](#gateway-configuration)
4. [Working with Gateways](#working-with-gateways)
5. [Gateway Caching](#gateway-caching)
6. [Gateway Filtering](#gateway-filtering)
7. [Adding New Gateways](#adding-new-gateways)

## Understanding Gateways

### Gateway Structure

Each gateway in PipraPay has the following properties:

```php
[
    'code' => 'bkash',              // Unique gateway identifier
    'name' => 'bKash',              // Display name
    'active' => true,                // Whether gateway is active
    'icon' => 'bkash.png',          // Gateway icon/image
    'currencies' => ['BDT'],         // Supported currencies
    'features' => ['refund'],        // Available features
    'metadata' => [...],             // Additional metadata
    'min_amount' => 10.00,          // Minimum transaction amount
    'max_amount' => 50000.00,       // Maximum transaction amount
    'region' => 'bangladesh',       // Geographic region
    'type' => 'payment'             // Gateway type
]
```

### Gateway Object

The SDK provides a `Gateway` object for easier gateway management:

```php
use PipraPay\Gateway;

$gateway = Gateway::fromArray($data);

echo $gateway->getName();              // Get gateway name
echo $gateway->getCode();              // Get gateway code
echo $gateway->isActive();            // Check if active
echo $gateway->getIcon();              // Get icon URL
$gateway->getCurrencies();            // Get supported currencies
$gateway->supportsCurrency('BDT');    // Check currency support
$gateway->hasFeature('refund');       // Check feature availability
$gateway->isValidAmount(1000);       // Check if amount is valid
```

## Gateway Discovery

### Fetch All Gateways

```php
use PipraPay\PipraPayClient;

$client = new PipraPayClient($config);

// Get all active gateways (with cache)
$response = $client->getGateways();

if ($response['success']) {
    $gateways = $response['gateways'];

    foreach ($gateways as $gateway) {
        echo $gateway->getName() . "\n";
    }
}
```

### Fetch All Gateways Including Inactive

```php
$response = $client->getGateways($useCache = true, $activeOnly = false);
```

### Fetch Without Cache

```php
$response = $client->getGateways($useCache = false);
```

### Get Gateway Collection

```php
$collection = $client->getGatewayCollection();

// Returns GatewayCollection object with powerful filtering
```

### Get Gateways for Specific Currency

```php
$response = $client->getGatewaysForCurrency('BDT');

if ($response['success']) {
    $gateways = $response['gateways'];

    // Only gateways supporting BDT
}
```

## Gateway Configuration

### Validate Gateway Configuration

```php
$validation = $client->validateGatewayConfig('bkash', [
    'amount' => 1000,
    'currency' => 'BDT'
]);

if ($validation['success']) {
    echo "Configuration valid";
} else {
    echo "Error: " . $validation['message'];
}
```

### Check Gateway Availability

```php
if ($client->isGatewayAvailable('bkash')) {
    // bKash is available and active
}
```

### Get Specific Gateway

```php
$gateway = $client->getGateway('bkash');

if ($gateway) {
    echo "Gateway: " . $gateway->getName();
    echo "Icon: " . $gateway->getIcon();
    echo "Currencies: " . implode(', ', $gateway->getCurrencies());
    echo "Features: " . implode(', ', $gateway->getFeatures());
}
```

## Working with Gateways

### Using GatewayCollection

The `GatewayCollection` class provides powerful methods for working with multiple gateways:

```php
$collection = $client->getGatewayCollection();

// Get active gateways
$activeGateways = $collection->getActive();

// Get gateways for specific currency
$bdtGateways = $collection->getByCurrency('BDT');

// Get gateways for specific region
$regionGateways = $collection->getByRegion('bangladesh');

// Get gateways with specific feature
$refundableGateways = $collection->withFeature('refund');

// Custom filtering
$filteredGateways = $collection->filter(function($gateway) {
    return $gateway->getMinAmount() <= 1000;
});

// Sort gateways
$sortedGateways = $collection->sortBy('name', 'asc');

// Find first gateway
$firstGateway = $collection->first();

// Find last gateway
$lastGateway = $collection->last();

// Find specific gateway
$found = $collection->find(function($gateway) {
    return $gateway->getCode() === 'bkash';
});

// Get gateway codes
$codes = $collection->getCodes();

// Get gateway names
$names = $collection->getNames();

// Convert to array
$array = $collection->toArray();

// Count gateways
$count = $collection->count();

// Check if empty
if ($collection->isEmpty()) {
    echo "No gateways available";
}

// Iterate through gateways
foreach ($collection as $gateway) {
    echo $gateway->getName() . "\n";
}
```

### Chaining Operations

```php
$collection = $client->getGatewayCollection();

// Get active BDT gateways with refund feature, sorted by name
$result = $collection
    ->getActive()
    ->getByCurrency('BDT')
    ->withFeature('refund')
    ->sortBy('name', 'asc');

foreach ($result as $gateway) {
    echo $gateway->getName() . "\n";
}
```

### Map Operations

```php
$collection = $client->getGatewayCollection();

// Map gateways to array of names
$names = $collection->map(function($gateway) {
    return $gateway->getName();
});

// Map to simple array
$simple = $collection->map(function($gateway) {
    return [
        'code' => $gateway->getCode(),
        'name' => $gateway->getName()
    ];
});
```

## Gateway Caching

### Enable Gateway Caching

```php
$config = [
    'gateway_cache_enabled' => true,
    'gateway_cache_ttl' => 3600,        // Cache for 1 hour
    'gateway_cache_file' => '/path/to/cache.json'
];

$client = new PipraPayClient($config);
```

### Refresh Gateway Cache

```php
// Force refresh from API
$response = $client->refreshGateways();
```

### Clear Gateway Cache

```php
// Clear cached gateway list
$client->clearGatewayCache();
```

### Get Cache Info

```php
$cache = $client->getGatewayCache();
$info = $cache->getCacheInfo();

print_r($info);
// Output:
// [
//     'enabled' => true,
//     'ttl' => 3600,
//     'cache_file' => '/path/to/cache.json',
//     'file_exists' => true,
//     'total_entries' => 2,
//     'expired_entries' => 0,
//     'file_size' => 1234,
//     'file_modified' => '2026-02-14 10:30:00'
// ]
```

### Manual Cache Management

```php
use PipraPay\GatewayCache;

$cache = new GatewayCache(
    '/path/to/cache.json',
    3600,    // TTL in seconds
    true     // Enabled
);

// Set cache
$cache->set('key', $data, 3600);

// Get cache (with callback if not exists)
$data = $cache->get('key', function() {
    // Fetch fresh data
    return fetchData();
});

// Check if cache exists
if ($cache->has('key')) {
    echo "Cache exists";
}

// Check if cache is expired
if ($cache->isExpired('key')) {
    echo "Cache expired";
}

// Delete specific cache
$cache->delete('key');

// Clear all cache
$cache->clear();

// Clear expired cache only
$cache->clearExpired();
```

## Gateway Filtering

### Filter by Currency

```php
$collection = $client->getGatewayCollection();

$bdtGateways = $collection->getByCurrency('BDT');
$usdGateways = $collection->getByCurrency('USD');
```

### Filter by Region

```php
$bdGateways = $collection->getByRegion('bangladesh');
$usGateways = $collection->getByRegion('usa');
```

### Filter by Type

```php
$paymentGateways = $collection->getByType('payment');
$walletGateways = $collection->getByType('wallet');
```

### Filter by Feature

```php
$refundableGateways = $collection->withFeature('refund');
$recurringGateways = $collection->withFeature('recurring');
$instantPayGateways = $collection->withFeature('instant_pay');
```

### Custom Filtering

```php
$filteredGateways = $collection->filter(function($gateway) {
    return $gateway->getMinAmount() <= 1000 &&
           $gateway->getMaxAmount() >= 10000 &&
           $gateway->hasFeature('refund');
});
```

### Filter by Multiple Conditions

```php
$filteredGateways = $collection
    ->getActive()
    ->getByCurrency('BDT')
    ->withFeature('refund')
    ->filter(function($gateway) {
        return $gateway->isValidAmount(5000);
    });
```

## Adding New Gateways

### Step 1: Configure Gateway in PipraPay Admin

1. Login to PipraPay admin panel
2. Navigate to Settings > Payment Gateways
3. Click "Add New Gateway"
4. Enter gateway details:
   - Gateway Code (unique identifier)
   - Gateway Name (display name)
   - Gateway Type
   - Supported Currencies
   - Min/Max Amounts
   - Region
   - Features
   - API Credentials

### Step 2: Enable Gateway

```php
// Gateway will automatically appear in your application
$gateways = $client->getGateways();

if ($gateways['success']) {
    foreach ($gateways['data'] as $gateway) {
        echo $gateway['name'] . " (" . $gateway['code'] . ")\n";
    }
}
```

### Step 3: Use New Gateway

```php
// No code changes needed - just use the gateway code
$payment = piprapay_create_payment(
    $invoice_id,
    $amount,
    'new_gateway_code'
);
```

### Gateway Registration Best Practices

1. **Use lowercase codes**: `bkash`, `nagad`, `stripe`
2. **Descriptive names**: "bKash Mobile Payment", "Nagad Digital Payment"
3. **Specify currency support**: Always list supported currencies
4. **Set appropriate limits**: Min/max amounts based on gateway capabilities
5. **Add features**: List all supported features (refund, recurring, etc.)
6. **Provide icon**: Add gateway logo/icon for better UX
7. **Test thoroughly**: Test gateway in sandbox mode first

## Common Gateway Operations

### Display Gateway Dropdown

```php
// Using helper function
echo piprapay_gateway_options_dropdown('', 'BDT', [
    'class' => 'form-control payment-gateway',
    'id' => 'gateway_select',
    'required' => true
]);

// Manual implementation
$gateways = piprapay_get_gateways_for_currency('BDT');

if ($gateways['success']) {
    echo '<select name="gateway" class="form-control">';
    echo '<option value="">Select Gateway</option>';

    foreach ($gateways['data'] as $gateway) {
        if ($gateway['active']) {
            echo "<option value=\"{$gateway['code']}\">{$gateway['name']}</option>";
        }
    }

    echo '</select>';
}
```

### Display Gateway List with Icons

```php
$gateways = piprapay_get_gateways();

if ($gateways['success']) {
    foreach ($gateways['data'] as $gateway) {
        if (!$gateway['active']) continue;

        echo '<div class="gateway-option">';
        echo '<img src="' . $gateway['icon'] . '" alt="' . $gateway['name'] . '">';
        echo '<span>' . $gateway['name'] . '</span>';

        if (!empty($gateway['currencies'])) {
            echo '<small>' . implode(', ', $gateway['currencies']) . '</small>';
        }

        echo '</div>';
    }
}
```

### Filter Gateways by User Location

```php
function getGatewaysForLocation($country, $currency) {
    $collection = piprapay_get_gateway_collection();

    // Get gateways for country/region and currency
    $gateways = $collection
        ->getActive()
        ->getByCurrency($currency)
        ->getByRegion(strtolower($country));

    return $gateways->toArray();
}

// Usage
$bdGateways = getGatewaysForLocation('bangladesh', 'BDT');
$usGateways = getGatewaysForLocation('usa', 'USD');
```

### Auto-select Best Gateway

```php
function autoSelectGateway($amount, $currency) {
    $collection = piprapay_get_gateway_collection();

    // Filter by currency and amount validity
    $validGateways = $collection
        ->getActive()
        ->getByCurrency($currency)
        ->filter(function($gateway) use ($amount) {
            return $gateway->isValidAmount($amount);
        });

    // Sort by preference (e.g., lowest min amount first)
    $sortedGateways = $validGateways->sortBy('min_amount', 'asc');

    // Return first gateway
    return $sortedGateways->first();
}

// Usage
$gateway = autoSelectGateway(1000, 'BDT');
if ($gateway) {
    echo "Best gateway: " . $gateway->getName();
}
```

## Performance Tips

1. **Enable Caching**: Always enable gateway caching in production
2. **Use Collection Methods**: Leverage GatewayCollection for filtering instead of loops
3. **Batch Operations**: Get all gateways once and filter in memory
4. **Set Appropriate TTL**: Cache for 1-24 hours depending on how often gateways change
5. **Monitor Cache Size**: Check cache file size regularly

## Troubleshooting

### Gateway Not Showing

```php
// Check if gateway exists
$gateway = $client->getGateway('bkash');

if (!$gateway) {
    echo "Gateway not found";
} elseif (!$gateway->isActive()) {
    echo "Gateway exists but is inactive";
}

// Refresh cache
$client->refreshGateways();
```

### Currency Not Supported

```php
// Get gateways for currency
$response = $client->getGatewaysForCurrency('BDT');

if (!$response['success']) {
    echo "Error: " . $response['message'];
} elseif ($response['gateways']->isEmpty()) {
    echo "No gateways support BDT currency";
}
```

### Cache Issues

```php
// Clear cache
$client->clearGatewayCache();

// Disable caching temporarily
$client->setGatewayCacheEnabled(false);
$gateways = $client->getGateways(false);
$client->setGatewayCacheEnabled(true);

// Check cache info
$cache = $client->getGatewayCache();
$info = $cache->getCacheInfo();
print_r($info);
```

## Best Practices

1. **Always validate** gateway configuration before creating payments
2. **Use appropriate caching** - balance freshness and performance
3. **Filter by currency** - show only relevant gateways to users
4. **Handle errors gracefully** - show user-friendly messages
5. **Log gateway issues** - track which gateways fail most often
6. **Monitor gateway performance** - track success rates and response times
7. **Test new gateways** - always test in sandbox before production
8. **Keep SDK updated** - ensure you have the latest gateway features

## Support

For issues related to:
- **Gateway Configuration**: PipraPay Admin Panel
- **SDK Usage**: SDK Documentation
- **API Issues**: PipraPay API Reference
- **Technical Support**: [email protected]
