<?php
/** Front-end renderer and assets for the suite tools. */

if ( ! defined( 'ABSPATH' ) ) exit;

/** Include tools embedded with [alltool slug="..."] on ordinary pages. */
function ufxots_page_tool_slugs() {
    $slugs = array();
    if ( ! function_exists('is_singular') || ! is_singular() ) return $slugs;
    if ( is_singular('alltool') ) $slugs[] = sanitize_key((string)get_post_meta(get_queried_object_id(), '_alltool_slug', true));
    $post = get_post();
    if ( $post && has_shortcode($post->post_content, 'alltool') && preg_match_all('/'.get_shortcode_regex(array('alltool')).'/s', $post->post_content, $matches, PREG_SET_ORDER) ) {
        foreach ( $matches as $match ) {
            if ( '[' === $match[1] && ']' === $match[6] ) continue;
            $atts = shortcode_parse_atts($match[3]);
            if ( is_array($atts) && ! empty($atts['slug']) ) $slugs[] = sanitize_key($atts['slug']);
        }
    }
    return array_values(array_unique(array_filter($slugs)));
}
function ufxots_current_tool_slug() {
    $slugs = ufxots_page_tool_slugs();
    return $slugs ? $slugs[0] : '';
}
function ufxots_is_suite_tool_page() {
    $configs = ufxots_tool_configs();
    foreach ( ufxots_page_tool_slugs() as $slug ) {
        if ( isset($configs[$slug]) && ! ufxots_tool_is_disabled($slug) ) return true;
    }
    return false;
}

add_action( 'wp_enqueue_scripts', 'ufxots_enqueue_assets', 45 );

/**
 * Map each extension tool to the one implementation module it needs.
 *
 * overall-tools.js remains the tiny shared runtime and contains the 12 remote
 * utilities listed under the "core" key. Every other tool gets only one
 * additional module instead of downloading all five extension bundles.
 *
 * @return array<string,string[]>
 */
function ufxots_asset_module_map() {
    return array(
        'core' => array(
            'webpage-article-extractor', 'website-font-finder', 'website-asset-extractor',
            'website-color-palette-extractor', 'sitemap-comparison-tool', 'website-migration-url-validator',
            'http-header-comparison', 'rss-feed-finder-validator', 'website-contact-social-extractor',
            'webpage-content-comparison', 'website-charset-language-checker', 'public-source-code-viewer',
        ),
        'data' => array(
            'csp-header-builder', 'htaccess-nginx-redirect-generator', 'responsive-srcset-generator',
            'sri-hash-generator', 'security-txt-generator-checker', 'permissions-policy-generator',
            'referrer-policy-generator-checker', 'pwa-manifest-generator-validator', 'html-table-data-extractor',
            'tracking-parameter-remover', 'url-structure-analyzer', 'json-yaml-converter', 'json-xml-converter',
            'jsonl-json-converter', 'markdown-html-converter', 'html-entity-encoder-decoder',
            'unicode-character-inspector', 'invisible-character-detector', 'url-parser-query-builder',
            'user-agent-parser', 'css-clamp-generator', 'css-gradient-generator', 'css-box-shadow-generator',
            'color-format-converter', 'number-base-converter', 'csv-cleaner-validator', 'csv-splitter-merger',
            'line-ending-converter', 'text-encoding-converter', 'aes-text-encryption', 'file-checksum-verifier',
            'code-secret-scanner', 'mime-file-type-inspector', 'ssh-key-fingerprint-viewer',
            'json-schema-validator', 'html-table-generator', 'filename-cleaner',
        ),
        'media' => array(
            'image-sprite-sheet-generator', 'blurhash-placeholder-generator', 'image-comparison-slider-maker',
            'image-grid-splitter', 'image-tiles-joiner', 'dpi-ppi-calculator', 'image-aspect-ratio-calculator',
            'image-contact-sheet-generator', 'social-image-safe-zone-preview', 'bulk-image-filename-renamer',
            'video-thumbnail-extractor', 'srt-vtt-subtitle-converter', 'subtitle-cleaner-validator',
            'subtitle-line-merger-splitter', 'audio-waveform-image-generator', 'audio-id3-metadata-editor',
            'video-metadata-viewer', 'video-frame-contact-sheet-generator', 'audio-joiner',
            'audio-volume-normalizer', 'audio-silence-trimmer', 'media-duration-calculator',
            'video-bitrate-calculator', 'video-resolution-aspect-calculator', 'webvtt-validator',
            'youtube-chapter-generator',
        ),
        'pdf' => array(
            'pdf-metadata-cleaner', 'extract-images-from-pdf', 'flatten-pdf-forms',
            'pdf-grayscale-converter', 'pdf-page-size-converter', 'pdf-bookmark-extractor',
            'pdf-attachment-extractor', 'duplicate-file-finder', 'folder-tree-generator',
        ),
        'marketing' => array(
            'digital-advertising-calculator', 'break-even-calculator', 'roi-calculator', 'discount-calculator',
            'commission-calculator', 'compound-interest-calculator', 'savings-goal-calculator',
            'working-days-calculator', 'overtime-pay-calculator', 'meeting-cost-calculator',
            'campaign-name-generator', 'utm-link-validator', 'social-media-character-counter',
            'email-subject-line-analyzer', 'affiliate-earnings-calculator',
        ),
        'advanced' => array(
            'json-diff-patch-generator', 'openapi-swagger-json-validator', 'http-request-curl-builder',
            'dns-zone-file-validator', 'html-accessibility-markup-checker', 'unicode-normalization-converter',
            'css-grid-flexbox-builder', 'hmac-generator-verifier',
        ),
    );
}

/** @return string[] */
function ufxots_required_asset_modules( $slugs ) {
    $required = array();
    foreach ( ufxots_asset_module_map() as $module => $module_slugs ) {
        if ( array_intersect( (array) $slugs, $module_slugs ) ) {
            $required[] = $module;
        }
    }
    return $required;
}

function ufxots_enqueue_assets() {
    if ( ! ufxots_is_suite_tool_page() ) return;

    $slugs   = ufxots_page_tool_slugs();
    $modules = ufxots_required_asset_modules( $slugs );

    wp_enqueue_style( 'ufxots-suite', UFXIE_URL . 'assets/overall-tools.css', array(), UFXIE_VERSION );
    wp_enqueue_script( 'ufxots-suite', UFXIE_URL . 'assets/overall-tools.js', array(), UFXIE_VERSION, true );

    $files = array(
        'data'      => 'overall-tools-data.js',
        'media'     => 'overall-tools-media.js',
        'pdf'       => 'overall-tools-pdf.js',
        'marketing' => 'overall-tools-marketing.js',
        'advanced'  => 'overall-tools-advanced.js',
    );
    foreach ( $files as $module => $file ) {
        if ( ! in_array( $module, $modules, true ) ) continue;
        $handle = 'ufxots-suite-' . $module;
        wp_enqueue_script( $handle, UFXIE_URL . 'assets/' . $file, array( 'ufxots-suite' ), UFXIE_VERSION, true );
        wp_script_add_data( $handle, 'strategy', 'defer' );
    }

    wp_localize_script( 'ufxots-suite', 'UFXOTS', array(
        'ajax'       => admin_url( 'admin-ajax.php' ),
        'nonce'      => wp_create_nonce( 'ufxots_run' ),
        'slug'       => ufxots_current_tool_slug(),
        'maxFileMb'  => 50,
        'pdfLib'     => 'https://cdn.jsdelivr.net/npm/pdf-lib@1.17.1/dist/pdf-lib.min.js',
        'pdfJs'      => 'https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.min.js',
        'pdfWorker'  => 'https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.worker.min.js',
        'jsZip'      => 'https://cdn.jsdelivr.net/npm/jszip@3.10.1/dist/jszip.min.js',
        'jsPdf'      => 'https://cdn.jsdelivr.net/npm/jspdf@2.5.2/dist/jspdf.umd.min.js',
    ) );
    wp_script_add_data( 'ufxots-suite', 'strategy', 'defer' );
}

add_filter( 'alltools_render_tool', 'ufxots_render_tool', 45, 2 );
function ufxots_render_tool( $rendered, $slug ) {
    $configs = ufxots_tool_configs();
    if ( ! isset( $configs[ $slug ] ) || ufxots_tool_is_disabled( $slug ) ) return $rendered;
    $tool = $configs[ $slug ];
    $ui   = $tool['ui'];
    $id   = 'ufxots-' . $slug;
    ?>
    <div id="<?php echo esc_attr( $id ); ?>" class="ufxots-tool" data-ufxots-tool="<?php echo esc_attr( $slug ); ?>" data-ui="<?php echo esc_attr( $ui ); ?>">
        <div class="at-tool-grid at-tool-grid-2 ufxots-workspace">
            <div class="at-card ufxots-input-card">
                <div class="ufxots-card-heading">
                    <h2><?php esc_html_e( 'Input and options', 'uptimefixer-image-extractor' ); ?></h2>
                    <p class="at-help"><?php echo esc_html( $tool['desc'] ); ?></p>
                </div>

                <?php ufxots_render_inputs( $slug, $ui ); ?>

                <div class="ufxots-actions">
                    <button type="button" class="at-btn at-btn-primary ufxots-run"><?php echo esc_html( $tool['cta'] ); ?></button>
                    <button type="button" class="at-btn at-btn-outline ufxots-reset"><?php esc_html_e( 'Reset', 'uptimefixer-image-extractor' ); ?></button>
                </div>
                <p class="at-help ufxots-privacy"><strong><?php esc_html_e( 'Privacy:', 'uptimefixer-image-extractor' ); ?></strong> <?php echo esc_html( $tool['privacy'] ); ?></p>
            </div>

            <div class="at-card ufxots-result-card">
                <div class="ufxots-result-head">
                    <div>
                        <span class="ufxots-kicker"><?php esc_html_e( 'Result', 'uptimefixer-image-extractor' ); ?></span>
                        <h3><?php esc_html_e( 'Your output', 'uptimefixer-image-extractor' ); ?></h3>
                    </div>
                    <div class="ufxots-output-actions" hidden>
                        <button type="button" class="ufxots-icon-btn ufxots-copy" title="<?php esc_attr_e( 'Copy result', 'uptimefixer-image-extractor' ); ?>">Copy</button>
                        <button type="button" class="ufxots-icon-btn ufxots-download" title="<?php esc_attr_e( 'Download result', 'uptimefixer-image-extractor' ); ?>">Download</button>
                    </div>
                </div>
                <div class="ufxots-status" role="status" aria-live="polite"><?php esc_html_e( 'Add the required input and run the tool.', 'uptimefixer-image-extractor' ); ?></div>
                <div class="ufxots-preview" hidden></div>
                <div class="ufxots-output" aria-live="polite"></div>
                <textarea class="ufxots-raw-output" hidden readonly></textarea>
                <canvas class="ufxots-canvas" hidden></canvas>
            </div>
        </div>

    </div>
    <?php
    return true;
}

function ufxots_render_inputs( $slug, $ui ) {
    if ( 'remote-url' === $ui ) {
        ufxots_input( 'url', 'Public webpage URL', 'https://example.com/page/', 'primary' );
    } elseif ( 'remote-two' === $ui ) {
        ufxots_input( 'url', 'First public URL', 'https://old.example.com/page/', 'primary' );
        ufxots_input( 'url', 'Second public URL', 'https://new.example.com/page/', 'secondary' );
    } elseif ( 'text' === $ui ) {
        ufxots_textarea( 'Input', ufxots_text_placeholder( $slug ), 'primary' );
    } elseif ( 'two-text' === $ui ) {
        ufxots_textarea( 'JSON document', '{"name":"Example"}', 'primary' );
        ufxots_textarea( 'JSON Schema', '{"type":"object","required":["name"]}', 'secondary' );
    } elseif ( 'file' === $ui ) {
        ufxots_file_input( 'Choose a local file', ufxots_file_accept( $slug ), false );
    } elseif ( 'files' === $ui ) {
        ufxots_file_input( 'Choose local files', ufxots_file_accept( $slug ), true );
    } elseif ( 'folder' === $ui ) {
        ?>
        <div class="at-field">
            <label for="ufxots-files-<?php echo esc_attr( $slug ); ?>"><?php esc_html_e( 'Choose a local folder', 'uptimefixer-image-extractor' ); ?></label>
            <input id="ufxots-files-<?php echo esc_attr( $slug ); ?>" class="at-input ufxots-files" type="file" multiple webkitdirectory directory>
            <small><?php esc_html_e( 'The browser shares only the selected folder entries with this page.', 'uptimefixer-image-extractor' ); ?></small>
        </div>
        <?php
    } elseif ( 'file-or-url' === $ui ) {
        ufxots_input( 'url', 'Public asset URL (optional)', 'https://cdn.example.com/app.js', 'primary' );
        ufxots_file_input( 'Or choose a local file', '*/*', false );
        ?>
        <div class="at-field">
            <label for="ufxots-algorithm-<?php echo esc_attr( $slug ); ?>"><?php esc_html_e( 'Hash algorithm', 'uptimefixer-image-extractor' ); ?></label>
            <select id="ufxots-algorithm-<?php echo esc_attr( $slug ); ?>" class="at-input ufxots-option" data-name="algorithm"><option>SHA-384</option><option>SHA-256</option><option>SHA-512</option></select>
        </div>
        <?php
    } else {
        echo '<div class="ufxots-dynamic-fields" data-builder="' . esc_attr( $slug ) . '"></div>';
    }
    if ( 'builder' !== $ui ) {
        echo '<div class="ufxots-dynamic-fields" data-builder="' . esc_attr( $slug ) . '"></div>';
    }
}

function ufxots_input( $type, $label, $placeholder, $class ) {
    $id = 'ufxots-' . sanitize_html_class( $class ) . '-' . wp_rand( 1000, 9999 );
    ?>
    <div class="at-field">
        <label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
        <input id="<?php echo esc_attr( $id ); ?>" class="at-input ufxots-<?php echo esc_attr( $class ); ?>" type="<?php echo esc_attr( $type ); ?>" <?php echo 'url' === $type ? 'inputmode="url" autocomplete="url"' : ''; ?> placeholder="<?php echo esc_attr( $placeholder ); ?>">
    </div>
    <?php
}

function ufxots_textarea( $label, $placeholder, $class ) {
    $id = 'ufxots-' . sanitize_html_class( $class ) . '-' . wp_rand( 1000, 9999 );
    ?>
    <div class="at-field">
        <label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
        <textarea id="<?php echo esc_attr( $id ); ?>" class="at-textarea ufxots-<?php echo esc_attr( $class ); ?>" rows="12" placeholder="<?php echo esc_attr( $placeholder ); ?>"></textarea>
    </div>
    <?php
}

function ufxots_file_input( $label, $accept, $multiple ) {
    $id = 'ufxots-files-' . wp_rand( 1000, 9999 );
    ?>
    <div class="ufxots-drop" data-drop>
        <div class="ufxots-drop-icon">＋</div>
        <strong><?php echo esc_html( $label ); ?></strong>
        <span><?php esc_html_e( 'Browse or drop files here', 'uptimefixer-image-extractor' ); ?></span>
        <input id="<?php echo esc_attr( $id ); ?>" class="ufxots-files" type="file" accept="<?php echo esc_attr( $accept ); ?>" <?php echo $multiple ? 'multiple' : ''; ?>>
        <div class="ufxots-file-list"></div>
    </div>
    <?php
}

function ufxots_file_accept( $slug ) {
    if ( false !== strpos( $slug, 'pdf' ) ) return 'application/pdf,.pdf';
    if ( false !== strpos( $slug, 'subtitle' ) || false !== strpos( $slug, 'webvtt' ) || 'srt-vtt-subtitle-converter' === $slug ) return '.srt,.vtt,text/plain';
    if ( false !== strpos( $slug, 'audio' ) ) return 'audio/*,.mp3,.wav,.ogg,.m4a';
    if ( false !== strpos( $slug, 'video' ) || 'media-duration-calculator' === $slug ) return 'video/*,audio/*';
    if ( false !== strpos( $slug, 'image' ) || false !== strpos( $slug, 'blurhash' ) || 'social-image-safe-zone-preview' === $slug ) return 'image/*';
    if ( false !== strpos( $slug, 'csv' ) ) return '.csv,text/csv,text/plain';
    if ( false !== strpos( $slug, 'text-encoding' ) || false !== strpos( $slug, 'line-ending' ) ) return 'text/*,.txt,.csv,.md,.json,.xml,.html,.css,.js';
    return '*/*';
}

function ufxots_text_placeholder( $slug ) {
    $placeholders = array(
        'htaccess-nginx-redirect-generator' => "/old-page/ https://example.com/new-page/\n/old-category/(.*) https://example.com/new-category/$1",
        'website-migration-url-validator'   => "https://old.example.com/page/ https://new.example.com/page/\nhttps://old.example.com/about/ https://new.example.com/about/",
        'tracking-parameter-remover'        => 'https://example.com/product/?utm_source=newsletter&utm_medium=email&variant=blue',
        'url-structure-analyzer'             => 'https://www.example.com/category/page/?id=42&utm_source=test#details',
        'unicode-character-inspector'       => 'Paste text, emoji, symbols or accented characters here.',
        'invisible-character-detector'      => 'Paste text that may contain zero-width or control characters.',
        'user-agent-parser'                 => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0 Safari/537.36',
        'csv-cleaner-validator'             => "name,email\nAlice,alice@example.com\nBob,bob@example.com",
        'code-secret-scanner'               => 'Paste a code sample to scan. Do not paste a real active secret merely to test the tool.',
        'ssh-key-fingerprint-viewer'        => 'ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAA... comment',
        'webvtt-validator'                  => "WEBVTT\n\n00:00.000 --> 00:03.000\nFirst caption",
        'youtube-chapter-generator'         => "00:00 Introduction\n01:25 First topic\n04:10 Summary",
        'filename-cleaner'                  => "My Final Image (1).JPG\nClient File #2.pdf",
        'utm-link-validator'                => 'https://example.com/?utm_source=newsletter&utm_medium=email&utm_campaign=launch',
    );
    return isset( $placeholders[ $slug ] ) ? $placeholders[ $slug ] : 'Enter or paste the source data here.';
}
