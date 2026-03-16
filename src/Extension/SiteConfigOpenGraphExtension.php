<?php

namespace Netwerkstatt\OpenGraph\Extension;

use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\Assets\Image;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\AssetAdmin\Forms\UploadField;

/**
 * Class \Netwerkstatt\OpenGraph\Extension\SiteConfigOpenGraphExtension
 *
 * @property SiteConfig|SiteConfigOpenGraphExtension $owner
 * @property int $DefaultOGImageID
 * @property int $OGWatermarkLogoID
 * @method Image DefaultOGImage()
 * @method Image OGWatermarkLogo()
 */
class SiteConfigOpenGraphExtension extends Extension
{
    private static array $has_one = [
        'DefaultOGImage' => Image::class,
        'OGWatermarkLogo' => Image::class,
    ];

    private static array $owns = [
        'DefaultOGImage',
        'OGWatermarkLogo',
    ];

    public function updateCMSFields(FieldList $fields): void
    {
        $fields->addFieldsToTab(
            'Root.OpenGraph',
            [
                UploadField::create('DefaultOGImage', _t(self::class . '.DefaultOGImage', 'Default Open Graph Image'))
                    ->setDescription(_t(self::class . '.DefaultOGImageDescription', 'Used when no specific image is set on a page. Recommended size: 1200 x 630 pixels.')),
                UploadField::create('OGWatermarkLogo', _t(self::class . '.OGWatermarkLogo', 'Open Graph Watermark (PNG)'))
                    ->setDescription(_t(self::class . '.OGWatermarkLogoDescription', 'Will be overlaid on the Open Graph image. Please use a transparent PNG file.')),
            ]
        );
    }
}
