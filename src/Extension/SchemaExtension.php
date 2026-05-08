<?php

namespace Netwerkstatt\OpenGraph\Extension;

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Core\Extension;
use SilverStripe\View\Requirements;
use Netwerkstatt\OpenGraph\Service\GraphSchemaBuilder;
use Spatie\SchemaOrg\Schema;
use TractorCow\OpenGraph\Extensions\OpenGraphObjectExtension;

class SchemaExtension extends Extension
{
    /**
     * Hook to include JSON-LD in the head of the page.
     */
    public function contentControllerInit()
    {
        $schema = $this->generateSchema();
        if ($schema) {
            Requirements::insertHeadTags(
                sprintf(
                    '<script type="application/ld+json">%s</script>',
                    json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
                ),
                'schema-json-ld'
            );
        }
    }

    /**
     * Generates the schema data.
     * Uses Spatie\SchemaOrg if available.
     *
     * @return array|string|null
     */
    public function generateSchema()
    {
        /** @var SiteTree $owner */
        $owner = $this->getOwner();

        if (class_exists(Schema::class)) {
            return new GraphSchemaBuilder()->build($owner);
        }

        // Generic fallback if Spatie is not installed
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => $owner->Title,
            'url' => $owner->AbsoluteLink(),
            'description' => $this->getDescription(),
        ];

        // Hook for specific modifications
        $owner->extend('updateSchemaData', $schema);

        return $schema;
    }

    /**
     * @return string
     */
    private function getDescription()
    {
        /** @var SiteTree $owner */

        $owner = $this->getOwner();
        if ($owner->hasExtension(OpenGraphObjectExtension::class)) {
            return $owner->getOGDescription();
        }
        return $owner->MetaDescription ?: $owner->dbObject('Content')->Summary();
    }
}
