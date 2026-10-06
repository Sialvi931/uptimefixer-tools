# UptimeFixer Tools

Custom WordPress plugin used on [UptimeFixer.com](https://uptimefixer.com/) to power the Website Image Extractor and the site's extended collection of online utilities.

The plugin is kept separate from the theme so tools can be maintained independently. Frontend assets are loaded only on the tool pages that need them whenever possible.

## Main areas

- Website utilities
- Developer tools
- Image tools
- PDF tools
- Text and data tools
- Marketing utilities
- Tool-specific SEO integration

## How it is organized

```text
assets/
inc/
uptimefixer-image-extractor.php
readme.txt
```

The plugin registers tool definitions through the theme's extension hooks and keeps rendering, lifecycle, SEO and remote-processing logic separated inside `inc/`.

## Performance approach

The suite uses a small shared controller and loads only the module needed by the current tool page.

Current module groups include:

- data
- media
- PDF
- marketing
- advanced

The Website Image Extractor also loads its own frontend assets only on its tool page.

## Requirements

- WordPress 5.8 or newer
- PHP 7.4 or newer
- UptimeFixer theme for the full integrated tool layout

## Installation

1. Download or clone the repository.
2. Place the plugin folder inside `wp-content/plugins/`.
3. Activate **UptimeFixer Overall Tools Suite** in WordPress.
4. Clear caches after replacing an existing version.
5. Test representative tools from each category.

## Current version

**2.5.0**

This release improves conditional module loading and deferred frontend assets without removing existing tool definitions or URLs.

See [CHANGELOG.md](CHANGELOG.md) for release notes.

## Live project

https://uptimefixer.com/

## Author

Azhar Mehmood  
DigiPlex Creations

## License

GPL-2.0-or-later. See `LICENSE`.
