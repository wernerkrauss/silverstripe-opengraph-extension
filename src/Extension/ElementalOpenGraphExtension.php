<?php

namespace Netwerkstatt\OpenGraph\Extension;

use SilverStripe\Core\Extension;
use SilverStripe\ORM\FieldType\DBHTMLText;

/**
 * Class ElementalOpenGraphExtension
 *
 * Add this extension to pages that use Silverstripe Elemental to provide optimised Open Graph descriptions.
 *
 * @package Netwerkstatt\OpenGraph\Extension
 */
class ElementalOpenGraphExtension extends Extension
{
    /**
     * @return string|null
     */
    public function getOGDescription(): ?string
    {
        // 1. Check for MetaDescription (original logic)
        $owner = $this->getOwner();
        if ($owner->MetaDescription) {
            return $owner->MetaDescription;
        }

        // 2. Elemental content
        if ($owner->hasMethod('getElementsForSearch')) {
            $content = $owner->getElementsForSearch();
            if ($content) {
                $dbField = DBHTMLText::create();
                $dbField->setValue($content);
                return $dbField->Summary(100);
            }
        }

        // 3. Fallback to default OpenGraphObjectExtension implementation if possible
        // Since we are an extension, we can't directly call parent::getOGDescription()
        // but the OpenGraphObjectExtension is likely also an extension on the same owner.
        // However, if we define getOGDescription here, it overrides the one in OpenGraphObjectExtension
        // because of how SilverStripe's extension system works (methods on the owner take precedence).
        
        return $owner->dbObject('Content')->Summary(100);
    }
}
