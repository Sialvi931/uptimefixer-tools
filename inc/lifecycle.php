<?php
/** Setup, deletion and safe update behaviour. */

if ( ! defined( 'ABSPATH' ) ) exit;

register_activation_hook( UFXIE_FILE, 'ufxots_activate' );
function ufxots_activate() {
    // Activation never changes an existing post's status.
    update_option( 'ufxots_setup_required', '1', false );
}

/** Managed copy for newly created suite pages. */
function ufxots_generated_post_content( $tool ) {
    return '<h2>Purpose</h2><p>' . esc_html( $tool['desc'] ) . '</p>'
        . '<h2>Input and verification</h2><p>' . esc_html( ufxots_editorial_input_tip( $tool ) ) . '</p>'
        . '<h2>Known limitation</h2><p>' . esc_html( $tool['limitation'] ) . '</p>'
        . '<h2>Privacy</h2><p>' . esc_html( $tool['privacy'] ) . '</p>';
}

/** Exact version-2.0 generated copy, used only to avoid overwriting edits. */
function ufxots_generated_post_content_v200( $tool ) {
    return '<h2>What this tool does</h2><p>' . esc_html( $tool['desc'] ) . '</p><h2>Practical guidance</h2><p>' . esc_html( $tool['long'] ) . '</p><h2>Privacy and limitations</h2><p>' . esc_html( $tool['privacy'] . ' ' . $tool['limitation'] ) . '</p>';
}

add_action( 'admin_init', 'ufxots_ensure_tool_pages', 28 );
function ufxots_ensure_tool_pages() {
    if ( ! current_user_can( 'manage_options' ) || ! post_type_exists( 'alltool' ) || ! function_exists( 'alltools_all' ) ) return;
    if ( '1' !== get_option( 'ufxots_setup_required' ) && UFXIE_VERSION === get_option( 'ufxots_setup_version' ) ) return;

    $configs  = ufxots_tool_configs();
    $disabled = ufxots_disabled_tools();
    $ids = get_posts( array(
        'post_type'      => 'alltool',
        'post_status'    => array( 'publish', 'future', 'draft', 'pending', 'private', 'trash', 'auto-draft' ),
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
        'meta_key'       => '_alltool_slug',
    ) );
    $existing = array();
    foreach ( $ids as $id ) {
        $existing[ sanitize_key( (string) get_post_meta( $id, '_alltool_slug', true ) ) ] = (int) $id;
    }

    $created = 0;
    $setup_failed = false;
    foreach ( $configs as $slug => $tool ) {
        if ( in_array( $slug, $disabled, true ) || isset( $existing[ $slug ] ) ) continue;
        $post_id = wp_insert_post( array(
            'post_type'    => 'alltool',
            'post_status'  => 'publish',
            'post_name'    => $slug,
            'post_title'   => $tool['title'],
            'post_excerpt' => $tool['desc'],
            'post_content' => ufxots_generated_post_content( $tool ),
        ), true );
        if ( is_wp_error( $post_id ) ) {
            $setup_failed = true;
            update_option( 'ufxots_setup_error', sanitize_text_field( $post_id->get_error_message() ), false );
            continue;
        }
        update_post_meta( $post_id, '_alltool_slug', $slug );
        update_post_meta( $post_id, '_ufxots_managed', UFXIE_VERSION );
        if ( function_exists( 'alltools_sync_yoast_meta_for_post' ) ) alltools_sync_yoast_meta_for_post( $post_id );
        $created++;
    }

    if ( $setup_failed ) return; // Retain the error and retry missing posts next time.
    delete_option( 'ufxots_setup_error' );
    delete_option( 'ufxots_setup_required' );
    update_option( 'ufxots_setup_version', UFXIE_VERSION, false );
    if ( $created ) flush_rewrite_rules( false );
}

function ufxots_disable_slug( $slug ) {
    $slug = sanitize_key( $slug );
    if ( ! in_array( $slug, ufxots_all_plugin_slugs(), true ) ) return;
    $disabled = ufxots_disabled_tools();
    if ( ! in_array( $slug, $disabled, true ) ) {
        $disabled[] = $slug;
        update_option( 'ufxots_disabled_tools', array_values( array_unique( $disabled ) ), false );
    }
}

function ufxots_enable_slug( $slug ) {
    $slug = sanitize_key( $slug );
    $disabled = array_values( array_diff( ufxots_disabled_tools(), array( $slug ) ) );
    update_option( 'ufxots_disabled_tools', $disabled, false );
    update_option( 'ufxots_setup_required', '1', false );
}

add_action( 'wp_trash_post', 'ufxots_track_trashed_tool', 10, 1 );
add_action( 'before_delete_post', 'ufxots_track_trashed_tool', 10, 1 );
function ufxots_track_trashed_tool( $post_id ) {
    if ( 'alltool' !== get_post_type( $post_id ) ) return;
    ufxots_disable_slug( (string) get_post_meta( $post_id, '_alltool_slug', true ) );
}

add_action( 'untrashed_post', 'ufxots_track_restored_tool', 10, 1 );
function ufxots_track_restored_tool( $post_id ) {
    if ( 'alltool' !== get_post_type( $post_id ) ) return;
    ufxots_enable_slug( (string) get_post_meta( $post_id, '_alltool_slug', true ) );
}

add_action( 'admin_notices', 'ufxots_setup_notice' );
function ufxots_setup_notice() {
    if ( ! current_user_can( 'manage_options' ) ) return;
    $error = get_option( 'ufxots_setup_error' );
    if ( $error ) echo '<div class="notice notice-error"><p><strong>UptimeFixer Overall Tools Suite:</strong> ' . esc_html( $error ) . '</p></div>';
}

/** Update only byte-for-byte legacy plugin copy; never rewrite edited content. */
// Legacy copy migration is intentionally not hooked: preserve existing content on update.
function ufxots_migrate_production_copy() {
    if ( ! current_user_can( 'manage_options' ) || UFXIE_VERSION === get_option( 'ufxots_copy_migration_version' ) ) return;
    $ids = get_posts( array(
        'post_type'      => 'alltool',
        'post_status'    => array( 'publish', 'draft', 'private', 'trash' ),
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
        'meta_key'       => '_alltool_slug',
        'meta_value'     => ufxots_all_plugin_slugs(),
        'meta_compare'   => 'IN',
    ) );
    $configs = ufxots_tool_configs();
    foreach ( $ids as $id ) {
        $post = get_post( $id );
        if ( ! $post ) continue;
        $slug = sanitize_key( (string) get_post_meta( $id, '_alltool_slug', true ) );
        if ( isset( $configs[ $slug ] ) && get_post_meta( $id, '_ufxots_managed', true )
            && trim( (string) $post->post_content ) === trim( ufxots_generated_post_content_v200( $configs[ $slug ] ) ) ) {
            $content = ufxots_generated_post_content( $configs[ $slug ] );
            wp_update_post( array( 'ID' => $id, 'post_content' => $content ) );
            update_post_meta( $id, '_ufxots_managed', UFXIE_VERSION );
        }
    }
    update_option( 'ufxots_copy_migration_version', UFXIE_VERSION, false );
}
