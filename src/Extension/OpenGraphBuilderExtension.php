<?php

namespace Netwerkstatt\OpenGraph\Extension;

use Page;
use SilverStripe\ORM\DataObject;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\Core\Extension;

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
            if ($image) {
                // @phpstan-ignore parameterByRef.type
                $owner->AppendTag($tags, 'og:image:width', '1200');
                // @phpstan-ignore parameterByRef.type
                $owner->AppendTag($tags, 'og:image:height', '630');
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
            $owner->AppendTag($tags, 'twitter:site', '@' . ltrim((string) $siteConfig->TwitterHandle, '@'));
        }
    }
}
