<?php
/**
 * Plugin Name: UptimeFixer Overall Tools Suite
 * Plugin URI:  https://uptimefixer.com/
 * Description: Adds the Website Image Extractor and a complete suite of secure online utilities to the Uptime Fixer theme without modifying theme files or existing content.
 * Version:     2.5.0
 * Author:      DigiPlex Creations
 * License:     GPL-2.0-or-later
 * Text Domain: uptimefixer-image-extractor
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'UFXIE_VERSION', '2.5.0' );
define( 'UFXIE_FILE', __FILE__ );
define( 'UFXIE_URL', plugin_dir_url( __FILE__ ) );
define( 'UFXIE_PATH', plugin_dir_path( __FILE__ ) );

require_once UFXIE_PATH . 'inc/definitions.php';
require_once UFXIE_PATH . 'inc/render.php';
require_once UFXIE_PATH . 'inc/lifecycle.php';
require_once UFXIE_PATH . 'inc/seo.php';
require_once UFXIE_PATH . 'inc/remote.php';

/** The single tool definition injected through the theme's public extension hook. */
function ufxie_tool_definition() {
    return array(
        'title'    => 'Website Image Extractor',
        'short'    => 'Website Image Extractor',
        'desc'     => 'Extract, preview and export public image URLs found on a webpage.',
        'long'     => 'Enter a public webpage URL to find normal images, lazy-loaded sources, responsive srcset candidates, social images and inline background references. Preview the results, open originals, copy URLs or export a CSV report.',
        'icon'     => 'image',
        'color'    => 'green',
        'category' => 'website-tools',
        'badge'    => '',
        'featured' => true,
        'cta'      => 'Extract Images',
        'illo'     => 'image-convert',
    );
}

add_filter( 'alltools_registered_tools', 'ufxie_register_tool', 40 );
function ufxie_register_tool( $tools ) {
    if ( ! is_array( $tools ) ) $tools = array();
    if ( function_exists( 'ufxots_tool_is_disabled' ) && ufxots_tool_is_disabled( 'website-image-extractor' ) ) return $tools;
    $tools['website-image-extractor'] = ufxie_tool_definition();
    return $tools;
}

/** Render inside the existing Uptime Fixer single-tool layout. */
add_filter( 'alltools_render_tool', 'ufxie_render_tool', 40, 2 );
function ufxie_render_tool( $rendered, $slug ) {
    if ( 'website-image-extractor' !== $slug ) return $rendered;
    ?>
    <div class="at-tool-grid at-tool-grid-2 ufxie-tool" data-ufxie-extractor>
        <div class="at-card">
            <h2><?php esc_html_e( 'Page to inspect', 'uptimefixer-image-extractor' ); ?></h2>
            <p class="at-help"><?php esc_html_e( 'Enter one public webpage URL. The tool reads its HTML and finds normal, lazy, responsive, social and inline-background image references.', 'uptimefixer-image-extractor' ); ?></p>
            <div class="at-field">
                <label for="ufxie-page-url"><?php esc_html_e( 'Public webpage URL', 'uptimefixer-image-extractor' ); ?></label>
                <input id="ufxie-page-url" class="at-input" type="url" inputmode="url" autocomplete="url" placeholder="https://example.com/page/">
            </div>
            <p class="at-help ufxie-warning"><?php esc_html_e( 'Reuse images only when you own them or have permission. JavaScript-only images and external stylesheet backgrounds may not exist in the fetched HTML.', 'uptimefixer-image-extractor' ); ?></p>
            <button type="button" class="at-btn at-btn-primary ufxie-run"><?php esc_html_e( 'Extract Images', 'uptimefixer-image-extractor' ); ?></button>
        </div>
        <div class="at-card">
            <h3><?php esc_html_e( 'Extracted Images', 'uptimefixer-image-extractor' ); ?></h3>
            <div class="at-help ufxie-status" role="status" aria-live="polite"><?php esc_html_e( 'Enter a public webpage URL to begin.', 'uptimefixer-image-extractor' ); ?></div>
            <div class="ufxie-output"></div>
        </div>
    </div>
    <?php
    return true;
}

/** Detect this plugin's generated tool page. */
function ufxie_is_tool_page() {
    return in_array( 'website-image-extractor', ufxots_page_tool_slugs(), true );
}

add_action( 'wp_enqueue_scripts', 'ufxie_enqueue_assets', 40 );
function ufxie_enqueue_assets() {
    if ( ! ufxie_is_tool_page() ) return;
    wp_enqueue_style( 'ufxie-image-extractor', UFXIE_URL . 'assets/image-extractor.css', array(), UFXIE_VERSION );
    wp_enqueue_script( 'ufxie-image-extractor', UFXIE_URL . 'assets/image-extractor.js', array(), UFXIE_VERSION, true );
    wp_script_add_data( 'ufxie-image-extractor', 'strategy', 'defer' );
    wp_localize_script( 'ufxie-image-extractor', 'UFXIE', array(
        'ajax'  => admin_url( 'admin-ajax.php' ),
        'nonce' => wp_create_nonce( 'ufxie_extract' ),
    ) );
}

/** Allow public image previews through the theme's filterable CSP. */
add_filter( 'alltools_content_security_policy', 'ufxie_allow_preview_images_in_policy', 30 );
function ufxie_allow_preview_images_in_policy( $policy ) {
    if ( ! ufxie_is_tool_page() ) return $policy;
    $policy = (string) $policy;
    if ( preg_match( '/(^|;)\s*img-src\s+([^;]*)/i', $policy, $match ) ) {
        if ( ! preg_match( '/(^|\s)https:(\s|$)/i', trim( $match[2] ) ) ) {
            $replacement = $match[1] . ' img-src ' . trim( $match[2] ) . ' https:';
            $policy = preg_replace( '/(^|;)\s*img-src\s+([^;]*)/i', $replacement, $policy, 1 );
        }
    } else {
        $policy = rtrim( trim( $policy ), ';' ) . "; img-src 'self' data: blob: https:";
    }
    return $policy;
}

/** Legacy fallback for Uptime Fixer releases without the CSP filter. */
add_action( 'send_headers', 'ufxie_allow_preview_images_legacy_headers', 100 );
function ufxie_allow_preview_images_legacy_headers() {
    if ( defined( 'ALLTOOLS_VERSION' ) && version_compare( ALLTOOLS_VERSION, '3.8.1', '>=' ) ) return;
    if ( headers_sent() || ! ufxie_is_tool_page() || ! function_exists( 'headers_list' ) ) return;
    foreach ( headers_list() as $line ) {
        if ( 0 !== stripos( $line, 'Content-Security-Policy:' ) ) continue;
        $policy = trim( substr( $line, strlen( 'Content-Security-Policy:' ) ) );
        $policy = ufxie_allow_preview_images_in_policy( $policy );
        header( 'Content-Security-Policy: ' . $policy, true );
        break;
    }
}

/** Create only this plugin's missing tool post; never edit an existing tool. */
register_activation_hook( __FILE__, 'ufxie_mark_setup_required' );
function ufxie_mark_setup_required() {
    update_option( 'ufxie_setup_required', '1', false );
}

add_action( 'admin_init', 'ufxie_maybe_create_tool_page', 25 );
function ufxie_maybe_create_tool_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;
    if ( '1' !== get_option( 'ufxie_setup_required' ) && UFXIE_VERSION === get_option( 'ufxie_setup_version' ) ) return;
    if ( ! post_type_exists( 'alltool' ) || ! function_exists( 'alltools_all' ) ) return;
    if ( function_exists( 'ufxots_tool_is_disabled' ) && ufxots_tool_is_disabled( 'website-image-extractor' ) ) {
        delete_option( 'ufxie_setup_required' );
        update_option( 'ufxie_setup_version', UFXIE_VERSION, false );
        return;
    }

    $existing = get_posts( array(
        'post_type'      => 'alltool',
        'post_status'    => array( 'publish', 'future', 'draft', 'pending', 'private', 'trash', 'auto-draft' ),
        'meta_key'       => '_alltool_slug',
        'meta_value'     => 'website-image-extractor',
        'fields'         => 'ids',
        'posts_per_page' => 1,
        'no_found_rows'  => true,
    ) );

    if ( ! $existing ) {
        $post_id = wp_insert_post( array(
            'post_type'    => 'alltool',
            'post_status'  => 'publish',
            'post_name'    => 'website-image-extractor',
            'post_title'   => 'Website Image Extractor',
            'post_excerpt' => 'Extract and preview public image URLs from a webpage.',
            'post_content' => '<p>Use this webpage image extractor to identify public image references in fetched HTML. Results can include standard image elements, lazy-load attributes, responsive image candidates, social metadata and inline background references.</p><p>The tool does not copy third-party files into WordPress storage. Review ownership and licensing before downloading or reusing an image. Dynamic JavaScript-only assets and external stylesheet backgrounds may not appear in a server-fetched page.</p>',
        ), true );
        if ( is_wp_error( $post_id ) ) {
            update_option( 'ufxie_setup_error', sanitize_text_field( $post_id->get_error_message() ), false );
            return;
        }
        update_post_meta( $post_id, '_alltool_slug', 'website-image-extractor' );
        if ( function_exists( 'alltools_sync_yoast_meta_for_post' ) ) alltools_sync_yoast_meta_for_post( $post_id );
        flush_rewrite_rules( false );
    } else {
        $post_id = (int) $existing[0];
        // Respect a page status chosen by the site owner. Activation creates
        // missing pages but never republishes an existing draft or private page.
    }

    delete_option( 'ufxie_setup_error' );
    delete_option( 'ufxie_setup_required' );
    update_option( 'ufxie_setup_version', UFXIE_VERSION, false );
}

/** Keep the content but prevent a non-working page from staying indexable while disabled. */
register_deactivation_hook( __FILE__, 'ufxie_deactivate_tool_page' );
function ufxie_deactivate_tool_page() {
    if ( ! post_type_exists( 'alltool' ) ) return;
    $slugs = array( 'website-image-extractor' );
    if ( function_exists( 'ufxots_all_plugin_slugs' ) ) $slugs = ufxots_all_plugin_slugs();
    $ids = get_posts( array(
        'post_type'      => 'alltool',
        'post_status'    => array( 'publish', 'future', 'draft', 'pending', 'private', 'trash', 'auto-draft' ),
        'meta_key'       => '_alltool_slug',
        'meta_value'     => $slugs,
        'meta_compare'   => 'IN',
        'fields'         => 'ids',
        'posts_per_page' => -1,
        'no_found_rows'  => true,
    ) );
    $restore = array();
    foreach ( $ids as $id ) {
        $status = get_post_status( $id );
        if ( 'publish' === $status || 'private' === $status ) {
            $restore[ (int) $id ] = $status;
            wp_update_post( array( 'ID' => (int) $id, 'post_status' => 'draft' ) );
        }
    }
    if ( $restore ) update_option( 'ufxots_restore_statuses', $restore, false );
    flush_rewrite_rules( false );
}

add_action( 'admin_notices', 'ufxie_admin_notices' );
function ufxie_admin_notices() {
    if ( ! current_user_can( 'manage_options' ) ) return;
    if ( ! function_exists( 'alltools_all' ) ) {
        echo '<div class="notice notice-warning"><p><strong>UptimeFixer Overall Tools Suite:</strong> Activate the Uptime Fixer theme to add these tools automatically.</p></div>';
        return;
    }
    $error = get_option( 'ufxie_setup_error' );
    if ( $error ) echo '<div class="notice notice-error"><p><strong>UptimeFixer Overall Tools Suite:</strong> ' . esc_html( $error ) . '</p></div>';
}

/** Public AJAX routes. */
add_action( 'wp_ajax_ufxie_extract_images', 'ufxie_ajax_extract_images' );
add_action( 'wp_ajax_nopriv_ufxie_extract_images', 'ufxie_ajax_extract_images' );
add_action( 'wp_ajax_ufxie_refresh_nonce', 'ufxie_ajax_refresh_nonce' );
add_action( 'wp_ajax_nopriv_ufxie_refresh_nonce', 'ufxie_ajax_refresh_nonce' );
add_action( 'wp_ajax_ufxots_refresh_nonce', 'ufxots_ajax_refresh_nonce' );
add_action( 'wp_ajax_nopriv_ufxots_refresh_nonce', 'ufxots_ajax_refresh_nonce' );

function ufxie_rate_limit( $bucket = 'extract', $limit = 10 ) {
    $ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
    $key = 'ufxie_' . substr( hash_hmac( 'sha256', $bucket . '|' . $ip, wp_salt( 'nonce' ) ), 0, 32 );
    $count = (int) get_transient( $key );
    if ( $count >= $limit ) wp_send_json_error( array( 'message' => 'Too many requests. Please wait a minute and try again.' ), 429 );
    set_transient( $key, $count + 1, MINUTE_IN_SECONDS );
}

function ufxie_ajax_refresh_nonce() {
    ufxie_rate_limit( 'nonce', 20 );
    nocache_headers();
    wp_send_json_success( array( 'nonce' => wp_create_nonce( 'ufxie_extract' ) ) );
}

/** Issue a fresh suite nonce so cached public pages recover without a reload. */
function ufxots_ajax_refresh_nonce() {
    ufxie_rate_limit( 'suite_nonce', 20 );
    nocache_headers();
    wp_send_json_success( array( 'nonce' => wp_create_nonce( 'ufxots_run' ) ) );
}

function ufxie_verify_ajax() {
    $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
    if ( ! $nonce || ! wp_verify_nonce( $nonce, 'ufxie_extract' ) ) wp_send_json_error( array( 'message' => 'Invalid request.' ), 403 );
    ufxie_rate_limit();
}

/** Reject localhost, private/reserved IPs and unresolvable hosts. */
function ufxie_host_is_public( $host ) {
    if ( function_exists( 'ufx_host_is_public' ) ) return ufx_host_is_public( $host );
    $host = strtolower( trim( (string) $host, "[] .\t\n\r\0\x0B" ) );
    if ( ! $host || strlen( $host ) > 253 || 'localhost' === $host || preg_match( '/(?:^|\.)local(?:host)?$/i', $host ) ) return false;
    if ( filter_var( $host, FILTER_VALIDATE_IP ) ) return false !== filter_var( $host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE );
    if ( ! preg_match( '/^[a-z0-9.-]+$/i', $host ) || false === strpos( $host, '.' ) ) return false;
    $ips = gethostbynamel( $host );
    if ( ! is_array( $ips ) || ! $ips ) return false;
    foreach ( $ips as $ip ) if ( false === filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) return false;
    return true;
}

function ufxie_clean_url( $raw ) {
    if ( function_exists( 'ufx_clean_url' ) ) return ufx_clean_url( $raw );
    $raw = trim( (string) $raw );
    if ( ! preg_match( '#^https?://#i', $raw ) ) $raw = 'https://' . $raw;
    $parts = wp_parse_url( $raw );
    if ( empty( $parts['host'] ) || ! empty( $parts['user'] ) || ! empty( $parts['pass'] ) ) return '';
    if ( isset( $parts['port'] ) && ! in_array( (int) $parts['port'], array( 80, 443 ), true ) ) return '';
    if ( ! ufxie_host_is_public( $parts['host'] ) ) return '';
    return esc_url_raw( $raw, array( 'http', 'https' ) );
}

/** Convert an HTML-relative asset reference to a safe absolute URL. */
function ufxie_absolute_url( $base, $value ) {
    $value = trim( html_entity_decode( (string) $value, ENT_QUOTES ) );
    if ( '' === $value || preg_match( '#^(?:data:|blob:|javascript:|mailto:|tel:|#)#i', $value ) ) return '';
    if ( preg_match( '#^https?://#i', $value ) ) return esc_url_raw( $value, array( 'http', 'https' ) );
    $parts = wp_parse_url( $base );
    if ( empty( $parts['scheme'] ) || empty( $parts['host'] ) ) return '';
    if ( 0 === strpos( $value, '//' ) ) return esc_url_raw( $parts['scheme'] . ':' . $value, array( 'http', 'https' ) );
    $origin = $parts['scheme'] . '://' . $parts['host'] . ( isset( $parts['port'] ) ? ':' . (int) $parts['port'] : '' );
    if ( 0 === strpos( $value, '/' ) ) return esc_url_raw( $origin . $value, array( 'http', 'https' ) );
    $path = isset( $parts['path'] ) ? $parts['path'] : '/';
    $directory = preg_replace( '#/[^/]*$#', '/', $path );
    $combined = $origin . $directory . $value;
    $parsed = wp_parse_url( $combined );
    if ( empty( $parsed['path'] ) ) return esc_url_raw( $combined, array( 'http', 'https' ) );
    $segments = array();
    foreach ( explode( '/', $parsed['path'] ) as $segment ) {
        if ( '' === $segment || '.' === $segment ) continue;
        if ( '..' === $segment ) array_pop( $segments );
        else $segments[] = $segment;
    }
    $absolute = $origin . '/' . implode( '/', $segments );
    if ( isset( $parsed['query'] ) ) $absolute .= '?' . $parsed['query'];
    return esc_url_raw( $absolute, array( 'http', 'https' ) );
}

function ufxie_ajax_extract_images() {
    ufxie_verify_ajax();
    $raw = isset( $_POST['url'] ) ? sanitize_text_field( wp_unslash( $_POST['url'] ) ) : '';
    $url = ufxie_clean_url( $raw );
    if ( ! $url ) wp_send_json_error( array( 'message' => 'Enter a valid public HTTP or HTTPS webpage URL.' ), 400 );

    $args = array(
        'timeout'             => 20,
        'redirection'         => 4,
        'reject_unsafe_urls'  => true,
        'sslverify'           => true,
        'limit_response_size' => 2097152,
        'user-agent'          => 'UptimeFixer Overall Tools/' . UFXIE_VERSION . ' (+https://uptimefixer.com/)',
    );
    $response = function_exists( 'ufx_safe_remote_get' ) ? ufx_safe_remote_get( $url, $args ) : wp_safe_remote_get( $url, $args );
    if ( is_wp_error( $response ) ) wp_send_json_error( array( 'message' => $response->get_error_message() ), 502 );
    $code = (int) wp_remote_retrieve_response_code( $response );
    if ( $code < 200 || $code >= 400 ) wp_send_json_error( array( 'message' => 'The webpage returned HTTP ' . $code . '.' ), 502 );
    $html = (string) wp_remote_retrieve_body( $response );
    if ( '' === trim( $html ) ) wp_send_json_error( array( 'message' => 'The webpage returned an empty response.' ), 422 );
    if ( ! class_exists( 'DOMDocument' ) || ! class_exists( 'DOMXPath' ) ) wp_send_json_error( array( 'message' => 'This server needs the PHP DOM extension for image extraction.' ), 503 );

    $dom = new DOMDocument();
    $previous = libxml_use_internal_errors( true );
    $loaded = $dom->loadHTML( '<?xml encoding="utf-8" ?>' . $html, LIBXML_NONET | LIBXML_NOWARNING | LIBXML_NOERROR );
    libxml_clear_errors();
    libxml_use_internal_errors( $previous );
    if ( ! $loaded ) wp_send_json_error( array( 'message' => 'The fetched response could not be parsed as HTML.' ), 422 );
    $xpath = new DOMXPath( $dom );

    $images = array();
    $safe_hosts = array();
    $skipped = 0;
    $responsive = 0;
    $backgrounds = 0;
    $metadata = 0;
    $attempts = 0;
    $image_nodes = $xpath->query( '//img' );

    $add_image = function( $raw_image, $source, $alt = '', $width = '', $height = '' ) use ( &$images, &$safe_hosts, &$skipped, &$attempts, $url ) {
        if ( count( $images ) >= 250 || $attempts >= 400 ) return;
        $attempts++;
        $absolute = ufxie_absolute_url( $url, $raw_image );
        $parts = $absolute ? wp_parse_url( $absolute ) : array();
        if ( empty( $parts['scheme'] ) || empty( $parts['host'] ) || ! in_array( strtolower( $parts['scheme'] ), array( 'http', 'https' ), true ) || ! empty( $parts['user'] ) || ! empty( $parts['pass'] ) || ( isset( $parts['port'] ) && ! in_array( (int) $parts['port'], array( 80, 443 ), true ) ) ) {
            $skipped++;
            return;
        }
        $host = strtolower( rtrim( (string) $parts['host'], '.' ) );
        if ( ! array_key_exists( $host, $safe_hosts ) ) $safe_hosts[ $host ] = ufxie_host_is_public( $host );
        if ( ! $safe_hosts[ $host ] ) {
            $skipped++;
            return;
        }
        $absolute = esc_url_raw( $absolute, array( 'http', 'https' ) );
        if ( ! $absolute || isset( $images[ $absolute ] ) ) return;
        $dimensions = ( $width || $height ) ? ( $width ? (int) $width : '?' ) . ' × ' . ( $height ? (int) $height : '?' ) : 'Not declared';
        $images[ $absolute ] = array(
            'url'        => $absolute,
            'source'     => sanitize_text_field( (string) $source ),
            'alt'        => function_exists( 'alltools_text_slice' ) ? alltools_text_slice( sanitize_text_field( wp_strip_all_tags( (string) $alt ) ), 0, 160 ) : substr( sanitize_text_field( wp_strip_all_tags( (string) $alt ) ), 0, 160 ),
            'dimensions' => sanitize_text_field( $dimensions ),
        );
    };

    $add_srcset = function( $srcset, $source, $alt = '', $width = '', $height = '' ) use ( &$add_image, &$responsive ) {
        foreach ( preg_split( '/\s*,\s*/', (string) $srcset ) as $candidate ) {
            $bits = preg_split( '/\s+/', trim( $candidate ) );
            if ( empty( $bits[0] ) ) continue;
            $responsive++;
            $add_image( $bits[0], $source, $alt, $width, $height );
        }
    };

    foreach ( $image_nodes as $image ) {
        $alt = $image->getAttribute( 'alt' );
        $width = $image->getAttribute( 'width' );
        $height = $image->getAttribute( 'height' );
        foreach ( array( 'src'=>'Image src', 'data-src'=>'Lazy image', 'data-lazy-src'=>'Lazy image', 'data-original'=>'Lazy image', 'data-flickity-lazyload'=>'Lazy image', 'data-lazy'=>'Lazy image' ) as $attribute => $source ) {
            if ( $image->hasAttribute( $attribute ) ) $add_image( $image->getAttribute( $attribute ), $source, $alt, $width, $height );
        }
        foreach ( array( 'srcset'=>'Responsive image', 'data-srcset'=>'Lazy responsive image', 'data-lazy-srcset'=>'Lazy responsive image' ) as $attribute => $source ) {
            if ( $image->hasAttribute( $attribute ) ) $add_srcset( $image->getAttribute( $attribute ), $source, $alt, $width, $height );
        }
    }

    foreach ( $xpath->query( '//picture/source[@srcset] | //picture/source[@data-srcset]' ) as $source_node ) {
        $srcset = $source_node->getAttribute( 'srcset' );
        if ( ! $srcset ) $srcset = $source_node->getAttribute( 'data-srcset' );
        $add_srcset( $srcset, 'Picture source' );
    }

    foreach ( $xpath->query( '//meta[@content]' ) as $meta ) {
        $key = strtolower( trim( $meta->getAttribute( 'property' ) ? $meta->getAttribute( 'property' ) : $meta->getAttribute( 'name' ) ) );
        if ( ! in_array( $key, array( 'og:image', 'og:image:url', 'og:image:secure_url', 'twitter:image', 'twitter:image:src' ), true ) ) continue;
        $metadata++;
        $add_image( $meta->getAttribute( 'content' ), $key );
    }

    foreach ( $xpath->query( '//link[@href]' ) as $link ) {
        $rel = strtolower( trim( $link->getAttribute( 'rel' ) ) );
        $as = strtolower( trim( $link->getAttribute( 'as' ) ) );
        if ( false === strpos( $rel, 'icon' ) && false === strpos( $rel, 'image_src' ) && !( false !== strpos( $rel, 'preload' ) && 'image' === $as ) ) continue;
        $metadata++;
        $add_image( $link->getAttribute( 'href' ), 'Linked image/icon' );
        if ( $link->hasAttribute( 'imagesrcset' ) ) $add_srcset( $link->getAttribute( 'imagesrcset' ), 'Preloaded responsive image' );
    }

    foreach ( $xpath->query( '//*[@style]' ) as $styled ) {
        if ( ! preg_match_all( '~url\(\s*([\'\"]?)(.*?)\1\s*\)~i', $styled->getAttribute( 'style' ), $matches ) ) continue;
        foreach ( $matches[2] as $background ) {
            $backgrounds++;
            $add_image( $background, 'Inline background' );
        }
    }
    foreach ( $xpath->query( '//style' ) as $style_node ) {
        if ( ! preg_match_all( '~url\(\s*([\'\"]?)(.*?)\1\s*\)~i', (string) $style_node->textContent, $matches ) ) continue;
        foreach ( $matches[2] as $background ) {
            $backgrounds++;
            $add_image( $background, 'Embedded CSS background' );
        }
    }

    wp_send_json_success( array(
        'results' => array(
            array( 'label'=>'Fetched page', 'value'=>$url ),
            array( 'label'=>'Image elements', 'value'=>$image_nodes->length ),
            array( 'label'=>'Unique public images', 'value'=>count( $images ) ),
            array( 'label'=>'Responsive candidates', 'value'=>$responsive ),
            array( 'label'=>'Background references', 'value'=>$backgrounds ),
            array( 'label'=>'Social/icon references', 'value'=>$metadata ),
            array( 'label'=>'Unsafe/data references skipped', 'value'=>$skipped ),
        ),
        'images' => array_values( $images ),
        'notice' => count( $images ) >= 250 || $attempts >= 400 ? 'The safe result limit was reached. Images loaded only by JavaScript or external stylesheets may not appear.' : 'Public image references found in the supplied webpage HTML are shown. Reuse only images you own or have permission to use.',
    ) );
}
