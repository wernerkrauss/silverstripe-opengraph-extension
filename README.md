# Silverstripe OpenGraph Extension

Extends [tractorcow/silverstripe-opengraph](https://github.com/tractorcow/silverstripe-opengraph) with advanced image handling and Schema.org support.

## Features

- **Advanced Image Handling**: Automatically resizes images to 1200x630px.
- **FocusPoint Support**: Uses `FocusFill()` if `jonom/silverstripe-focuspoint` is installed.
- **Watermark Support**: Automatically applies watermarks if the image has a `Watermarked()` method.
- **Twitter Cards**: Automatically generates `twitter:card` (summary_large_image).
- **OG Dimensions**: Includes `og:image:width` and `og:image:height` for faster preview generation.
- **Schema.org (JSON-LD)**: Provides a generic framework for JSON-LD, with optional `spatie/schema-org` support.

## Installation

With composer:
```shell
composer require netwerkstatt/silverstripe-opengraph-extension
```

## Configuration

The module is pre-configured to apply to all `Page` objects. You can customise the image dimensions in YAML:

```yaml
Netwerkstatt\OpenGraph\Extension\OpenGraphImageExtension:
  og_image_width: 1200
  og_image_height: 630
```

## Schema.org Customization

You can extend the JSON-LD data in your `Page` class or via an extension:

```php
public function updateSchemaData(&$schema)
{
    // If spatie/schema-org is used, $schema is a Spatie object
    if ($schema instanceof \Spatie\SchemaOrg\WebPage) {
         $schema->author('My Name');
    }
}
```
