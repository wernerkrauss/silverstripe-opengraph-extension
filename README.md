# Silverstripe OpenGraph Extension

Extends [tractorcow/silverstripe-opengraph](https://github.com/tractorcow/silverstripe-opengraph) with advanced image handling, CMS previews, Elemental support, and Schema.org integration.

## Features

- **Advanced Image Handling**: Automatically resizes images to 1200x630px using `FocusFill()` (if `jonom/silverstripe-focuspoint` is installed) or `Fill()`.
- **Watermark Support**: Automatically applies watermarks if configured in `SiteConfig` (requires `netwerkstatt/silverstripe-image-toolkit`).
- **SiteConfig Integration**: Adds global fields for a default Open Graph image and an optional watermark logo.
- **CMS Preview**: Adds a real-time Social Media preview in the "OpenGraph" tab of the CMS, including warnings for missing content.
- **Elemental Support**: Automatically generates `og:description` from Elemental blocks if the main content field is empty.
- **Twitter Cards**: Automatically generates `twitter:card` (summary_large_image).
- **OG Dimensions**: Includes `og:image:width` and `og:image:height` for faster preview generation on first share.
- **Schema.org (JSON-LD)**: Provides a framework for JSON-LD, with optional `spatie/schema-org` support.

## Extensions

### OpenGraphImageExtension
Adds a `OGImageCustom` field to pages for specific Open Graph images. It also provides the `getOGImage()` hook for the builder and the CMS preview.
- **Target**: `Page`

### SiteConfigOpenGraphExtension
Adds global settings for a default Open Graph image and a watermark logo (requires `silverstripe-image-toolkit`).
- **Target**: `SilverStripe\SiteConfig\SiteConfig`

### ElementalOpenGraphExtension
Provides an optimized `getOGDescription()` for pages using Silverstripe Elemental. It uses `getElementsForSearch()` to aggregate content from blocks.
- **Target**: Classes with `ElementalPageExtension`

### OpenGraphBuilderExtension
Extends the `OpenGraphBuilder` to include Twitter card types and image dimensions.
- **Target**: `TractorCow\OpenGraph\ObjectBuilders\OpenGraphBuilder`

### SchemaExtension
Injects JSON-LD into the page head.
- **Target**: `Page` and `ContentController`

### SiteTreeSchemaGraphExtension
Provides a default `WebPage` node for all `SiteTree` records using `spatie/schema-org`.
- **Target**: `SilverStripe\CMS\Model\SiteTree`

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

The schema graph follows an extension-first provider/orchestrator pattern:

- The module orchestrates graph creation in `GraphSchemaBuilder`.
- Project code contributes nodes via dedicated extensions/providers.
- No schema-specific methods are required in project base classes like `Page` or `PageController`.

#### Contributor hooks

- `updateSchemaGraphContributors(array &$contributors, SiteTree $page, SiteConfig $siteConfig)`
- `updateSchemaGraphItems(array &$items, SiteTree $page, SiteConfig $siteConfig)`
- `updateSchemaGraphNodes(array &$nodes, SiteTree $page, SiteConfig $siteConfig)`

#### Safe project examples

Example 1: Add a project-specific provider (e.g. on `SiteConfig`):

```php
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Core\Extension;
use Spatie\SchemaOrg\BaseType;
use Spatie\SchemaOrg\Schema;

class SiteConfigSchemaExtension extends Extension
{
    /**
     * @return array<int, BaseType>
     */
    public function provideSchemaGraphNodes(SiteTree $page): array
    {
        $website = Schema::webSite()
            ->name($this->getOwner()->Title)
            ->url($page->getAbsoluteBaseURL());

        return [$website];
    }
}
```

Example 2: Collect block items via a dedicated page extension:

```php
use Netwerkstatt\Site\Page\BlockPage;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Core\Extension;

class BlockPageSchemaItemsExtension extends Extension
{
    /**
     * @param array<int, object> $items
     */
    public function updateSchemaGraphItems(array &$items, SiteTree $page): void
    {
        if (!$page instanceof BlockPage) {
            return;
        }

        foreach ($page->ElementalArea()->Elements() as $element) {
            $items[] = $element;
        }
    }
}
```

Example 3: Let each block/model provide its own node:

```php
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\SiteConfig\SiteConfig;
use Spatie\SchemaOrg\BaseType;
use Spatie\SchemaOrg\Schema;

class RoomType extends DataObject
{
    /**
     * @return array<int, BaseType>
     */
    public function provideSchemaGraphNodes(SiteTree $page, SiteConfig $siteConfig): array
    {
        $node = Schema::hotelRoom()->name($this->Title);
        return [$node];
    }
}
```
