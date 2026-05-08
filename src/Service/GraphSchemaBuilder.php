<?php

namespace Netwerkstatt\OpenGraph\Service;

use SilverStripe\CMS\Controllers\ContentController;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\SiteConfig\SiteConfig;
use Spatie\SchemaOrg\BaseType;
use ArgumentCountError;
use ReflectionMethod;

class GraphSchemaBuilder
{
    /**
     * @param SiteTree|ContentController $owner
     *
     * @return array<string, mixed>|null
     */
    public function build($owner): ?array
    {
        $page = $owner instanceof SiteTree ? $owner : $owner->data();
        if (!$page instanceof SiteTree) {
            return null;
        }

        $siteConfig = SiteConfig::current_site_config();
        if (!$siteConfig) {
            return null;
        }

        $contributors = [$siteConfig, $page];
        $this->extendSchemaGraphContributors($contributors, $owner, $page, $siteConfig);

        $items = [];
        $this->extendSchemaGraphItems($items, $owner, $page, $siteConfig);
        $contributors = array_merge($contributors, $items);

        $nodes = [];
        foreach ($contributors as $contributor) {
            foreach ($this->collectOwnerNodes($contributor, $page, $siteConfig, $owner) as $node) {
                $nodes[] = $node;
            }
        }

        $owner->extend('updateSchemaGraphNodes', $nodes, $page, $siteConfig);

        if (empty($nodes)) {
            return null;
        }

        return [
            '@context' => 'https://schema.org',
            '@graph' => array_values($nodes),
        ];
    }

    /**
     * @param object $provider
     * @param mixed ...$arguments
     *
     * @return array<int, array<string, mixed>>
     */
    private function collectOwnerNodes(object $provider, ...$arguments): array
    {
        if (!$this->supportsMethod($provider, 'provideSchemaGraphNodes')) {
            return [];
        }

        $nodes = $this->callWithCompatibleArguments($provider, 'provideSchemaGraphNodes', $arguments);
        if (!is_array($nodes)) {
            return [];
        }

        $result = [];
        foreach ($nodes as $node) {
            if ($node instanceof BaseType) {
                $result[] = $node->toArray();
            }
        }

        return $result;
    }

    /**
     * @param array<int, object> $contributors
     */
    private function extendSchemaGraphContributors(array &$contributors, object $owner, SiteTree $page, SiteConfig $siteConfig): void
    {
        if (method_exists($owner, 'extend')) {
            $owner->extend('updateSchemaGraphContributors', $contributors, $page, $siteConfig);
        }

        if ($owner !== $page && method_exists($page, 'extend')) {
            $page->extend('updateSchemaGraphContributors', $contributors, $page, $siteConfig);
        }

        if ($siteConfig !== $page && method_exists($siteConfig, 'extend')) {
            $siteConfig->extend('updateSchemaGraphContributors', $contributors, $page, $siteConfig);
        }

        $contributors = array_values(array_filter($contributors, is_object(...)));
    }

    /**
     * @param array<int, object> $items
     */
    private function extendSchemaGraphItems(array &$items, object $owner, SiteTree $page, SiteConfig $siteConfig): void
    {
        if (method_exists($owner, 'extend')) {
            $owner->extend('updateSchemaGraphItems', $items, $page, $siteConfig);
        }

        if ($owner !== $page && method_exists($page, 'extend')) {
            $page->extend('updateSchemaGraphItems', $items, $page, $siteConfig);
        }

        if ($siteConfig !== $page && method_exists($siteConfig, 'extend')) {
            $siteConfig->extend('updateSchemaGraphItems', $items, $page, $siteConfig);
        }

        $items = array_values(array_filter($items, is_object(...)));
    }

    /**
     * @param array<int, mixed> $arguments
     */
    private function callWithCompatibleArguments(object $provider, string $method, array $arguments)
    {
        if (!method_exists($provider, $method)) {
            return $this->callWithFallbackArgumentCount($provider, $method, $arguments);
        }

        $reflection = new ReflectionMethod($provider, $method);
        $parameters = $reflection->getParameters();
        $required = 0;
        $acceptsVariadic = false;

        foreach ($parameters as $parameter) {
            if ($parameter->isVariadic()) {
                $acceptsVariadic = true;
                break;
            }

            if (!$parameter->isOptional()) {
                $required++;
            }
        }

        if (count($arguments) < $required) {
            return null;
        }

        if ($acceptsVariadic) {
            return $provider->{$method}(...$arguments);
        }

        $maxArguments = count($parameters);
        return $provider->{$method}(...array_slice($arguments, 0, $maxArguments));
    }

    private function supportsMethod(object $provider, string $method): bool
    {
        if (method_exists($provider, $method)) {
            return true;
        }

        return method_exists($provider, 'hasMethod') && $provider->hasMethod($method);
    }

    /**
     * @param array<int, mixed> $arguments
     */
    private function callWithFallbackArgumentCount(object $provider, string $method, array $arguments)
    {
        for ($argumentCount = count($arguments); $argumentCount >= 0; $argumentCount--) {
            try {
                return $provider->{$method}(...array_slice($arguments, 0, $argumentCount));
            } catch (ArgumentCountError) {
                continue;
            }
        }

        return null;
    }
}
