# CRM - Comprehensive Business Management Platform

A full-featured CRM and business management system built with **PHP (CodeIgniter 3.x)** and **MySQL/MariaDB**. This platform combines customer relationship management, sales automation, billing, payment processing, employee management, attendance tracking, and administrative tools in a single integrated solution.

---

## 🚀 Features Overview

### 📊 Core CRM Functionality
- **Customer & Lead Management** - Complete contact lifecycle from lead capture to customer conversion
- **Sales Pipeline** - Track opportunities, generate offers/quotes, manage deal progress
- **Client Profiles** - Organizations, contacts, communication history, and account management

### 💰 Billing & Payment Processing
- **Invoice Management** - Generate, track, and manage invoices with payment status
- **Payment Gateway Integration** - [PipraPay](https://piprapay.com) integration supporting:
  - 🇧🇩 **bKash** - Bangladeshi Mobile Financial Service
  - 🇧🇩 **Nagad** - Bangladeshi Digital Financial Service
  - 🌍 **Stripe** - International Payment Gateway
- **Dynamic Gateway Discovery** - Add new payment gateways without code changes
- **Webhook Handling** - Real-time payment notifications and verification
- **Refund Processing** - Full refund workflow support

### 👥 Employee & HR Management
- **Employee Records** - Staff profiles, designations, departments
- **Role-Based Access Control** - Admin panels, permission management, module-level access
- **Attendance Tracking** - Time and attendance monitoring

### 🏢 Biometric Attendance Integration
- **ZKTeco Device Support** - Biometric check-in/check-out synchronization
- **Automated Sync** - Real-time attendance data import
- **Time Tracking** - Employee work hours monitoring

### 📁 File & Document Management
- **elFinder Integration** - Full-featured file manager
- **Upload Management** - Organized file storage and retrieval
- **Document Preview** - Browse and preview files in-browser

### 📄 PDF Generation & Reporting
- **Dompdf Integration** - HTML to PDF conversion
- **Invoice PDFs** - Professional invoice generation
- **Report Export** - Export reports and documents as PDF

### 🌐 Domain, Hosting & Provider Management
- **Domain Records** - Client domain portfolio management
- **Hosting Management** - Hosting service tracking
- **Provider Database** - Service provider information

### 🛠️ Administrative Tools
- **Database Migration & Setup** - Automated schema management
- **Verification Scripts** - Installation health checks
- **Audit & Repair Utilities** - Database integrity tools
- **Environment Configuration** - Multi-environment support (dev/test/prod)

---

## 🛠 Technology Stack

| Layer | Technology |
|-------|------------|
| **Backend** | PHP 7.4+, CodeIgniter 3.x |
| **Database** | MySQL / MariaDB |
| **Frontend** | HTML5, CSS3, JavaScript (jQuery, Bootstrap) |
| **PDF Generation** | Dompdf |
| **File Manager** | elFinder |
| **Payment SDK** | PipraPay SDK (standalone, reusable) |
| **Biometric** | ZKTeco / zklib-js |
| **Dependencies** | Composer (PHP), npm (biometric-sync) |

---

## 📦 Project Structure

```
tic_crm/
├── application/                 # CodeIgniter application core
│   ├── config/                 # Configuration files
│   ├── controllers/            # MVC Controllers
│   ├── libraries/              # Core libraries (Piprapay, etc.)
│   ├── helpers/                # Helper functions
│   ├── models/                 # Data models
│   ├── views/                  # View templates
│   ├── migrations/             # Database migrations
│   └── third_party/            # Third-party integrations
├── assets/                     # Frontend assets (CSS, JS, images)
├── plugins/                    # jQuery plugins and UI components
├── modules/                    # Modular extensions (WooCommerce, etc.)
├── piprapay-sdk/               # Standalone PipraPay PHP SDK
│   ├── src/PipraPay/          # SDK core classes
│   ├── examples/              # Usage examples
│   └── docs/                  # SDK documentation
├── biometric-sync/             # Node.js biometric sync service
├── filemanager/                # elFinder file manager instances
├── uploads/                    # User uploads
├── docs/                       # Project documentation
├── vendor/                     # Composer dependencies
└── index.php                   # Entry point
```

---

## 📋 Requirements

### CRM Application
- **PHP** >= 7.4
- **CodeIgniter** 3.x
- **MySQL/MariaDB** 5.7+
- **Extensions**: cURL, JSON, OpenSSL, mbstring
- **Web Server**: Apache (mod_rewrite) or Nginx
- **SSL/TLS** certificate (required for production)

### PipraPay Server (Payment Gateway)
- **PHP** >= 7.4
- **MySQL/MariaDB** 5.7+
- **Extensions**: cURL, JSON, OpenSSL
- **SSL/TLS** certificate (required)
- Dedicated subdomain (e.g., `payment.yourdomain.com`)

### PipraPay SDK (Standalone)
- **PHP** >= 7.4
- **Extensions**: cURL, JSON
- **Composer** (optional, for autoloading)

### Biometric Sync Service
- **Node.js** >= 16.x
- **npm** dependencies (see `biometric-sync/package.json`)

---

## ⚙️ Installation & Setup

### 1. Clone the Repository
```bash
git clone <repository-url> tic_crm
cd tic_crm
```

### 2. Configure Environment
```bash
# Copy and edit configuration
cp application/config/config.php.example application/config/config.php
cp application/config/database.php.example application/config/database.php
# Update database credentials, base_url, encryption_key, etc.
```

### 3. Install PHP Dependencies
```bash
composer install
```

### 4. Database Setup
```bash
# Option A: Run Migrations (Recommended)
php index.php migrate latest

# Option B: Manual SQL Import
# Import the SQL files in order:
# - domains_schema.sql
# - hostings_schema.sql
# - providers_schema.sql
# - setup_piprapay.sql (for payment tables)
```

### 5. Install PipraPay Server (For Payments)
1. Download PipraPay from [piprapay.com](https://piprapay.com)
2. Install on a subdomain (e.g., `payment.yourdomain.com`)
3. Create MySQL database for PipraPay
4. Run PipraPay installer and create admin account
5. Configure payment gateways (bKash, Nagad, Stripe) in PipraPay admin

### 6. Configure CRM Payment Integration
```bash
# Run setup verification
php setup_piprapay.php

# Or configure via Admin Panel:
# Settings > Payment Settings > PipraPay section
```

**Required PipraPay Credentials:**
- API URL: `https://payment.yourdomain.com/api/v1`
- API Key & Secret (from PipraPay Settings > API)
- Merchant ID (from PipraPay Settings > API)
- Webhook Secret (from PipraPay Settings > Webhooks)

### 7. Configure Webhooks
In PipraPay Admin Panel:
- **Webhook URL**: `https://your-crm-domain.com/payment/piprapay/webhook`
- **Events**: Payment Completed, Payment Failed

### 8. Set Up Biometric Sync (Optional)
```bash
cd biometric-sync
npm install
# Configure config.json with ZKTeco device details
npm start
```

### 9. Configure Web Server
**Apache** - Ensure `mod_rewrite` is enabled and `.htaccess` is present
**Nginx** - Configure rewrite rules for CodeIgniter

---

## 🚀 Quick Start

See [QUICKSTART.md](QUICKSTART.md) for a step-by-step guide to getting PipraPay payments working.

### Key Documentation Files
| File | Description |
|------|-------------|
| [QUICKSTART.md](QUICKSTART.md) | Payment integration quick start |
| [ENHANCEMENT_SUMMARY.md](ENHANCEMENT_SUMMARY.md) | PipraPay SDK enhancement details |
| [PROJECT_FEATURES.md](docs/PROJECT_FEATURES.md) | Complete feature documentation |
| [ZKTECO_IMPLEMENTATION_GUIDE.md](ZKTECO_IMPLEMENTATION_GUIDE.md) | Biometric attendance setup |
| [PIPRAPAY_IMPLEMENTATION.md](PIPRAPAY_IMPLEMENTATION.md) | Detailed payment integration |

---

## 💳 Payment Integration (PipraPay)

The PipraPay integration is a **standalone, reusable SDK** that can be used in any PHP project.

### SDK Installation (Other Projects)
```bash
# Copy piprapay-sdk folder to your project
cd piprapay-sdk
composer install
```

### Basic Usage
```php
require_once 'vendor/autoload.php';

use PipraPay\PipraPayClient;
use PipraPay\PaymentRequest;

$client = new PipraPayClient([
    'api_url'     => 'https://payment.yourdomain.com/api/v1',
    'api_key'     => 'your_api_key',
    'api_secret'  => 'your_api_secret',
    'merchant_id' => 'your_merchant_id',
    'test_mode'   => true
]);

$request = new PaymentRequest();
$request->setAmount(1000)
        ->setCurrency('BDT')
        ->setInvoiceId('INV-001')
        ->setGateway('bkash')
        ->setCallbackUrl('https://yourdomain.com/callback')
        ->setSuccessUrl('https://yourdomain.com/success')
        ->setCancelUrl('https://yourdomain.com/cancel');

$response = $client->initiatePayment($request);

if ($response['success']) {
    header('Location: ' . $response['data']['payment_url']);
}
```

### Dynamic Gateway Discovery (No Code Changes!)
```php
// Get all available gateways for a currency
$gateways = $client->getGatewaysForCurrency('BDT');

// Filter by region, features, etc.
$collection = $client->getGatewayCollection()
    ->filterByRegion('BD')
    ->filterByFeature('recurring')
    ->sortBy('name');

// Just add gateways in PipraPay admin - they appear automatically!
```

See [piprapay-sdk/README.md](piprapay-sdk/README.md) for complete SDK documentation.

---

## 🔧 Configuration

### Main CRM Config (`application/config/config.php`)
```php
$config['base_url'] = 'https://yourdomain.com/';
$config['index_page'] = '';
$config['encryption_key'] = 'your-32-char-encryption-key';
```

### Database Config (`application/config/database.php`)
```php
$db['default'] = [
    'dsn'   => '',
    'hostname' => 'localhost',
    'username' => 'db_user',
    'password' => 'db_password',
    'database' => 'tic_crm',
    'dbdriver' => 'mysqli',
    'char_set' => 'utf8mb4',
    'dbcollat' => 'utf8mb4_general_ci',
];
```

### PipraPay Config (`application/config/piprapay.php`)
```php
$config['piprapay_enabled']            = TRUE;
$config['piprapay_api_url']            = 'https://payment.yourdomain.com/api/v1';
$config['piprapay_api_key']            = 'your_api_key';
$config['piprapay_api_secret']         = 'your_api_secret';
$config['piprapay_merchant_id']        = 'your_merchant_id';
$config['piprapay_webhook_secret']     = 'your_webhook_secret';
$config['piprapay_test_mode']          = TRUE;
$config['piprapay_gateway_cache_ttl']  = 3600;
$config['piprapay_gateway_cache_enabled'] = TRUE;
```

---

## 🔐 Security Best Practices

- **Never commit** API keys, secrets, or database credentials to version control
- Use **environment-specific** configuration files
- **Rotate API keys** regularly
- Use **HTTPS everywhere** in production
- **Verify webhook signatures** on all callbacks
- **Limit database permissions** - use least privilege
- **Enable SSL** for database connections
- **Regular backups** and security updates
- **Monitor logs** for suspicious activity

---

## 🧪 Testing

### Run Setup Verification
```bash
php setup_piprapay.php
php verify_installation.php
```

### Payment Flow Testing
1. Enable **Test Mode** in both CRM and PipraPay
2. Create a test invoice
3. Process test payment via bKash/Nagad/Stripe sandbox
4. Verify webhook delivery and transaction recording

---

## 📚 Documentation

- [Quick Start Guide](QUICKSTART.md)
- [Project Features](docs/PROJECT_FEATURES.md)
- [PipraPay Enhancement Summary](ENHANCEMENT_SUMMARY.md)
- [PipraPay Implementation](PIPRAPAY_IMPLEMENTATION.md)
- [ZKTeco Biometric Guide](ZKTECO_IMPLEMENTATION_GUIDE.md)
- [Quick Reference](QUICK_REFERENCE.md)
- [SDK Documentation](piprapay-sdk/README.md)
- [SDK CodeIgniter Integration](piprapay-sdk/docs/codeigniter-integration.md)
- [SDK Gateway Management](piprapay-sdk/docs/gateway-management.md)

---

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

---

## 📄 License

This project is proprietary software. All rights reserved.

---

## 📞 Support

- **PipraPay Server**: [piprapay.com/support](https://piprapay.com/support)
- **General CRM Issues**: Create an issue in this repository
- **Email**: [support@tic-crm.com](mailto:support@tic-crm.com)

---

## 🙏 Acknowledgments

- [CodeIgniter](https://codeigniter.com/) - PHP Framework
- [PipraPay](https://piprapay.com/) - Payment Gateway Platform
- [elFinder](https://studio-42.github.io/elFinder/) - File Manager
- [Dompdf](https://github.com/dompdf/dompdf) - PDF Generation
- [ZKTeco](https://www.zkteco.com/) - Biometric Devices
- All open-source libraries used in this project

---

**Built with ❤️ for comprehensive business management**
