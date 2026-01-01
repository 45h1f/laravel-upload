# Laravel Secure File Upload & Media Guard

A robust, secure, and modern Laravel package for handling file uploads with virus scanning, MIME validation, and frontend integration.

## Features

- 🔒 **Secure Uploads**: MIME validation, extension checks.
- 🦠 **Virus Scanning**: Hookable interface (ClamAV ready).
- ⏱️ **Temporary Access**: Signed URLs for temporary file access.
- 🧹 **Orphan Cleanup**: Auto-delete temporary files via Artisan command.
- 🖼️ **Frontend Ready**: Drag & Drop components for Blade, React, and Vue.
- 🧩 **SOLID Architecture**: Swappable drivers.

## Installation

Add the package to your `composer.json` (if local) or install via composer.

```bash
composer require ashiful/upload
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=upload-config
```

## Customization

You can publish the views and assets to modify them:

```bash
# Publish views to resources/views/vendor/upload
php artisan vendor:publish --tag=upload-views

# Publish JS/Vue/React components to resources/js/vendor/ashiful/upload
php artisan vendor:publish --tag=upload-assets
```

## Usage

### Backend Service

```php
use Ashiful\Upload\Facades\SecureUpload;

// Basic Upload
$path = SecureUpload::upload($request->file('file'), 'uploads');

// Get Signed URL
$url = SecureUpload::getSignedUrl($path, 60); // 60 minutes
```

### Cleanup Command

Add to your `App\Console\Kernel.php` schedule or run manually:

```bash
php artisan upload:cleanup --hours=24
```

### Frontend Components

#### Blade
Include the Blade component in your view:
```blade
@include('upload::dropzone')
```

#### React
Import the component:
```jsx
import ReactUpload from '../../packages/ashiful/upload/resources/js/components/ReactUpload';

<ReactUpload uploadUrl="/upload/media" csrfToken={token} />
```

#### Vue
Import the component:
```vue
import VueUpload from '../../packages/ashiful/upload/resources/js/components/VueUpload.vue';

<VueUpload upload-url="/upload/media" :csrf-token="token" />
```

## Testing

To run the package tests specifically:

```bash
php artisan test packages/ashiful/upload
```
# laravel-upload
