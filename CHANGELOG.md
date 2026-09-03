# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.6.0] - 2026-09-03

### Added
- Wikidata data types can now be used to annotate a value (`ValueAnnotatingInterface`), so a statement can carry its own reconciled source, qualifier or certainty. The suggest widget, its language control and the URI panel all work inside the annotation sidebar exactly as they do in the item form.

### Fixed
- **1.5.0 could not be used on Omeka S 4.0 or 4.1.** It implements `ConversionTargetInterface`, which Omeka only added in 4.2.0, so opening the item form or a resource template raised a fatal error on earlier 4.x releases. The version requirement is now `^4.2.0`, which those installations correctly refuse rather than breaking. If you are on Omeka S 4.0 or 4.1, stay on 1.4.2 or upgrade Omeka.

## [1.5.0] - 2026-09-02

### Added
- Data type conversion (`ConversionTargetInterface`), so the Wikidata data types now appear as targets in batch edit's "Convert data type". A value converts when a Wikidata entity can be identified from its URI or from a URI typed into its text field, and the URI is normalised to its canonical form on the way; anything else is left untouched and reported as unconvertible. This is the only way to recover values damaged by the `getName()` defect below that have since been re-saved.
- A descriptive `User-Agent` on all requests to the Wikidata APIs. Wikimedia keys its rate limit on the user agent, and the Laminas default was throttled after a handful of lookups ([ISSUE#10](https://github.com/nishad/omeka-s-wikidata/issues/10)).
- An upgrade step that repairs values stored under the pre-1.5.0 capitalised data type names.

### Changed
- **Dropped support for Omeka S 3.x**; the module now requires Omeka S 4.0 or later. The resource form's language control is incompatible between the two, and supporting both is what left the language selector broken on every version. See the README for details.
- The value form now uses Omeka S 4's own `.language-wrapper` markup, so the language control, its datalist and its validation behave exactly as they do for every other data type ([ISSUE#11](https://github.com/nishad/omeka-s-wikidata/issues/11), [ISSUE#6](https://github.com/nishad/omeka-s-wikidata/issues/6)).
- Suggestion caching now stores JSON instead of serialized PHP, in a per-installation directory under Omeka's configured `temp_dir` created with mode 0700, and prunes entries it can no longer serve.
- The "Languages" data type now reconciles against Q34770 (a particular language) instead of Q315 (the human faculty of language), which returned unrelated entities and, for "french", nothing but "condom".
- All data types now mint the same canonical `http://www.wikidata.org/entity/...` URI; the reconciliation suggesters previously used `https://`, so one entity could be stored under two URIs.
- The suggest proxy answers a failed lookup with HTTP 502 and a JSON error instead of a 500, and the browser logs the reason.
- Shared suggester behaviour moved to `AbstractSuggester` and `AbstractReconciliation`.

### Fixed
- Data types reported their own name capitalised (`Wikidata:mediawiki:all`) while being registered in lowercase. Omeka stores the reported name in `value.type` and resolves it case-sensitively, so every saved value silently degraded to a plain `uri` or `literal`, the module's renderer never ran, and the suggest widget disappeared when the item was edited again.
- Language tags are normalised to the form the Wikidata APIs accept. Canonical BCP 47 tags such as `pt-BR`, `zh-Hant` and `en-GB` previously returned no suggestions at all; region variants Wikidata does not know now fall back to the primary subtag ([ISSUE#6](https://github.com/nishad/omeka-s-wikidata/issues/6)).
- `hydrate()` accepts `o:lang` as well as `@language`, so a language set through the API or an import is no longer discarded.
- Failed lookups are no longer cached. A single rate-limited or timed-out request used to be stored as an empty result and served for an hour ([ISSUE#10](https://github.com/nishad/omeka-s-wikidata/issues/10)).
- Network failures no longer surface as HTTP 500 from the suggest proxy ([ISSUE#10](https://github.com/nishad/omeka-s-wikidata/issues/10)).
- Missing keys in an API response no longer raise PHP warnings, which under `APPLICATION_ENV=development` were printed into the JSON body and broke the response outright ([ISSUE#10](https://github.com/nishad/omeka-s-wikidata/issues/10)).
- Cache keys no longer collide: the query, language and data type were concatenated without separators, so `("tokyo", "en")` and `("tokyoen", "")` shared an entry.
- Concurrent cache writes are locked, and an unreadable entry is refetched rather than answered with a 500.
- The module version is valid semver again. `1.5.0.alpha1` threw in Omeka's module-update check and stopped update notifications for every module after this one.
- The `wikidataall:` branch in the front-end script was unreachable and has been removed; tooltips are delegated once per page instead of once per value.
- The URI icon no longer duplicates the value's `aria-labelledby` id.

### Security
- Cached suggestions are decoded with `json_decode` instead of `unserialize`, which could instantiate arbitrary objects if the cache file was writable by another user on a shared host.
- The hover-preview URL is escaped before being placed in the attribute Tipped evaluates.

## [1.4.1] - 2024-12-20

### Fixed
- Made the module compatible with the latest version of Omeka S (4.1.1), resolving installation issues on updated systems ([ISSUE#7](https://github.com/nishad/omeka-s-wikidata/issues/7), [ISSUE#8](https://github.com/nishad/omeka-s-wikidata/issues/8)). (Thanks to [Juan Felipe Urueña Calderón](https://github.com/juanfuc))

## [1.4.0] - 2022-01-20

### Fixed
- Fixed issues while using with ValueSuggest ([ISSUE#3](https://github.com/nishad/omeka-s-wikidata/issues/3)). (Thanks to [Daniel-KM](https://github.com/Daniel-KM))
- copyedits to README.md. (Thanks to [Daniel Mietchen](https://github.com/Daniel-Mietchen))
- Added citation to the README and to the repository.

## [1.3.0] - 2021-03-06

### Added
- Support for Omeka-S v3

### Removed
- Support for Omeka-S v2 and v1

## [1.2.2] - 2021-03-06

### Fixed
- Minor bug for Wikidata MediaWiki API

## [1.2.1] - 2021-03-06

### Fixed
- Preview for Wikidata MediaWiki API
- Preview for Wikidata Openrefine reconciliation API - All
- Preview for Wikidata Openrefine reconciliation API - Properties

## [1.2.0] - 2020-10-08

### Fixed
- Typo in Wikidata URIs [PR-1](https://github.com/nishad/omeka-s-wikidata/pull/1)
- Typo in README

## [1.1.0] - 2020-09-18

### Added
- Changelog

### Changed
- Updated README with more instructions

### Fixed
- Wikimedia is changed to MediaWiki to support naming conventions on APIs

## [1.0.1] - 2020-09-03

### Added

- Initial public release from private development repository v1 branch
- Autosuggestions from Wikidata MediaWiki API
- Autosuggestions from Wikidata Openrefine reconciliation API

[Unreleased]: https://github.com/nishad/omeka-s-wikidata/compare/v1.6.0...HEAD
[1.6.0]: https://github.com/nishad/omeka-s-wikidata/compare/v1.5.0...v1.6.0
[1.5.0]: https://github.com/nishad/omeka-s-wikidata/compare/v1.4.2...v1.5.0
[1.4.2]: https://github.com/nishad/omeka-s-wikidata/compare/v1.4.1...v1.4.2
[1.4.1]: https://github.com/nishad/omeka-s-wikidata/compare/v1.4.0...v1.4.1
[1.4.0]: https://github.com/nishad/omeka-s-wikidata/compare/v1.3.0...v1.4.0
[1.3.0]: https://github.com/nishad/omeka-s-wikidata/compare/v1.2.2...v1.3.0
[1.2.2]: https://github.com/nishad/omeka-s-wikidata/compare/v1.2.1...v1.2.2
[1.2.1]: https://github.com/nishad/omeka-s-wikidata/compare/v1.2.0...v1.2.1
[1.2.0]: https://github.com/nishad/omeka-s-wikidata/compare/v1.1.0...v1.2.0
[1.1.0]: https://github.com/nishad/omeka-s-wikidata/compare/v1.0.1...v1.1.0
[1.0.1]: https://github.com/nishad/omeka-s-wikidata/releases/tag/v1.0.1
