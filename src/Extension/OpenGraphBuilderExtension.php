<?php

namespace Netwerkstatt\OpenGraph\Extension;

use Page;
use SilverStripe\Core\Extension;
use SilverStripe\ORM\DataObject;
use SilverStripe\SiteConfig\SiteConfig;
use TractorCow\OpenGraph\ObjectBuilders\OpenGraphBuilder;

/**
 * @property OpenGraphBuilder $owner
 */
class OpenGraphBuilderExtension extends Extension
{
    /**
     * @param array<string, string|array<string, string>> $tags
     * @param Page|DataObject $object
     */
    public function updateDefaultMetaTags(&$tags, $object)
    {
        /** @var OpenGraphBuilder $owner */
        $owner = $this->getOwner();

        // @phpstan-ignore parameterByRef.type
        $owner->AppendTag($tags, 'twitter:card', 'summary_large_image');

        // 2. Add Dimensions for the OG Image (important for first-time sharing)
        if ($object->hasMethod('getOGImage')) {
            $image = $object->getOGImage();
            $exists = is_string($image) ? !empty($image) : ($image && $image->exists());

            if ($exists) {
                // @phpstan-ignore parameterByRef.type
                $owner->AppendTag($tags, 'og:image:width', $object->config()->get('og_image_width'));
                // @phpstan-ignore parameterByRef.type
                $owner->AppendTag($tags, 'og:image:height', $object->config()->get('og_image_height'));
            }
        }
    }

    /**
     * Add Application meta tags (like twitter:site)
     *
     * @param array<string, string|array<string, string>> $tags
     * @param SiteConfig $siteConfig
     */
    public function updateApplicationMetaTags(&$tags, $siteConfig)
    {
        /** @var OpenGraphBuilder $owner */
        $owner = $this->getOwner();

        if ($siteConfig->TwitterHandle) {
            // @phpstan-ignore parameterByRef.type
            $owner->AppendTag($tags, 'twitter:site', '@' . ltrim((string)$siteConfig->TwitterHandle, '@'));
        }
    }
}
