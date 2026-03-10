<?php

namespace Netwerkstatt\OpenGraph\Extension;

use SilverStripe\Core\Extension;
use SilverStripe\View\Requirements;
use Spatie\SchemaOrg\Schema;

class SchemaExtension extends Extension
{
    /**
     * Hook to include JSON-LD in the head of the page.
     */
    public function contentControllerInit()
    {
        $schema = $this->generateSchema();
        if ($schema) {
            $json = is_string($schema) ? $schema : json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            Requirements::insertHeadTags(
                sprintf('<script type="application/ld+json">%s</script>', $json),
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
        $object = $this->getOwner();

        if (class_exists(Schema::class)) {
            // Using Spatie\SchemaOrg
            $schema = Schema::webPage()
                ->name($object->Title)
                ->url($object->AbsoluteLink())
                ->description($object->MetaDescription ?: $object->dbObject('Content')->Summary());

            // Hook for specific modifications
            $object->extend('updateSchemaData', $schema);

            return $schema->toArray();
        }

        // Generic fallback if Spatie is not installed
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => $object->Title,
            'url' => $object->AbsoluteLink(),
            'description' => $object->MetaDescription ?: $object->dbObject('Content')->Summary(),
        ];

        // Hook for specific modifications
        $object->extend('updateSchemaData', $schema);

        return $schema;
    }
}
