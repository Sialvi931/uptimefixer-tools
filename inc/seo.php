<?php
/** Extra WebApplication schema and suite-specific SEO safeguards. */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'wp_head', 'ufxots_output_web_application_schema', 35 );
function ufxots_output_web_application_schema() {
    if ( ! ufxots_is_suite_tool_page() ) return;
    // The companion theme already emits the same WebApplication entity (and
    // cooperates with Yoast's graph), so avoid duplicate structured data.
    if ( function_exists( 'alltools_jsonld' ) || function_exists( 'alltools_yoast_is_active' ) ) return;
    $slug = ufxots_current_tool_slug();
    $tool = ufxots_tool_configs()[ $slug ];
    $schema = array(
        '@context'            => 'https://schema.org',
        '@type'               => 'WebApplication',
        '@id'                 => get_permalink() . '#web-application',
        'name'                => $tool['title'],
        'url'                 => get_permalink(),
        'description'         => $tool['desc'],
        'applicationCategory' => 'UtilityApplication',
        'operatingSystem'     => 'Any',
        'browserRequirements' => 'Requires a modern web browser with JavaScript enabled.',
        'isAccessibleForFree' => true,
        'offers'              => array( '@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'USD' ),
    );
    echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}

/** Never overwrite hand-written Yoast fields; fill only genuinely empty plugin pages. */
add_action( 'admin_init', 'ufxots_sync_empty_seo_fields', 80 );
function ufxots_sync_empty_seo_fields() {
    if ( ! current_user_can( 'manage_options' ) || ! function_exists( 'alltools_sync_yoast_meta_for_post' ) ) return;
    if ( UFXIE_VERSION === get_option( 'ufxots_seo_sync_version' ) ) return;
    $ids = get_posts( array(
        'post_type'      => 'alltool',
        'post_status'    => array( 'publish', 'draft', 'private' ),
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
        'meta_key'       => '_alltool_slug',
        'meta_value'     => array_keys( ufxots_tool_configs() ),
        'meta_compare'   => 'IN',
    ) );
    foreach ( $ids as $id ) alltools_sync_yoast_meta_for_post( $id );
    update_option( 'ufxots_seo_sync_version', UFXIE_VERSION, false );
}

/** Whether a slug belongs to this extension's 108-tool registry. */
function ufxots_is_plugin_tool_slug( $slug ) {
    return in_array( sanitize_key( $slug ), ufxots_all_plugin_slugs(), true );
}

/** Use the theme's editorial decision when available; otherwise respect holds. */
function ufxots_plugin_tool_index_ready( $post_id ) {
    $post_id = (int) $post_id;
    if ( ! $post_id || ! ufxots_is_plugin_tool_slug( get_post_meta( $post_id, '_alltool_slug', true ) ) ) return false;
    if ( function_exists( 'alltools_is_tool_index_ready' ) ) return alltools_is_tool_index_ready( $post_id );
    return 'no' !== get_post_meta( $post_id, '_alltools_index_ready', true );
}

function ufxots_current_plugin_tool_needs_review() {
    if ( ! function_exists( 'is_singular' ) || ! is_singular( 'alltool' ) ) return false;
    $post_id = get_queried_object_id();
    return ufxots_is_plugin_tool_slug( get_post_meta( $post_id, '_alltool_slug', true ) )
        && ! ufxots_plugin_tool_index_ready( $post_id );
}

/** Defence in depth: only extension pages explicitly held back remain noindex. */
add_filter( 'wp_robots', 'ufxots_noindex_unreviewed_tool', 1100 );
function ufxots_noindex_unreviewed_tool( $robots ) {
    if ( ufxots_current_plugin_tool_needs_review() ) {
        unset( $robots['index'], $robots['nofollow'] );
        $robots['noindex'] = true;
        $robots['follow'] = true;
    }
    return $robots;
}

add_filter( 'wpseo_robots_array', 'ufxots_yoast_noindex_unreviewed_tool', 1100 );
function ufxots_yoast_noindex_unreviewed_tool( $robots ) {
    if ( ufxots_current_plugin_tool_needs_review() ) {
        $robots['index'] = 'noindex';
        $robots['follow'] = 'follow';
    }
    return $robots;
}

/** IDs excluded from XML sitemaps because an editor explicitly held them back. */
function ufxots_nonindexable_tool_post_ids() {
    static $excluded = null;
    if ( null !== $excluded ) return $excluded;
    $ids = get_posts( array(
        'post_type'      => 'alltool',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
        'meta_key'       => '_alltool_slug',
        'meta_value'     => ufxots_all_plugin_slugs(),
        'meta_compare'   => 'IN',
    ) );
    $excluded = array_values( array_filter( array_map( 'intval', $ids ), function( $id ) {
        return ! ufxots_plugin_tool_index_ready( $id );
    } ) );
    return $excluded;
}

add_filter( 'wpseo_exclude_from_sitemap_by_post_ids', 'ufxots_exclude_unreviewed_from_yoast', 30 );
function ufxots_exclude_unreviewed_from_yoast( $ids ) {
    return array_values( array_unique( array_merge( (array) $ids, ufxots_nonindexable_tool_post_ids() ) ) );
}

add_filter( 'wp_sitemaps_posts_query_args', 'ufxots_exclude_unreviewed_from_core_sitemap', 30, 2 );
function ufxots_exclude_unreviewed_from_core_sitemap( $args, $post_type ) {
    if ( 'alltool' !== $post_type ) return $args;
    $args['post__not_in'] = array_values( array_unique( array_merge(
        isset( $args['post__not_in'] ) ? (array) $args['post__not_in'] : array(),
        ufxots_nonindexable_tool_post_ids()
    ) ) );
    return $args;
}

/** Cooperate with the theme's ad inventory gate on explicitly held-back pages. */
add_filter( 'alltools_ads_allowed_on_current_page', 'ufxots_gate_ads_on_unreviewed_tool', 30 );
function ufxots_gate_ads_on_unreviewed_tool( $allowed ) {
    return ufxots_current_plugin_tool_needs_review() ? false : $allowed;
}

/** Fallback for conventionally enqueued AdSense scripts on older theme builds. */
add_filter( 'script_loader_tag', 'ufxots_suppress_adsense_on_unreviewed_tool', 1100, 2 );
function ufxots_suppress_adsense_on_unreviewed_tool( $tag, $handle ) {
    if ( ! ufxots_current_plugin_tool_needs_review() ) return $tag;
    $is_adsense = false !== stripos( (string) $tag, 'pagead2.googlesyndication.com' )
        || false !== stripos( (string) $tag, 'adsbygoogle' )
        || false !== stripos( (string) $handle, 'adsense' );
    return $is_adsense ? '' : $tag;
}

/** Warn when the installed theme lacks the extension-aware review workflow. */
add_action( 'admin_notices', 'ufxots_theme_quality_compatibility_notice' );
function ufxots_theme_quality_compatibility_notice() {
    if ( ! current_user_can( 'manage_options' ) || ! defined( 'ALLTOOLS_VERSION' ) ) return;
    if ( version_compare( ALLTOOLS_VERSION, '3.9.1', '>=' ) ) return;
    echo '<div class="notice notice-warning"><p><strong>' . esc_html__( 'UptimeFixer Overall Tools Suite:', 'uptimefixer-image-extractor' ) . '</strong> '
        . esc_html__( 'Install Uptime Fixer theme 3.9.1 or newer for restored published-tool indexing, complete sitemap coverage and canonical recovery.', 'uptimefixer-image-extractor' ) . '</p></div>';
}
