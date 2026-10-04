=== UptimeFixer Overall Tools Suite ===
Contributors: digiplexcreations
Tags: online tools, website tools, developer tools, image tools, pdf tools, calculators
Requires at least: 5.8
Requires PHP: 7.4
Stable tag: 2.5.0
License: GPLv2 or later

Upgrades the original UptimeFixer Website Image Extractor plugin into a professional 108-tool suite. Together with Uptime Fixer theme 4.0.0, the public registry contains 346 distinct working tools.

== What version 2.4 includes ==

* Keeps the original Website Image Extractor and its URL.
* Adds 107 distinct non-duplicate tools for websites, developers, images, subtitles, audio, video, PDFs, files, marketing and business calculations.
* Adds eight focused browser tools for JSON changes, OpenAPI, cURL, DNS zones, HTML accessibility markup, Unicode normalization, CSS layouts and HMAC signatures.
* Integrates through the Uptime Fixer theme's registered-tool and renderer hooks.
* Uses one polished, responsive UI system with mobile, tablet and dark-mode support.
* Processes local files and text in the visitor browser wherever practical.
* Uses protected, rate-limited WordPress requests only for public webpage checks.
* Does not depend on commercial SEO, traffic, backlink, authority or conversion data providers.
* Combines closely overlapping ideas into complete pages instead of publishing thin doorway pages.

== SEO and content quality ==

Every suite entry has a unique task title, summary, long description, call to action, limitation and privacy explanation. The Uptime Fixer theme uses these entries to provide title tags, meta descriptions, focus keyphrases, canonical URLs, Open Graph and Twitter metadata, breadcrumbs and visible guidance.

The plugin also adds factual WebApplication structured data without fabricated reviews or ratings. New tool posts receive useful editorial content. Versioned migrations update only unchanged plugin-managed copy; hand-written page content and existing Yoast fields are preserved.

Deleting or moving one of this plugin's tool pages to Trash disables its registry entry so it is not automatically recreated. Restoring the page re-enables it. Deactivating the plugin temporarily drafts its published tool pages so non-working interfaces do not remain indexable; reactivation restores the recorded status when the page was not changed manually.

Published plugin tools use normal WordPress indexing and sitemap visibility. A specific page can still be held back with the theme's Search visibility control; explicitly held-back pages remain noindex, are excluded from XML sitemaps and do not receive conventionally enqueued AdSense scripts.

== Security and privacy ==

* Public URL requests use nonces, per-IP rate limiting, TLS verification, redirect limits and bounded response sizes.
* Localhost, private/reserved IP space, credentialed URLs and unexpected ports are rejected.
* Public checks never accept server credentials or visitor-supplied request headers.
* Source viewers and extractors escape returned data before display.
* Browser tools do not intentionally upload local files to WordPress.
* File and media tools show practical codec, memory, format and verification limitations.
* Third-party images and public assets remain subject to their owners' copyright and terms.

== Installation / update ==

1. Install and activate Uptime Fixer theme v4.0.0.
2. In Plugins > Add New > Upload Plugin, upload the version 2.4.0 ZIP.
3. WordPress should recognize the same plugin folder and replace the previous Website Image Extractor version.
4. Activate the plugin and open WordPress Admin once. Missing suite pages are created automatically.
5. Go to Settings > Permalinks and click Save Changes once.
6. Clear WordPress, LiteSpeed, hosting and CDN caches.
7. Confirm the directory reports 346 tools, then test the original image extractor and several tools from every category.
8. Clear WordPress, LiteSpeed, hosting and CDN caches, then confirm published tool pages return index,follow with self-referencing canonicals and appear in the XML sitemap. Use Draft quality only for a specific page that needs correction.

== Important limitations ==

No tool claims to provide live backlinks, Domain Authority, competitor rankings, keyword volume or competitor traffic without a licensed data provider. Public webpage tools inspect only observable public responses. JavaScript-rendered content, login walls, CAPTCHAs and anti-bot systems are not bypassed.

PDF and complex media jobs can depend on browser memory, codec support and pinned browser libraries loaded from jsDelivr. Keep originals and verify every downloaded result.

Advertising approval and search rankings are decisions made by third parties. This plugin improves functionality, clarity and technical presentation but cannot guarantee approval or rankings.

== Changelog ==

= 2.5.0 =
* Preserves every existing plugin tool and URL; no tool definitions or processing engines are removed.
* Loads only the JavaScript module required by the current suite tool instead of every suite module on every tool page.
* Defers suite JavaScript where supported so non-critical code does not block initial rendering.
* Keeps the original Website Image Extractor and its script, with deferred loading for a lighter initial render.

= 2.4.0 =
* Preserves all 108 plugin tool slugs, interfaces and existing URLs.
* Removes the duplicate tool-name heading and repetitive three-card boilerplate from the shared renderer so the theme can show richer operation-aware guidance once.
* Adds privacy-safe GTM dataLayer events for tool starts, successful results, errors, copies and downloads; input values, URLs and filenames are never included.
* Defers suite scripts through Uptime Fixer theme 4.0.0 and refreshes only extension-managed SEO defaults.

= 2.3.1 =
* Restored normal indexing and XML sitemap inclusion for published registered tools while preserving the explicit Draft quality hold-back control.
* Kept the extension's robots, sitemap and AdSense safeguards aligned with Uptime Fixer theme 3.9.1.
* Bumped the SEO sync version so existing tool pages refresh non-manual Yoast defaults without overwriting hand-written fields.

= 2.3.0 =
* Preserved existing tool titles, excerpts, content, slugs, publication status and manual SEO fields during activation and versioned migrations.
* Restored recorded tool-page publication statuses after plugin reactivation without overriding pages changed while the plugin was inactive.
* Added automatic nonce refresh and one safe retry for public webpage tools on cached pages, plus bounded request and dependency-load timeouts.
* Reused one race-safe loader for PDF, ZIP and data-processing browser libraries so concurrent tools cannot remain stuck on duplicate scripts.
* Avoided duplicate WebApplication schema when Uptime Fixer theme 3.9.0 or Yoast-aware theme schema is already active.
* Kept all 108 plugin slugs and the complete 346-tool combined registry unchanged.

= 2.2.0 =
* Added eight unique, browser-processed tools: JSON Diff & JSON Patch Generator, OpenAPI & Swagger JSON Validator, HTTP Request & cURL Builder, DNS Zone File Validator, HTML Accessibility Markup Checker, Unicode Normalization Converter, CSS Grid & Flexbox Builder and HMAC Generator & Verifier.
* Kept every existing plugin tool and URL while increasing the extension registry from 100 to 108 tools.
* Certified zero duplicate slugs against the 238-tool Uptime Fixer theme 3.8.2 registry for 346 combined tools.
* Kept the new pages noindex, outside XML sitemaps and without theme-enqueued AdSense until individually reviewed.
* Added input validation, explicit limitations, local privacy processing and exportable results for every new workflow.

= 2.1.0 =
* Certified the combined registry at 338 unique tools with Uptime Fixer theme 3.8.2.
* Added extension-aware noindex, XML sitemap and theme-enqueued AdSense safeguards until each plugin tool is individually reviewed.
* Added tool-specific editorial context through the theme's new extension hook.
* Replaced repetitive managed page copy with purpose, input, verification, limitation and privacy sections without overwriting edited pages.
* Updated the image-preview CSP integration to use the theme's filterable policy, with a legacy fallback.
* Preserved all 100 renderers, browser handlers, protected remote checks and original URLs.

= 2.0.1 =
* Removed visitor-facing technical service terminology and promotional badges from tool pages and directory cards.
* Reworded visible privacy guidance for a cleaner production website experience.

= 2.0.0 =
* Upgraded the same plugin identity into UptimeFixer Overall Tools Suite.
* Kept the original Website Image Extractor working and unchanged in purpose.
* Added 99 non-duplicate working tool interfaces.
* Added browser processing engines, protected public-page analyzers and export controls.
* Added unique registry content, editorial guidance and WebApplication schema.
* Added trash-aware disabling so removed tool pages are not forced back.
* Added responsive suite UI and dark-mode styling.

= 1.0.0 =
* Initial standalone Website Image Extractor release.
