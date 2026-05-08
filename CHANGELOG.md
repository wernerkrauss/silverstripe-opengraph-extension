# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/2.0.0.html).

## [0.3.0] - 2026-05-08

### Added
- `GraphSchemaBuilder` service for centralized JSON-LD/Schema.org generation.
- `SiteTreeSchemaGraphExtension` for enhanced Schema.org integration on pages.
- `SiteConfigOpenGraphExtension` fields for default OG image and watermark logo.
- Comprehensive documentation for Schema.org features in README.

### Changed
- Refactored `SchemaExtension` to use the new `GraphSchemaBuilder`.
- Improved overall code structure and maintainability.

## [0.2.0] - 2026-03-16

### Added
- Internationalization (i18n) support for German and English.
- `ElementalOpenGraphExtension` for optimized descriptions in Elemental-based pages.
- Advanced CMS preview for Open Graph tags.

### Fixed
- Improved Open Graph image generation and fallback logic.

## [0.1.0] - 2026-03-16

### Added
- Initial release.
- Basic Open Graph image support.
- BSD-3-Clause License.
