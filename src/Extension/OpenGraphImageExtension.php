<?php

namespace Netwerkstatt\OpenGraph\Extension;

use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\LiteralField;
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
        $fields->addFieldToTab('Root.OpenGraph', HeaderField::create('PreviewHeader', 'Open Graph Preview', 2));

        $fields->addFieldToTab('Root.OpenGraph', $this->getOpenGraphPReview());
    }

    private function getOpenGraphPreview(): ?LiteralField
    {
        $owner = $this->getOwner();
        if (!$owner->hasMethod('getTagBuilder')) {
            return null;
        }

        $builder = $owner->getTagBuilder();
        $tags = '';
        $builder->BuildTags($tags, $owner, SiteConfig::current_site_config());

        // Extract relevant OG tags
        $title = $this->getMetaContent($tags, 'og:title');
        $description = $this->getMetaContent($tags, 'og:description');
        $image = $this->getMetaContent($tags, 'og:image');
        $siteName = $this->getMetaContent($tags, 'og:site_name');

        $previewHtml = sprintf(
            '<div class="og-preview-card" style="border: 1px solid #ddd; border-radius: 8px; overflow: hidden; max-width: 500px; font-family: sans-serif; margin-bottom: 20px;">
                %s
                <div style="padding: 12px; background: #f0f2f5;">
                    <div style="font-size: 12px; color: #65676b; text-transform: uppercase; margin-bottom: 4px;">%s</div>
                    <div style="font-weight: bold; font-size: 16px; color: #050505; margin-bottom: 4px;">%s</div>
                    <div style="font-size: 14px; color: #65676b; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">%s</div>
                </div>
            </div>',
            $image ? sprintf('<div style="aspect-ratio: 1200/630; overflow: hidden; background: #eee;"><img src="%s" style="width: 100%%; height: 100%%; object-fit: cover;" /></div>',
                $image) : '',
            htmlspecialchars($siteName ?: ''),
            htmlspecialchars($title ?: ''),
            htmlspecialchars($description ?: '')
        );

        return LiteralField::create('OGPreview', $previewHtml);

    }

    private function getMetaContent(string $tags, string $property): ?string
    {
        if (preg_match('/property="' . preg_quote($property, '/') . '"\s+content="([^"]+)"/', $tags, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Get the OG Image for this object.
     * Uses FocusFill if available, otherwise Fill.
     *
     * @return string|null
     */
    public function getOGImage(): ?string
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

        if (!$image || !$image->exists()) {
            return null;
        }

        $width = $this->getOwner()->config()->get('og_image_width');
        $height = $this->getOwner()->config()->get('og_image_height');

        // Use FocusFill if Jonom/FocusPoint is installed and image has a focus point
        $ogImage = $image->hasMethod('FocusFill')
            ? $image->FocusFill($width, $height)
            : $image->Fill($width, $height);

        if ($ogImage && $ogImage->hasMethod('Watermark')) {
            $config = SiteConfig::current_site_config();
            $logo = $config->hasMethod('OGWatermarkLogo') ? $config->OGWatermarkLogo() : null;
            if ($logo && $logo->exists()) {
                $ogImage = $ogImage->Watermark($logo);
            }
        }

        return $ogImage ? $ogImage->getAbsoluteURL() : null;
    }
}
