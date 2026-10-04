<?php
/**
 * Tool registry for UptimeFixer Overall Tools Suite.
 *
 * Every entry is a distinct task that is not already supplied by the bundled
 * Uptime Fixer theme. Closely overlapping ideas are intentionally combined
 * into one complete page instead of creating several thin URLs.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/** @return array<string,array<string,mixed>> */
function ufxots_tool_configs() {
    static $configs = null;
    if ( null !== $configs ) return $configs;

    $rows = array(
        /* Public website research and implementation. */
        array( 'webpage-article-extractor', 'Webpage Text & Article Extractor', 'Extract the title, article text, headings and reading statistics from a public webpage.', 'website-tools', 'word', 'blue', 'Extract Article', 'remote-url', 'Review the cleaned text against the source because navigation or dynamic content can affect extraction.' ),
        array( 'website-font-finder', 'Website Font Finder', 'Find declared font families, web-font files and @font-face rules used by a public webpage.', 'website-tools', 'word', 'purple', 'Find Fonts', 'remote-url', 'External stylesheets are sampled safely; fonts added only after JavaScript runs may not appear.' ),
        array( 'website-asset-extractor', 'Website Asset Extractor', 'List public stylesheets, scripts, images, media, fonts and document assets referenced by a webpage.', 'website-tools', 'download', 'green', 'Extract Assets', 'remote-url', 'The result is an inventory of public references, not permission to copy third-party files.' ),
        array( 'website-color-palette-extractor', 'Website Color Palette Extractor', 'Extract commonly declared HEX, RGB and HSL colours from webpage HTML and sampled CSS.', 'website-tools', 'image', 'pink', 'Extract Colours', 'remote-url', 'Rendered gradients, images and JavaScript-generated styles can contain colours not present in source code.' ),
        array( 'csp-header-builder', 'CSP Header Builder & Validator', 'Build and inspect a Content-Security-Policy header with common directives and safe defaults.', 'developer-tools', 'shield', 'purple', 'Build CSP', 'builder', 'Test the policy in report-only mode before enforcing it on a production website.' ),
        array( 'htaccess-nginx-redirect-generator', '.htaccess & Nginx Redirect Generator', 'Convert old-to-new URL mappings into Apache .htaccess or Nginx redirect rules.', 'developer-tools', 'redirect', 'orange', 'Generate Rules', 'text', 'Review loops, regex characters and server context before deploying generated redirect rules.' ),
        array( 'responsive-srcset-generator', 'Responsive Image srcset Generator', 'Create responsive srcset, sizes and picture markup from image filenames and width variants.', 'developer-tools', 'image', 'blue', 'Generate Markup', 'builder', 'Confirm every generated image URL exists and matches the declared intrinsic width.' ),
        array( 'sri-hash-generator', 'SRI Hash Generator', 'Generate SHA-256, SHA-384 or SHA-512 Subresource Integrity values for a local file or public asset.', 'developer-tools', 'shield', 'green', 'Generate SRI', 'file-or-url', 'Regenerate the integrity value whenever the protected resource bytes change.' ),
        array( 'security-txt-generator-checker', 'Security.txt Generator & Checker', 'Create a standards-oriented security.txt draft or inspect the file published by a website.', 'website-tools', 'shield', 'green', 'Build or Check', 'builder', 'Use a real monitored contact and a future expiry date before publishing the file.' ),
        array( 'permissions-policy-generator', 'Permissions-Policy Header Generator', 'Build a Permissions-Policy header for browser capabilities such as camera, microphone and geolocation.', 'developer-tools', 'shield', 'blue', 'Generate Header', 'builder', 'Browser support differs by feature, so test the result in the browsers your visitors use.' ),
        array( 'referrer-policy-generator-checker', 'Referrer-Policy Generator & Checker', 'Compare referrer policy choices, generate a header and inspect a live page declaration.', 'website-tools', 'shield', 'purple', 'Generate or Check', 'builder', 'Choose the least permissive policy that still supports required analytics and integrations.' ),
        array( 'pwa-manifest-generator-validator', 'PWA Manifest Generator & Validator', 'Create and validate a web app manifest with names, icons, display mode, colours and start URL.', 'developer-tools', 'code', 'green', 'Build Manifest', 'builder', 'A valid manifest alone does not make a site installable; HTTPS and a suitable service worker may also be required.' ),
        array( 'sitemap-comparison-tool', 'XML Sitemap Comparison Tool', 'Compare two public XML sitemaps to find added, removed and unchanged URL entries.', 'website-tools', 'code', 'purple', 'Compare Sitemaps', 'remote-two', 'Large sitemap indexes are bounded per run; confirm important migration results in Search Console.' ),
        array( 'website-migration-url-validator', 'Website Migration URL Validator', 'Check old and new URL pairs for status, redirect destination and migration mismatches.', 'website-tools', 'check', 'green', 'Validate Migration', 'text', 'Test representative templates as well as the submitted sample before launching a migration.' ),
        array( 'http-header-comparison', 'HTTP Header Comparison Tool', 'Compare response status and important HTTP headers returned by two public URLs.', 'website-tools', 'code', 'blue', 'Compare Headers', 'remote-two', 'Headers can vary by cache, location and user agent, so treat the output as a point-in-time comparison.' ),
        array( 'html-table-data-extractor', 'HTML Table to CSV & JSON Extractor', 'Extract accessible table rows from a public webpage or pasted HTML and export CSV or JSON.', 'website-tools', 'grid', 'orange', 'Extract Tables', 'builder', 'Visually merged cells and JavaScript-only tables may require manual cleanup after extraction.' ),
        array( 'rss-feed-finder-validator', 'RSS & Atom Feed Finder', 'Discover linked RSS or Atom feeds on a public website and validate their basic XML structure.', 'website-tools', 'search', 'orange', 'Find Feeds', 'remote-url', 'Feed discovery checks public source and common locations; private or script-generated feeds may be missed.' ),
        array( 'website-contact-social-extractor', 'Website Contact & Social Link Extractor', 'Find public email, telephone and supported social-profile links declared on a webpage.', 'website-tools', 'link', 'blue', 'Extract Contacts', 'remote-url', 'Results come only from the supplied public page and should be used responsibly.' ),
        array( 'webpage-content-comparison', 'Webpage Content Comparison Tool', 'Compare readable text from two public webpages and highlight additions, removals and similarity.', 'website-tools', 'search', 'purple', 'Compare Pages', 'remote-two', 'Dynamic widgets, personalisation and timestamps can create differences unrelated to editorial content.' ),
        array( 'tracking-parameter-remover', 'URL Tracking Parameter Remover', 'Remove common advertising and analytics parameters while preserving required destination parameters.', 'marketing-tools', 'link', 'green', 'Clean URL', 'text', 'Review the cleaned destination because some websites use query parameters for essential page state.' ),
        array( 'website-charset-language-checker', 'Website Charset & Language Checker', 'Inspect a page language declaration, character encoding and content-language response header.', 'website-tools', 'globe', 'blue', 'Check Language', 'remote-url', 'The declared language and encoding should be checked against the text visitors actually receive.' ),
        array( 'public-source-code-viewer', 'Public Webpage Source Viewer', 'Fetch and inspect bounded public HTML source with line numbers and a downloadable text copy.', 'developer-tools', 'code', 'purple', 'View Source', 'remote-url', 'The server response may differ from the DOM produced after client-side JavaScript executes.' ),
        array( 'url-structure-analyzer', 'URL Structure Analyzer', 'Break a URL into origin, path, query, fragment and SEO-relevant structural observations.', 'website-tools', 'link', 'orange', 'Analyze URL', 'text', 'Structural checks do not predict rankings; use them to catch avoidable consistency problems.' ),

        /* Data, text and developer utilities. */
        array( 'json-yaml-converter', 'JSON ↔ YAML Converter', 'Convert structured data between JSON and a practical safe YAML subset in the browser.', 'developer-tools', 'code', 'blue', 'Convert Data', 'builder', 'Advanced YAML tags, anchors and executable types are deliberately not evaluated.' ),
        array( 'json-xml-converter', 'JSON ↔ XML Converter', 'Convert JSON objects and arrays to XML or turn ordinary XML elements into JSON.', 'developer-tools', 'code', 'purple', 'Convert Data', 'builder', 'Attributes and mixed-content XML can require manual review after conversion.' ),
        array( 'jsonl-json-converter', 'JSONL ↔ JSON Converter', 'Convert newline-delimited JSON records to a JSON array and back with row-level validation.', 'developer-tools', 'code', 'green', 'Convert Records', 'builder', 'Each JSONL line must be a complete valid JSON value.' ),
        array( 'markdown-html-converter', 'Markdown ↔ HTML Converter', 'Convert common Markdown formatting to sanitized HTML or readable HTML back to Markdown.', 'developer-tools', 'code', 'pink', 'Convert Markup', 'builder', 'Complex embedded HTML and platform-specific Markdown extensions may need manual adjustment.' ),
        array( 'html-entity-encoder-decoder', 'HTML Entity Encoder & Decoder', 'Encode reserved HTML characters or decode named and numeric HTML entities.', 'developer-tools', 'code', 'orange', 'Encode or Decode', 'builder', 'Encoding text does not make untrusted HTML safe to execute.' ),
        array( 'unicode-character-inspector', 'Unicode Character Inspector', 'Inspect characters, code points, UTF-16 units and escaped representations in supplied text.', 'text-tools', 'search', 'blue', 'Inspect Characters', 'text', 'Combined emoji and accented characters can contain several Unicode code points.' ),
        array( 'invisible-character-detector', 'Invisible Character Detector', 'Reveal zero-width, direction, non-breaking and control characters hidden in text.', 'text-tools', 'search', 'red', 'Detect Characters', 'text', 'Remove characters only after checking whether they are meaningful for the language or layout.' ),
        array( 'url-parser-query-builder', 'URL Parser & Query Builder', 'Parse URL components, edit query parameters and rebuild an encoded URL.', 'developer-tools', 'link', 'green', 'Parse URL', 'builder', 'Parameter order and repeated keys can matter to some applications.' ),
        array( 'user-agent-parser', 'User-Agent Parser', 'Identify common browser, rendering engine, operating system and device signals in a user-agent string.', 'developer-tools', 'search', 'purple', 'Parse User Agent', 'text', 'User-agent strings can be reduced or spoofed and should not be treated as verified identity.' ),
        array( 'css-clamp-generator', 'CSS clamp() Fluid Type Generator', 'Generate responsive CSS clamp values from minimum, maximum and viewport measurements.', 'developer-tools', 'code', 'blue', 'Generate clamp()', 'builder', 'Preview the result at intermediate viewport sizes before using it across a design system.' ),
        array( 'css-gradient-generator', 'CSS Gradient Generator', 'Build linear or radial CSS gradients with adjustable angle, colours and stop positions.', 'developer-tools', 'image', 'purple', 'Generate Gradient', 'builder', 'Check contrast when text or controls appear over the generated gradient.' ),
        array( 'css-box-shadow-generator', 'CSS Box Shadow Generator', 'Create and preview CSS box-shadow values including inset, blur, spread and colour.', 'developer-tools', 'image', 'orange', 'Generate Shadow', 'builder', 'Heavy shadows can reduce clarity and rendering performance when repeated many times.' ),
        array( 'color-format-converter', 'HEX, RGB & HSL Color Converter', 'Convert colours between HEX, RGB and HSL while previewing the normalized result.', 'developer-tools', 'image', 'pink', 'Convert Colour', 'builder', 'Display calibration and colour profiles can change perceived appearance.' ),
        array( 'number-base-converter', 'Binary, Decimal & Hex Converter', 'Convert integers between binary, octal, decimal and hexadecimal representations.', 'developer-tools', 'calculator', 'green', 'Convert Number', 'builder', 'Very large values are handled as integers and may be limited by browser memory.' ),
        array( 'csv-cleaner-validator', 'CSV Cleaner & Validator', 'Detect delimiters, normalize row widths, identify malformed rows and export cleaned CSV.', 'developer-tools', 'grid', 'blue', 'Clean CSV', 'text', 'Review quoted fields and leading zeros before replacing source data.' ),
        array( 'csv-splitter-merger', 'CSV Splitter & Merger', 'Split a CSV by row count or merge compatible CSV files with one shared header.', 'developer-tools', 'grid', 'purple', 'Process CSV', 'files', 'Merged files should use the same delimiter, column order and header names.' ),
        array( 'line-ending-converter', 'Line Ending Converter', 'Convert text files between LF, CRLF and CR line endings and download a clean copy.', 'developer-tools', 'word', 'green', 'Convert Endings', 'file', 'Choose the convention required by the destination operating system or repository.' ),
        array( 'text-encoding-converter', 'Text Encoding Converter', 'Read UTF-8, UTF-16 or Latin-1 text and export it as UTF-8 or UTF-16.', 'developer-tools', 'word', 'orange', 'Convert Encoding', 'file', 'Encoding detection is heuristic when a file has no byte-order mark.' ),
        array( 'aes-text-encryption', 'AES Text Encryption & Decryption', 'Encrypt or decrypt text locally with AES-GCM and a password-derived key.', 'developer-tools', 'shield', 'green', 'Encrypt or Decrypt', 'builder', 'Keep the password and exported encrypted package separately; lost passwords cannot be recovered.' ),
        array( 'file-checksum-verifier', 'File Checksum Verifier', 'Calculate a local file checksum and compare it with an expected SHA-256, SHA-384 or SHA-512 value.', 'developer-tools', 'shield', 'blue', 'Verify File', 'file', 'Obtain the expected checksum from a trusted channel before relying on a match.' ),
        array( 'code-secret-scanner', 'Code Secret Scanner', 'Scan pasted code for patterns resembling exposed API keys, tokens, private keys and credentials.', 'developer-tools', 'shield', 'red', 'Scan Code', 'text', 'Pattern matching can miss secrets or flag examples; rotate any credential that was genuinely exposed.' ),
        array( 'mime-file-type-inspector', 'MIME & File Type Inspector', 'Inspect local file signature bytes, browser MIME type, extension, size and modification date.', 'developer-tools', 'search', 'purple', 'Inspect File', 'file', 'A detected type does not prove a file is safe to open or execute.' ),
        array( 'ssh-key-fingerprint-viewer', 'SSH Public Key Fingerprint Viewer', 'Calculate SHA-256 and legacy MD5 fingerprints from an OpenSSH public key.', 'developer-tools', 'shield', 'orange', 'View Fingerprint', 'text', 'Compare fingerprints over a trusted channel before accepting a host or user key.' ),
        array( 'json-schema-validator', 'JSON Schema Validator', 'Validate JSON against common schema rules including types, properties, required fields, arrays and patterns.', 'developer-tools', 'check', 'green', 'Validate JSON', 'two-text', 'The browser validator covers common draft keywords and reports unsupported advanced keywords.' ),
        array( 'html-table-generator', 'HTML Table Generator', 'Turn CSV-style data into accessible responsive HTML table markup with optional headers and caption.', 'developer-tools', 'grid', 'blue', 'Generate Table', 'builder', 'Check heading scope, caption meaning and mobile overflow in the destination page.' ),

        /* Image production helpers not already in the theme. */
        array( 'image-sprite-sheet-generator', 'Image Sprite Sheet Generator', 'Arrange multiple local images into a downloadable sprite sheet with CSS position data.', 'image-tools', 'grid', 'purple', 'Create Sprite', 'files', 'Mixed image sizes are placed into equal cells; inspect padding and scaling before use.' ),
        array( 'blurhash-placeholder-generator', 'BlurHash Placeholder Generator', 'Create a compact BlurHash string and preview placeholder from a local image.', 'image-tools', 'image', 'blue', 'Generate BlurHash', 'file', 'BlurHash is a visual placeholder and must not replace meaningful image alternative text.' ),
        array( 'image-comparison-slider-maker', 'Image Comparison Slider Maker', 'Preview two local images in an adjustable before-and-after comparison layout.', 'image-tools', 'image', 'green', 'Compare Images', 'files', 'Use images with the same dimensions and framing for an honest comparison.' ),
        array( 'image-grid-splitter', 'Image Grid Splitter', 'Split one image into equal rows and columns and download the resulting tiles.', 'image-tools', 'grid', 'orange', 'Split Image', 'builder', 'Image dimensions that are not evenly divisible can produce slightly different edge tiles.' ),
        array( 'image-tiles-joiner', 'Image Tiles Joiner', 'Join multiple local image tiles into a configurable row-and-column canvas.', 'image-tools', 'grid', 'pink', 'Join Tiles', 'builder', 'Select tiles in reading order and confirm the grid has enough cells.' ),
        array( 'dpi-ppi-calculator', 'DPI & PPI Calculator', 'Calculate print size, pixel density or required pixels from dimensions and viewing assumptions.', 'calculators', 'calculator', 'blue', 'Calculate Density', 'builder', 'Printers and screens can scale output, so verify final production requirements separately.' ),
        array( 'image-aspect-ratio-calculator', 'Image Aspect Ratio Calculator', 'Simplify an aspect ratio and calculate matching width or height without stretching.', 'calculators', 'resize', 'green', 'Calculate Ratio', 'builder', 'Cropping changes composition even when the output uses the correct aspect ratio.' ),
        array( 'image-contact-sheet-generator', 'Image Contact Sheet Generator', 'Create a labelled contact sheet from multiple local images with adjustable columns.', 'image-tools', 'grid', 'purple', 'Create Sheet', 'files', 'Large batches are resized for the sheet; keep originals for full-resolution inspection.' ),
        array( 'social-image-safe-zone-preview', 'Social Image Safe-Zone Preview', 'Preview an image inside common social post, story, cover and thumbnail safe zones.', 'image-tools', 'image', 'pink', 'Preview Safe Zone', 'file', 'Platform crops can change over time and vary by device; keep important text away from edges.' ),
        array( 'bulk-image-filename-renamer', 'Bulk Image Filename Renamer', 'Create SEO-friendly sequential filenames and downloadable renamed copies for local images.', 'image-tools', 'word', 'orange', 'Rename Images', 'files', 'Filename changes do not update references already stored in a website or document.' ),

        /* Video, audio and subtitle utilities. */
        array( 'video-thumbnail-extractor', 'Video Thumbnail Extractor', 'Capture a selected frame from a local video and download it as a PNG or JPEG thumbnail.', 'video-tools', 'image', 'purple', 'Extract Thumbnail', 'file', 'Seek accuracy depends on browser codec support and video keyframes.' ),
        array( 'srt-vtt-subtitle-converter', 'SRT ↔ VTT Subtitle Converter', 'Convert subtitle files between SRT and WebVTT while normalizing timestamps and cue headers.', 'video-tools', 'word', 'blue', 'Convert Subtitles', 'file', 'Review styling, speaker labels and cue timing in the target player.' ),
        array( 'subtitle-cleaner-validator', 'Subtitle Cleaner & Validator', 'Validate SRT or WebVTT cues, normalize spacing and report ordering or timestamp errors.', 'video-tools', 'check', 'green', 'Clean Subtitles', 'file', 'Automated cleanup does not verify spoken-word accuracy or reading speed.' ),
        array( 'subtitle-line-merger-splitter', 'Subtitle Line Merger & Splitter', 'Merge short subtitle lines or wrap long cues to a selected character limit.', 'video-tools', 'word', 'orange', 'Format Subtitles', 'file', 'Watch the result to confirm line breaks match speech and do not obscure the picture.' ),
        array( 'audio-waveform-image-generator', 'Audio Waveform Image Generator', 'Render a waveform from a local audio file and download it as a transparent or solid image.', 'audio-tools', 'pulse', 'purple', 'Create Waveform', 'file', 'The waveform represents amplitude and does not measure perceived loudness.' ),
        array( 'audio-id3-metadata-editor', 'MP3 ID3 Metadata Editor', 'Review and write basic title, artist, album, year and genre ID3v2 tags in an MP3 copy.', 'audio-tools', 'word', 'blue', 'Edit Metadata', 'file', 'Keep the original MP3 because unusual tag layouts or embedded artwork may be replaced.' ),
        array( 'video-metadata-viewer', 'Video Metadata Viewer', 'Inspect local video duration, dimensions, aspect ratio, browser codec type and file details.', 'video-tools', 'search', 'green', 'Inspect Video', 'file', 'Browser metadata does not include every container track or professional codec field.' ),
        array( 'video-frame-contact-sheet-generator', 'Video Frame Contact Sheet Generator', 'Capture evenly spaced video frames and arrange them into a downloadable contact sheet.', 'video-tools', 'grid', 'pink', 'Create Contact Sheet', 'file', 'Long or high-resolution video can take more memory and processing time.' ),
        array( 'audio-joiner', 'Audio Joiner', 'Decode compatible local audio files, join them in order and download a WAV copy.', 'audio-tools', 'pulse', 'green', 'Join Audio', 'files', 'All sources are resampled to a common rate; check transitions and total file size.' ),
        array( 'audio-volume-normalizer', 'Audio Volume Normalizer', 'Analyze peak level, apply safe gain and export a normalized WAV copy in the browser.', 'audio-tools', 'gauge', 'orange', 'Normalize Audio', 'file', 'Peak normalization is not the same as broadcast loudness mastering.' ),
        array( 'audio-silence-trimmer', 'Audio Silence Trimmer', 'Detect quiet audio at the beginning and end and export the retained section as WAV.', 'audio-tools', 'resize', 'blue', 'Trim Silence', 'file', 'Choose a conservative threshold so intentional quiet speech or ambience is not removed.' ),
        array( 'media-duration-calculator', 'Media Duration Calculator', 'Add durations from multiple local audio or video files and display the total runtime.', 'calculators', 'calendar', 'purple', 'Calculate Duration', 'files', 'Unreadable codecs are reported instead of being silently treated as zero.' ),
        array( 'video-bitrate-calculator', 'Video Bitrate Calculator', 'Estimate bitrate from file size and duration or calculate a target bitrate for a size limit.', 'calculators', 'calculator', 'green', 'Calculate Bitrate', 'builder', 'Allow space for audio and container overhead when choosing an encoding target.' ),
        array( 'video-resolution-aspect-calculator', 'Video Resolution & Aspect Calculator', 'Calculate matching video dimensions, simplified ratios and common resolution comparisons.', 'calculators', 'resize', 'blue', 'Calculate Resolution', 'builder', 'Encoders may require even-numbered dimensions and platform-specific limits.' ),
        array( 'webvtt-validator', 'WebVTT Validator', 'Check WEBVTT headers, cue timestamps, sequence order and common formatting problems.', 'video-tools', 'check', 'purple', 'Validate WebVTT', 'text', 'Player-specific styling and region support still require testing in the destination player.' ),
        array( 'youtube-chapter-generator', 'Video Chapter Timestamp Generator', 'Build and validate a chapter list from titles and timestamps for video descriptions.', 'video-tools', 'calendar', 'orange', 'Generate Chapters', 'text', 'The first chapter should start at 00:00 and chapter spacing must meet the target platform rules.' ),

        /* PDF and general file workflows. */
        array( 'pdf-metadata-cleaner', 'PDF Metadata Viewer & Remover', 'View common PDF document metadata and download a copy with metadata fields cleared.', 'pdf-tools', 'shield', 'purple', 'Inspect PDF', 'file', 'Visible page content, annotations and embedded files can still contain identifying information.' ),
        array( 'extract-images-from-pdf', 'Extract Images from PDF', 'Render PDF pages as downloadable PNG or JPEG images in a ZIP archive.', 'pdf-tools', 'image', 'blue', 'Extract Images', 'file', 'Rendered page images preserve appearance but are not the original compressed image objects.' ),
        array( 'flatten-pdf-forms', 'Flatten PDF Forms', 'Flatten interactive form fields into a non-editable PDF copy for consistent viewing.', 'pdf-tools', 'check', 'green', 'Flatten PDF', 'file', 'Flattening is difficult to reverse, so keep the original interactive document.' ),
        array( 'pdf-grayscale-converter', 'PDF Grayscale Converter', 'Render PDF pages in grayscale and download a print-friendly PDF copy.', 'pdf-tools', 'image', 'orange', 'Convert PDF', 'file', 'Rendered conversion can flatten selectable text and interactive elements.' ),
        array( 'pdf-page-size-converter', 'PDF Page Size Converter', 'Place PDF pages onto A4, Letter or custom-size pages with fit or fill scaling.', 'pdf-tools', 'resize', 'blue', 'Resize PDF', 'file', 'Inspect margins, orientation and small text after scaling.' ),
        array( 'pdf-bookmark-extractor', 'PDF Bookmark Extractor', 'Read a PDF outline and export bookmark titles, levels and page destinations when available.', 'pdf-tools', 'word', 'purple', 'Extract Bookmarks', 'file', 'Some PDFs use named destinations or encrypted outlines that cannot be resolved in a browser.' ),
        array( 'pdf-attachment-extractor', 'PDF Attachment Extractor', 'List and download embedded file attachments found in a supported PDF document.', 'pdf-tools', 'download', 'green', 'Extract Attachments', 'file', 'Treat extracted attachments as untrusted files and scan them before opening.' ),
        array( 'duplicate-file-finder', 'Duplicate File Finder', 'Group selected local files by size and SHA-256 content hash without uploading them.', 'other-tools', 'search', 'orange', 'Find Duplicates', 'files', 'Review paths and filenames before deleting any original file outside this browser tool.' ),
        array( 'folder-tree-generator', 'Folder Tree Generator', 'Create a clean text or Markdown directory tree from a selected local folder.', 'other-tools', 'grid', 'blue', 'Generate Tree', 'folder', 'Browser folder access includes only the files you explicitly select.' ),
        array( 'filename-cleaner', 'Filename Cleaner', 'Normalize unsafe characters, spacing, case and separators in a list of filenames.', 'other-tools', 'word', 'green', 'Clean Names', 'text', 'Check for duplicate cleaned names before renaming files on your device.' ),

        /* Focused quality expansion: distinct workflows not supplied by the theme. */
        array( 'json-diff-patch-generator', 'JSON Diff & JSON Patch Generator', 'Compare two JSON documents and create an RFC 6902-style patch with readable change details.', 'developer-tools', 'code', 'purple', 'Compare JSON', 'builder', 'Array changes are compared by position; review patches for arrays whose items were reordered rather than edited.' ),
        array( 'openapi-swagger-json-validator', 'OpenAPI & Swagger JSON Validator', 'Validate a JSON OpenAPI or Swagger document and summarize paths, methods, parameters and response coverage.', 'developer-tools', 'check', 'green', 'Validate API Spec', 'builder', 'This focused browser check covers important structural rules but does not replace the complete official specification or an integration test.' ),
        array( 'http-request-curl-builder', 'HTTP Request & cURL Builder', 'Build a readable cURL command from a URL, method, headers, authentication and optional request body.', 'developer-tools', 'code', 'blue', 'Build cURL', 'builder', 'The command is generated but never executed; protect real credentials and confirm every destination before running it.' ),
        array( 'dns-zone-file-validator', 'DNS Zone File Validator & Conflict Checker', 'Inspect pasted DNS zone records for common syntax errors, duplicates, CNAME conflicts and mail-record issues.', 'developer-tools', 'globe', 'orange', 'Validate Zone', 'builder', 'The checker supports common record forms and cannot confirm delegation, propagation or live authoritative DNS responses.' ),
        array( 'html-accessibility-markup-checker', 'HTML Accessibility Markup Checker', 'Check pasted HTML for common alternative-text, label, heading, link, landmark, language and duplicate-ID problems.', 'developer-tools', 'check', 'blue', 'Check Markup', 'builder', 'Automated markup checks cover only detectable patterns and do not certify WCAG conformance or replace keyboard and screen-reader testing.' ),
        array( 'unicode-normalization-converter', 'Unicode Normalization Converter', 'Normalize text to NFC, NFD, NFKC or NFKD and inspect character and code-point changes.', 'text-tools', 'word', 'green', 'Normalize Text', 'builder', 'Compatibility forms can intentionally change the representation of styled symbols and should not be applied blindly to identifiers.' ),
        array( 'css-grid-flexbox-builder', 'CSS Grid & Flexbox Layout Builder', 'Generate responsive Grid or Flexbox CSS and preview a configurable multi-item layout.', 'developer-tools', 'grid', 'pink', 'Build Layout', 'builder', 'The preview demonstrates layout behavior only; test the generated CSS with real content and project breakpoints.' ),
        array( 'hmac-generator-verifier', 'HMAC Generator & Signature Verifier', 'Generate or verify HMAC-SHA-256, SHA-384 or SHA-512 signatures locally in the browser.', 'developer-tools', 'shield', 'purple', 'Generate HMAC', 'builder', 'HMAC security depends on a strong secret and an exact shared byte encoding; do not paste production secrets into an untrusted device.' ),

        /* Marketing and practical business calculators. */
        array( 'digital-advertising-calculator', 'Digital Advertising Metrics Calculator', 'Calculate CTR, CPC, CPM, CPA, conversion rate, ROAS and estimated ad revenue in one report.', 'marketing-tools', 'gauge', 'blue', 'Calculate Metrics', 'builder', 'Use figures from the same date range and attribution model for a meaningful comparison.' ),
        array( 'break-even-calculator', 'Break-Even Calculator', 'Calculate break-even units and revenue from fixed cost, selling price and variable cost.', 'business-tools', 'calculator', 'green', 'Calculate Break-Even', 'builder', 'Real operations can include stepped costs, taxes, returns and capacity limits.' ),
        array( 'roi-calculator', 'ROI Calculator', 'Calculate return on investment, net return and annualized ROI from cost, value and duration.', 'calculators', 'calculator', 'purple', 'Calculate ROI', 'builder', 'ROI does not represent risk, cash-flow timing or tax treatment.' ),
        array( 'discount-calculator', 'Discount Calculator', 'Calculate sale price, amount saved and combined sequential discounts.', 'calculators', 'percent', 'orange', 'Calculate Discount', 'builder', 'Sequential discounts are multiplied rather than simply added together.' ),
        array( 'commission-calculator', 'Commission Calculator', 'Calculate flat, percentage or tiered sales commission from entered performance.', 'business-tools', 'calculator', 'blue', 'Calculate Commission', 'builder', 'Confirm thresholds, returns, caps and local payroll rules with the actual plan.' ),
        array( 'compound-interest-calculator', 'Compound Interest Calculator', 'Project principal growth with regular contributions, compounding frequency and duration.', 'calculators', 'bank', 'green', 'Calculate Growth', 'builder', 'The projection is illustrative and excludes taxes, fees, inflation and changing returns.' ),
        array( 'savings-goal-calculator', 'Savings Goal Calculator', 'Estimate the regular contribution needed to reach a savings goal with optional interest.', 'calculators', 'bank', 'purple', 'Plan Savings', 'builder', 'Returns are not guaranteed and real contribution dates can change the outcome.' ),
        array( 'working-days-calculator', 'Working Days Calculator', 'Count weekdays between two dates with optional excluded holiday dates.', 'calculators', 'calendar', 'blue', 'Count Working Days', 'builder', 'Public holidays and workweeks differ by country and employer.' ),
        array( 'overtime-pay-calculator', 'Overtime Pay Calculator', 'Estimate regular and overtime gross pay from hours, base rate and multiplier.', 'calculators', 'calculator', 'orange', 'Calculate Pay', 'builder', 'Confirm overtime eligibility, thresholds, deductions and local employment rules.' ),
        array( 'meeting-cost-calculator', 'Meeting Cost Calculator', 'Estimate meeting cost from duration and participant hourly rates.', 'business-tools', 'calendar', 'purple', 'Calculate Meeting Cost', 'builder', 'Salary-based hourly rates may not include benefits, overhead or opportunity cost.' ),
        array( 'campaign-name-generator', 'Campaign Name Generator', 'Create consistent campaign names from brand, channel, market, objective and date fields.', 'marketing-tools', 'word', 'green', 'Generate Names', 'builder', 'Use one documented naming convention across the team and analytics platform.' ),
        array( 'utm-link-validator', 'UTM Link Validator', 'Inspect campaign URLs for missing, duplicate or inconsistent UTM parameters.', 'marketing-tools', 'link', 'blue', 'Validate UTM Link', 'text', 'Validation checks structure and consistency, not whether analytics received the visit.' ),
        array( 'social-media-character-counter', 'Social Media Character Counter', 'Count characters, words, hashtags and URLs against selectable platform writing limits.', 'marketing-tools', 'word', 'pink', 'Check Length', 'builder', 'Platform limits and URL counting rules can change; verify critical campaigns before publishing.' ),
        array( 'email-subject-line-analyzer', 'Email Subject Line Analyzer', 'Review subject length, capitalization, punctuation, spam-risk phrases and preview text balance.', 'marketing-tools', 'word', 'orange', 'Analyze Subject', 'builder', 'Heuristic feedback cannot predict inbox placement or subscriber response.' ),
        array( 'affiliate-earnings-calculator', 'Affiliate Earnings Calculator', 'Estimate clicks, conversions, sales value, commission and earnings per click.', 'marketing-tools', 'calculator', 'green', 'Estimate Earnings', 'builder', 'Actual results depend on attribution, reversals, cookie windows and program terms.' ),
    );

    $configs = array();
    foreach ( $rows as $row ) {
        $category = $row[3];
        $privacy = in_array( $category, array( 'website-tools' ), true )
            ? 'Only the public URL you enter is checked. Do not enter login details, private links or confidential information.'
            : 'Your input is processed in the browser where supported and is not intentionally stored by this tool.';
        $configs[ $row[0] ] = array(
            'slug'       => $row[0],
            'title'      => $row[1],
            'short'      => $row[1],
            'desc'       => $row[2],
            'long'       => $row[2] . ' The interface provides a focused workflow, clear output and export or copy controls where useful. ' . $row[8],
            'icon'       => $row[4],
            'color'      => $row[5],
            'category'   => $category,
            'badge'      => '',
            'featured'   => in_array( $row[0], array( 'webpage-article-extractor', 'website-font-finder', 'website-asset-extractor', 'csp-header-builder', 'digital-advertising-calculator' ), true ),
            'cta'        => $row[6],
            'illo'       => in_array( $category, array( 'image-tools', 'video-tools', 'audio-tools' ), true ) ? 'image-convert' : 'code',
            'ui'         => $row[7],
            'limitation' => $row[8],
            'privacy'    => $privacy,
        );
    }
    return $configs;
}

/** Practical input guidance shared by visible copy and managed post content. */
function ufxots_editorial_input_tip( $tool ) {
    $ui = isset( $tool['ui'] ) ? $tool['ui'] : '';
    if ( 'remote-url' === $ui ) {
        return 'Use a complete public HTTP or HTTPS URL and compare the result with the current live page before acting on it.';
    }
    if ( 'remote-two' === $ui ) {
        return 'Use two complete public URLs that represent the same kind of page, then review every reported difference in its original context.';
    }
    if ( in_array( $ui, array( 'file', 'files', 'folder', 'file-or-url' ), true ) ) {
        return 'Work from a copy of each source file, stay within the displayed browser limits, and open every downloaded result before replacing an original.';
    }
    if ( in_array( $ui, array( 'text', 'two-text' ), true ) ) {
        return 'Start with a representative text sample, preserve a source copy, and read the complete output before publishing or applying it elsewhere.';
    }
    return 'Check every entered value, unit and selected option, then rerun the tool with a second realistic example to confirm the workflow.';
}

/** Add bespoke context only where the extension has a genuinely unique guide. */
add_filter( 'alltools_tool_editorial_content', 'ufxots_extend_theme_editorial_content', 20, 2 );
function ufxots_extend_theme_editorial_content( $editorial, $slug ) {
    if ( ! empty( $editorial ) ) return $editorial;

    // The theme builds operation- and category-aware guidance for all suite
    // tools. Returning the old generic cards here duplicated that copy.
    return $editorial;
}

function ufxots_disabled_tools() {
    $disabled = get_option( 'ufxots_disabled_tools', array() );
    return is_array( $disabled ) ? array_values( array_unique( array_map( 'sanitize_key', $disabled ) ) ) : array();
}

function ufxots_tool_is_disabled( $slug ) {
    return in_array( sanitize_key( $slug ), ufxots_disabled_tools(), true );
}

function ufxots_all_plugin_slugs() {
    return array_merge( array( 'website-image-extractor' ), array_keys( ufxots_tool_configs() ) );
}

add_filter( 'alltools_registered_tools', 'ufxots_register_tools', 45 );
function ufxots_register_tools( $tools ) {
    if ( ! is_array( $tools ) ) $tools = array();
    $disabled = ufxots_disabled_tools();
    foreach ( ufxots_tool_configs() as $slug => $config ) {
        if ( in_array( $slug, $disabled, true ) || isset( $tools[ $slug ] ) ) continue;
        $tools[ $slug ] = $config;
    }
    return $tools;
}
