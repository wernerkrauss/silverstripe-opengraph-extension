# Silverstripe OpenGraph Extension

Extends [tractorcow/silverstripe-opengraph](https://github.com/tractorcow/silverstripe-opengraph) with advanced image handling, CMS previews, Elemental support, and Schema.org integration.

## Features

- **Advanced Image Handling**: Automatically resizes images to 1200x630px using `FocusFill()` (if `jonom/silverstripe-focuspoint` is installed) or `Fill()`.
- **Watermark Support**: Automatically applies watermarks if configured in `SiteConfig`.
- **CMS Preview**: Adds a real-time Social Media preview in the "OpenGraph" tab of the CMS, including warnings for missing content.
- **Elemental Support**: Automatically generates `og:description` from Elemental blocks if the main content field is empty.
- **Twitter Cards**: Automatically generates `twitter:card` (summary_large_image).
- **OG Dimensions**: Includes `og:image:width` and `og:image:height` for faster preview generation on first share.
- **Schema.org (JSON-LD)**: Provides a framework for JSON-LD, with optional `spatie/schema-org` support.

## Extensions

### OpenGraphImageExtension
Adds a `OGImageCustom` field to pages for specific Open Graph images. It also provides the `getOGImage()` hook for the builder and the CMS preview.
- **Target**: `Page`

### ElementalOpenGraphExtension
Provides an optimized `getOGDescription()` for pages using Silverstripe Elemental. It uses `getElementsForSearch()` to aggregate content from blocks.
- **Target**: Classes with `ElementalPageExtension`

### OpenGraphBuilderExtension
Extends the `OpenGraphBuilder` to include Twitter card types and image dimensions.
- **Target**: `TractorCow\OpenGraph\ObjectBuilders\OpenGraphBuilder`

### SchemaExtension
Injects JSON-LD into the page head.
- **Target**: `Page` and `ContentController`

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

### Elemental Integration

If you want to explicitly enable Elemental support for a specific page type:

```yaml
Netwerkstatt\Site\Page\BlockPage:
  extensions:
    - Netwerkstatt\OpenGraph\Extension\ElementalOpenGraphExtension
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
