<?php

namespace Netwerkstatt\OpenGraph\Extension;

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Director;
use SilverStripe\Core\Extension;
use Spatie\SchemaOrg\BaseType;
use Spatie\SchemaOrg\Schema;

/**
 * @property SiteTree|SiteTreeSchemaGraphExtension $owner
 */
class SiteTreeSchemaGraphExtension extends Extension
{
    /**
     * @return array<int, BaseType>
     */
    public function provideSchemaGraphNodes(): array
    {
        $owner = $this->getOwner();
        $baseUrl = rtrim(Director::absoluteBaseURL(), '/');

        $webPage = Schema::webPage()
            ->setProperty('@id', sprintf('%s#webpage', $owner->AbsoluteLink()))
            ->url($owner->AbsoluteLink())
            ->name($owner->Title)
            ->description($owner->MetaDescription ?: $owner->dbObject('Content')->Summary())
            ->isPartOf(Schema::webSite()->setProperty('@id', sprintf('%s#website', $baseUrl)))
            ->about(Schema::lodgingBusiness()->setProperty('@id', sprintf('%s#business', $baseUrl)));

        return [$webPage];
    }
}
