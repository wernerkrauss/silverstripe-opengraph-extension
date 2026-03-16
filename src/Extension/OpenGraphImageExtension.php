<?php

namespace Netwerkstatt\OpenGraph\Extension;

use SilverStripe\Core\Extension;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Forms\FieldList;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * Class OpenGraphImageExtension
 * @package Netwerkstatt\OpenGraph\Extension
 *
 * @property Image|OpenGraphImageExtension $owner
 * @method Image OGImageCustom()
 */
class OpenGraphImageExtension extends Extension
{
    private static array $has_one = [
        'OGImageCustom' => Image::class,
    ];

    private static array $owns = [
        'OGImageCustom',
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldToTab(
            'Root.OpenGraph',
            UploadField::create('OGImageCustom', 'Spezifisches Open Graph Bild')
                ->setDescription('Empfohlene Größe: 1200 x 630 Pixel. Fokuspunkt setzen für optimalen Social-Media-Zuschnitt.')
        );
    }

    /**
     * Get the OG Image for this object.
     * Uses FocusFill if available, otherwise Fill.
     *
     * @return Image|null
     */
    public function getOGImage()
    {
        $image = null;

        // 1. Specific Page OG Image
        if ($this->getOwner()->OGImageCustomID) {
            $image = $this->getOwner()->OGImageCustom();
        }

        // 2. Fallback to SiteConfig Default
        if (!$image) {
            $config = SiteConfig::current_site_config();
            if ($config->hasMethod('DefaultOGImage') && $config->DefaultOGImage()->exists()) {
                $image = $config->DefaultOGImage();
            }
        }

        if ($image && $image->exists()) {
            $width = 1200;
            $height = 630;

            // Use FocusFill if Jonom/FocusPoint is installed and image has a focus point
            if ($image->hasMethod('FocusFill')) {
                $image = $image->FocusFill($width, $height);
            } else {
                $image = $image->Fill($width, $height);
            }

            /** @var AssetContainer $image */
            if ($image->hasMethod('Watermark')) {
                $image = $image->Watermark();
            }

            return $image;
        }

        return null;
    }
}
