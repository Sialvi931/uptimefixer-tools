<?php
/** Hardened public-URL operations shared by the suite tools. */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'wp_ajax_ufxots_remote', 'ufxots_ajax_remote' );
add_action( 'wp_ajax_nopriv_ufxots_remote', 'ufxots_ajax_remote' );

function ufxots_ajax_remote() {
    $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
    if ( ! $nonce || ! wp_verify_nonce( $nonce, 'ufxots_run' ) ) wp_send_json_error( array( 'message' => 'The security token is invalid or expired. Refresh the page and try again.' ), 403 );
    if ( function_exists( 'ufxie_rate_limit' ) ) ufxie_rate_limit( 'suite', 12 );

    $task = isset( $_POST['task'] ) ? sanitize_key( wp_unslash( $_POST['task'] ) ) : '';
    $allowed = array(
        'webpage-article-extractor', 'website-font-finder', 'website-asset-extractor',
        'website-color-palette-extractor', 'rss-feed-finder-validator',
        'website-contact-social-extractor', 'website-charset-language-checker',
        'public-source-code-viewer', 'sitemap-comparison-tool', 'http-header-comparison',
        'webpage-content-comparison', 'security-txt-generator-checker',
        'referrer-policy-generator-checker', 'html-table-data-extractor',
        'website-migration-url-validator', 'sri-hash-generator',
    );
    if ( ! in_array( $task, $allowed, true ) ) wp_send_json_error( array( 'message' => 'This remote operation is not allowed.' ), 400 );

    $primary   = isset( $_POST['primary'] ) ? sanitize_textarea_field( wp_unslash( $_POST['primary'] ) ) : '';
    $secondary = isset( $_POST['secondary'] ) ? sanitize_textarea_field( wp_unslash( $_POST['secondary'] ) ) : '';
    $options   = isset( $_POST['options'] ) ? json_decode( wp_unslash( $_POST['options'] ), true ) : array();
    if ( ! is_array( $options ) ) $options = array();

    try {
        switch ( $task ) {
            case 'webpage-article-extractor':
                $result = ufxots_remote_article( $primary );
                break;
            case 'website-font-finder':
                $result = ufxots_remote_fonts( $primary );
                break;
            case 'website-asset-extractor':
                $result = ufxots_remote_assets( $primary );
                break;
            case 'website-color-palette-extractor':
                $result = ufxots_remote_colours( $primary );
                break;
            case 'rss-feed-finder-validator':
                $result = ufxots_remote_feeds( $primary );
                break;
            case 'website-contact-social-extractor':
                $result = ufxots_remote_contacts( $primary );
                break;
            case 'website-charset-language-checker':
                $result = ufxots_remote_language( $primary );
                break;
            case 'public-source-code-viewer':
                $result = ufxots_remote_source( $primary );
                break;
            case 'sitemap-comparison-tool':
                $result = ufxots_remote_sitemap_compare( $primary, $secondary );
                break;
            case 'http-header-comparison':
                $result = ufxots_remote_header_compare( $primary, $secondary );
                break;
            case 'webpage-content-comparison':
                $result = ufxots_remote_content_compare( $primary, $secondary );
                break;
            case 'security-txt-generator-checker':
                $result = ufxots_remote_security_txt( $primary );
                break;
            case 'referrer-policy-generator-checker':
                $result = ufxots_remote_referrer( $primary );
                break;
            case 'html-table-data-extractor':
                $result = ufxots_remote_tables( $primary );
                break;
            case 'website-migration-url-validator':
                $result = ufxots_remote_migration( $primary );
                break;
            case 'sri-hash-generator':
                $algorithm = isset( $options['algorithm'] ) ? sanitize_text_field( $options['algorithm'] ) : 'SHA-384';
                $result = ufxots_remote_sri( $primary, $algorithm );
                break;
            default:
                throw new Exception( 'Unsupported operation.' );
        }
        wp_send_json_success( $result );
    } catch ( Exception $error ) {
        wp_send_json_error( array( 'message' => sanitize_text_field( $error->getMessage() ) ), 422 );
    }
}

function ufxots_remote_get( $raw_url, $limit = 2097152, $redirections = 4 ) {
    $url = function_exists( 'ufxie_clean_url' ) ? ufxie_clean_url( $raw_url ) : '';
    if ( ! $url ) throw new Exception( 'Enter a valid public HTTP or HTTPS URL.' );
    $args = array(
        'timeout'             => 18,
        'redirection'         => min( 5, max( 0, (int) $redirections ) ),
        'reject_unsafe_urls'  => true,
        'sslverify'           => true,
        'limit_response_size' => min( 5242880, max( 1024, (int) $limit ) ),
        'user-agent'          => 'UptimeFixer Overall Tools/' . UFXIE_VERSION . ' (+https://uptimefixer.com/)',
    );
    $response = function_exists( 'ufx_safe_remote_get' ) ? ufx_safe_remote_get( $url, $args ) : wp_safe_remote_get( $url, $args );
    if ( is_wp_error( $response ) ) throw new Exception( $response->get_error_message() );
    return array(
        'url'     => $url,
        'code'    => (int) wp_remote_retrieve_response_code( $response ),
        'body'    => (string) wp_remote_retrieve_body( $response ),
        'headers' => wp_remote_retrieve_headers( $response ),
    );
}

function ufxots_dom( $html ) {
    if ( ! class_exists( 'DOMDocument' ) || ! class_exists( 'DOMXPath' ) ) throw new Exception( 'The server needs the PHP DOM extension for this tool.' );
    $dom = new DOMDocument();
    $previous = libxml_use_internal_errors( true );
    $loaded = $dom->loadHTML( '<?xml encoding="utf-8" ?>' . (string) $html, LIBXML_NONET | LIBXML_NOWARNING | LIBXML_NOERROR );
    libxml_clear_errors();
    libxml_use_internal_errors( $previous );
    if ( ! $loaded ) throw new Exception( 'The fetched response could not be parsed as HTML.' );
    return array( $dom, new DOMXPath( $dom ) );
}

function ufxots_node_text( $node ) {
    return trim( preg_replace( '/\s+/u', ' ', (string) $node->textContent ) );
}

function ufxots_fetch_stylesheets( $base, $xpath, $max = 6 ) {
    $css = array();
    foreach ( $xpath->query( '//style' ) as $style ) $css[] = (string) $style->textContent;
    $count = 0;
    foreach ( $xpath->query( '//link[@href]' ) as $link ) {
        if ( $count >= $max ) break;
        $rel = strtolower( $link->getAttribute( 'rel' ) );
        if ( false === strpos( $rel, 'stylesheet' ) ) continue;
        $url = ufxie_absolute_url( $base, $link->getAttribute( 'href' ) );
        if ( ! $url ) continue;
        try {
            $response = ufxots_remote_get( $url, 524288, 2 );
            if ( $response['code'] >= 200 && $response['code'] < 400 ) {
                $css[] = $response['body'];
                $count++;
            }
        } catch ( Exception $ignore ) {
            continue;
        }
    }
    return implode( "\n", $css );
}

function ufxots_remote_article( $url ) {
    $response = ufxots_remote_get( $url );
    if ( $response['code'] < 200 || $response['code'] >= 400 ) throw new Exception( 'The webpage returned HTTP ' . $response['code'] . '.' );
    list( $dom, $xpath ) = ufxots_dom( $response['body'] );
    foreach ( $xpath->query( '//script|//style|//noscript|//nav|//footer|//header|//aside|//form|//svg' ) as $node ) {
        if ( $node->parentNode ) $node->parentNode->removeChild( $node );
    }
    $title = '';
    $title_nodes = $xpath->query( '//meta[@property="og:title"]/@content | //title' );
    if ( $title_nodes->length ) $title = ufxots_node_text( $title_nodes->item( 0 ) );
    $candidates = $xpath->query( '//article | //main | //*[@role="main"]' );
    $root = $candidates->length ? $candidates->item( 0 ) : $dom->getElementsByTagName( 'body' )->item( 0 );
    if ( ! $root ) throw new Exception( 'No readable body was found.' );
    $parts = array();
    $headings = array();
    foreach ( $xpath->query( './/h1|.//h2|.//h3|.//p|.//li|.//blockquote', $root ) as $node ) {
        $text = ufxots_node_text( $node );
        if ( strlen( $text ) < 2 ) continue;
        if ( preg_match( '/^h[1-3]$/i', $node->nodeName ) ) $headings[] = $text;
        $parts[] = $text;
        if ( count( $parts ) >= 600 ) break;
    }
    $text = trim( implode( "\n\n", array_values( array_unique( $parts ) ) ) );
    $words = str_word_count( wp_strip_all_tags( $text ) );
    return array(
        'summary' => array( 'Page title' => $title ?: 'Not declared', 'Words' => $words, 'Headings' => count( $headings ), 'Estimated reading time' => max( 1, (int) ceil( $words / 220 ) ) . ' min' ),
        'rows' => array_map( function( $heading ) { return array( 'Heading', $heading ); }, array_slice( $headings, 0, 60 ) ),
        'text' => $text,
        'downloadName' => 'extracted-article.txt',
        'notice' => 'Readable source content was extracted. Compare the result with the live page before reuse.',
    );
}

function ufxots_remote_fonts( $url ) {
    $response = ufxots_remote_get( $url );
    list( $dom, $xpath ) = ufxots_dom( $response['body'] );
    $css = $response['body'] . "\n" . ufxots_fetch_stylesheets( $response['url'], $xpath );
    $families = array();
    if ( preg_match_all( '/font-family\s*:\s*([^;}{]+)/i', $css, $matches ) ) {
        foreach ( $matches[1] as $value ) foreach ( str_getcsv( $value ) as $family ) {
            $family = trim( $family, " \t\n\r\0\x0B'\"" );
            if ( $family && strlen( $family ) < 100 ) $families[ strtolower( $family ) ] = $family;
        }
    }
    $files = array();
    if ( preg_match_all( '~url\([\'\"]?([^\)\'\"]+\.(?:woff2?|ttf|otf)(?:\?[^\)\'\"]*)?)[\'\"]?\)~i', $css, $matches ) ) {
        foreach ( $matches[1] as $value ) {
            $absolute = ufxie_absolute_url( $response['url'], $value );
            if ( $absolute ) $files[ $absolute ] = $absolute;
        }
    }
    $rows = array();
    foreach ( array_values( $families ) as $value ) $rows[] = array( 'Font family', $value );
    foreach ( array_values( $files ) as $value ) $rows[] = array( 'Font file', $value );
    return array( 'summary' => array( 'Declared families' => count( $families ), 'Font files' => count( $files ) ), 'rows' => array_slice( $rows, 0, 250 ), 'text' => implode( "\n", array_values( $families ) ), 'downloadName' => 'website-fonts.txt', 'notice' => 'Font declarations found in page source and a bounded stylesheet sample.' );
}

function ufxots_remote_assets( $url ) {
    $response = ufxots_remote_get( $url );
    list( $dom, $xpath ) = ufxots_dom( $response['body'] );
    $queries = array(
        'Stylesheet' => array( '//link[contains(translate(@rel,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"stylesheet")]/@href', 'value' ),
        'Script'     => array( '//script[@src]/@src', 'value' ),
        'Image'      => array( '//img[@src]/@src | //source[@src]/@src', 'value' ),
        'Media'      => array( '//video[@src]/@src | //audio[@src]/@src | //track[@src]/@src', 'value' ),
        'Download'   => array( '//a[@href]/@href', 'value' ),
    );
    $assets = array();
    foreach ( $queries as $type => $spec ) {
        foreach ( $xpath->query( $spec[0] ) as $node ) {
            $absolute = ufxie_absolute_url( $response['url'], $node->nodeValue );
            if ( ! $absolute ) continue;
            if ( 'Download' === $type && ! preg_match( '/\.(?:pdf|docx?|xlsx?|pptx?|zip|csv|txt|xml|json)(?:[?#]|$)/i', $absolute ) ) continue;
            $assets[ $type . '|' . $absolute ] = array( $type, $absolute );
            if ( count( $assets ) >= 500 ) break 2;
        }
    }
    $counts = array();
    foreach ( $assets as $asset ) $counts[ $asset[0] ] = isset( $counts[ $asset[0] ] ) ? $counts[ $asset[0] ] + 1 : 1;
    return array( 'summary' => array_merge( array( 'Total unique assets' => count( $assets ) ), $counts ), 'rows' => array_values( $assets ), 'text' => implode( "\n", array_map( function( $row ) { return $row[0] . "\t" . $row[1]; }, $assets ) ), 'downloadName' => 'website-assets.tsv', 'notice' => 'Public asset references were collected from the fetched HTML.' );
}

function ufxots_remote_colours( $url ) {
    $response = ufxots_remote_get( $url );
    list( $dom, $xpath ) = ufxots_dom( $response['body'] );
    $css = $response['body'] . "\n" . ufxots_fetch_stylesheets( $response['url'], $xpath );
    preg_match_all( '/#[0-9a-f]{3,8}\b|rgba?\([^\)]{3,80}\)|hsla?\([^\)]{3,80}\)/i', $css, $matches );
    $counts = array();
    foreach ( $matches[0] as $colour ) {
        $key = strtolower( preg_replace( '/\s+/', '', $colour ) );
        if ( strlen( $key ) > 60 ) continue;
        $counts[ $key ] = isset( $counts[ $key ] ) ? $counts[ $key ] + 1 : 1;
    }
    arsort( $counts );
    $rows = array();
    foreach ( array_slice( $counts, 0, 80, true ) as $colour => $count ) $rows[] = array( $colour, $count . ' declarations' );
    return array( 'summary' => array( 'Unique colour values' => count( $counts ), 'Declarations scanned' => array_sum( $counts ) ), 'rows' => $rows, 'palette' => array_keys( array_slice( $counts, 0, 24, true ) ), 'text' => implode( "\n", array_keys( $counts ) ), 'downloadName' => 'website-colours.txt', 'notice' => 'Colours were extracted from source CSS declarations, not pixels in images.' );
}

function ufxots_remote_feeds( $url ) {
    $response = ufxots_remote_get( $url );
    list( $dom, $xpath ) = ufxots_dom( $response['body'] );
    $feeds = array();
    foreach ( $xpath->query( '//link[@href]' ) as $link ) {
        $type = strtolower( $link->getAttribute( 'type' ) );
        $rel  = strtolower( $link->getAttribute( 'rel' ) );
        if ( false === strpos( $rel, 'alternate' ) || ! preg_match( '/(?:rss|atom|xml)/', $type ) ) continue;
        $feed = ufxie_absolute_url( $response['url'], $link->getAttribute( 'href' ) );
        if ( $feed ) $feeds[ $feed ] = array( $type ?: 'Feed', $feed );
    }
    $base = wp_parse_url( $response['url'] );
    if ( isset( $base['scheme'], $base['host'] ) ) {
        foreach ( array( '/feed/', '/rss.xml', '/feed.xml', '/atom.xml' ) as $path ) {
            if ( count( $feeds ) >= 12 ) break;
            $candidate = $base['scheme'] . '://' . $base['host'] . $path;
            try {
                $check = ufxots_remote_get( $candidate, 262144, 2 );
                if ( $check['code'] >= 200 && $check['code'] < 400 && preg_match( '/<(?:rss|feed)\b/i', $check['body'] ) ) $feeds[ $candidate ] = array( 'Discovered feed', $candidate );
            } catch ( Exception $ignore ) {}
        }
    }
    return array( 'summary' => array( 'Feeds found' => count( $feeds ) ), 'rows' => array_values( $feeds ), 'text' => implode( "\n", array_keys( $feeds ) ), 'downloadName' => 'feeds.txt', 'notice' => $feeds ? 'Linked and common public feed locations were checked.' : 'No public RSS or Atom feed was found in the bounded check.' );
}

function ufxots_remote_contacts( $url ) {
    $response = ufxots_remote_get( $url );
    list( $dom, $xpath ) = ufxots_dom( $response['body'] );
    $rows = array();
    $social = array( 'facebook.com', 'instagram.com', 'linkedin.com', 'youtube.com', 'youtu.be', 'x.com', 'twitter.com', 'pinterest.com', 'tiktok.com', 'github.com', 'threads.net' );
    foreach ( $xpath->query( '//a[@href]' ) as $link ) {
        $href = trim( html_entity_decode( $link->getAttribute( 'href' ), ENT_QUOTES ) );
        if ( 0 === stripos( $href, 'mailto:' ) ) $rows[ 'Email|' . strtolower( $href ) ] = array( 'Email', preg_replace( '/^mailto:/i', '', preg_replace( '/\?.*$/', '', $href ) ) );
        elseif ( 0 === stripos( $href, 'tel:' ) ) $rows[ 'Telephone|' . $href ] = array( 'Telephone', preg_replace( '/^tel:/i', '', $href ) );
        else {
            $absolute = ufxie_absolute_url( $response['url'], $href );
            $host = $absolute ? strtolower( (string) wp_parse_url( $absolute, PHP_URL_HOST ) ) : '';
            foreach ( $social as $domain ) if ( $host === $domain || substr( $host, -strlen( '.' . $domain ) ) === '.' . $domain ) {
                $rows[ 'Social|' . $absolute ] = array( 'Social profile', $absolute );
                break;
            }
        }
        if ( count( $rows ) >= 200 ) break;
    }
    $emails = 0; $phones = 0; $profiles = 0;
    foreach ( $rows as $row ) { if ( 'Email' === $row[0] ) $emails++; elseif ( 'Telephone' === $row[0] ) $phones++; else $profiles++; }
    return array( 'summary' => array( 'Emails' => $emails, 'Telephone links' => $phones, 'Social profiles' => $profiles ), 'rows' => array_values( $rows ), 'text' => implode( "\n", array_map( function( $row ) { return $row[0] . ': ' . $row[1]; }, $rows ) ), 'downloadName' => 'public-contact-links.txt', 'notice' => 'Only public links declared in the supplied webpage were returned.' );
}

function ufxots_remote_language( $url ) {
    $response = ufxots_remote_get( $url );
    list( $dom, $xpath ) = ufxots_dom( $response['body'] );
    $html = $dom->getElementsByTagName( 'html' )->item( 0 );
    $lang = $html ? $html->getAttribute( 'lang' ) : '';
    $dir  = $html ? $html->getAttribute( 'dir' ) : '';
    $meta_charset = '';
    $nodes = $xpath->query( '//meta[@charset]/@charset | //meta[translate(@http-equiv,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz")="content-type"]/@content' );
    if ( $nodes->length ) $meta_charset = $nodes->item( 0 )->nodeValue;
    $content_type = isset( $response['headers']['content-type'] ) ? (string) $response['headers']['content-type'] : '';
    $content_language = isset( $response['headers']['content-language'] ) ? (string) $response['headers']['content-language'] : '';
    return array( 'summary' => array( 'HTML language' => $lang ?: 'Not declared', 'Text direction' => $dir ?: 'Not declared', 'Meta charset' => $meta_charset ?: 'Not declared', 'Content-Type header' => $content_type ?: 'Not declared', 'Content-Language header' => $content_language ?: 'Not declared' ), 'rows' => array(), 'text' => "HTML language: $lang\nText direction: $dir\nMeta charset: $meta_charset\nContent-Type: $content_type\nContent-Language: $content_language", 'downloadName' => 'language-charset-report.txt', 'notice' => 'Declarations were read from the current public response.' );
}

function ufxots_remote_source( $url ) {
    $response = ufxots_remote_get( $url, 1048576 );
    $body = substr( $response['body'], 0, 500000 );
    $lines = explode( "\n", str_replace( array( "\r\n", "\r" ), "\n", $body ) );
    $numbered = array();
    foreach ( array_slice( $lines, 0, 10000 ) as $index => $line ) $numbered[] = str_pad( (string) ( $index + 1 ), 5, ' ', STR_PAD_LEFT ) . '  ' . $line;
    return array( 'summary' => array( 'HTTP status' => $response['code'], 'Fetched bytes' => strlen( $response['body'] ), 'Displayed lines' => count( $numbered ) ), 'rows' => array(), 'text' => implode( "\n", $numbered ), 'downloadName' => 'webpage-source.html.txt', 'notice' => strlen( $response['body'] ) > strlen( $body ) ? 'The public source was truncated to the safe display limit.' : 'Public response source loaded with line numbers.' );
}

function ufxots_sitemap_urls( $url ) {
    $response = ufxots_remote_get( $url, 5242880 );
    if ( ! preg_match( '/<(?:urlset|sitemapindex)\b/i', $response['body'] ) ) throw new Exception( 'The supplied URL did not return a recognizable XML sitemap.' );
    preg_match_all( '~<loc>\s*(.*?)\s*</loc>~is', $response['body'], $matches );
    $urls = array();
    foreach ( array_slice( $matches[1], 0, 5000 ) as $entry ) {
        $entry = html_entity_decode( trim( wp_strip_all_tags( $entry ) ), ENT_QUOTES );
        if ( filter_var( $entry, FILTER_VALIDATE_URL ) ) $urls[ $entry ] = true;
    }
    return array_keys( $urls );
}

function ufxots_remote_sitemap_compare( $first, $second ) {
    $a = ufxots_sitemap_urls( $first );
    $b = ufxots_sitemap_urls( $second );
    $added = array_values( array_diff( $b, $a ) );
    $removed = array_values( array_diff( $a, $b ) );
    $same = array_values( array_intersect( $a, $b ) );
    $rows = array();
    foreach ( array_slice( $added, 0, 1000 ) as $url ) $rows[] = array( 'Added', $url );
    foreach ( array_slice( $removed, 0, 1000 ) as $url ) $rows[] = array( 'Removed', $url );
    return array( 'summary' => array( 'First sitemap URLs' => count( $a ), 'Second sitemap URLs' => count( $b ), 'Added' => count( $added ), 'Removed' => count( $removed ), 'Unchanged' => count( $same ) ), 'rows' => $rows, 'text' => implode( "\n", array_merge( array_map( function( $x ) { return 'ADDED\t' . $x; }, $added ), array_map( function( $x ) { return 'REMOVED\t' . $x; }, $removed ) ) ), 'downloadName' => 'sitemap-comparison.tsv', 'notice' => 'Up to 5,000 locations per submitted sitemap were compared.' );
}

function ufxots_header_values( $response ) {
    $wanted = array( 'content-type', 'content-length', 'cache-control', 'etag', 'last-modified', 'content-encoding', 'vary', 'server', 'location', 'strict-transport-security', 'content-security-policy', 'permissions-policy', 'referrer-policy', 'x-content-type-options', 'x-frame-options', 'cross-origin-resource-policy' );
    $values = array( 'HTTP status' => $response['code'] );
    foreach ( $wanted as $name ) if ( isset( $response['headers'][ $name ] ) ) $values[ $name ] = (string) $response['headers'][ $name ];
    return $values;
}

function ufxots_remote_header_compare( $first, $second ) {
    $a = ufxots_header_values( ufxots_remote_get( $first, 32768 ) );
    $b = ufxots_header_values( ufxots_remote_get( $second, 32768 ) );
    $keys = array_values( array_unique( array_merge( array_keys( $a ), array_keys( $b ) ) ) );
    $rows = array();
    foreach ( $keys as $key ) $rows[] = array( $key, isset( $a[ $key ] ) ? $a[ $key ] : '—', isset( $b[ $key ] ) ? $b[ $key ] : '—' );
    return array( 'summary' => array( 'Headers compared' => count( $keys ) ), 'columns' => array( 'Header', 'First URL', 'Second URL' ), 'rows' => $rows, 'text' => implode( "\n", array_map( function( $row ) { return implode( "\t", $row ); }, $rows ) ), 'downloadName' => 'http-header-comparison.tsv', 'notice' => 'Important response headers from two point-in-time requests were compared.' );
}

function ufxots_readable_text( $url ) {
    $response = ufxots_remote_get( $url );
    list( $dom, $xpath ) = ufxots_dom( $response['body'] );
    foreach ( $xpath->query( '//script|//style|//noscript|//svg|//nav|//footer|//header|//form' ) as $node ) if ( $node->parentNode ) $node->parentNode->removeChild( $node );
    $body = $dom->getElementsByTagName( 'body' )->item( 0 );
    return $body ? trim( preg_replace( '/\s+/u', ' ', $body->textContent ) ) : '';
}

function ufxots_remote_content_compare( $first, $second ) {
    $a = ufxots_readable_text( $first );
    $b = ufxots_readable_text( $second );
    $a_lower = function_exists( 'mb_strtolower' ) ? mb_strtolower( $a ) : strtolower( $a );
    $b_lower = function_exists( 'mb_strtolower' ) ? mb_strtolower( $b ) : strtolower( $b );
    $aw = array_values( array_unique( preg_split( '/[^\pL\pN]+/u', $a_lower ) ) );
    $bw = array_values( array_unique( preg_split( '/[^\pL\pN]+/u', $b_lower ) ) );
    $intersection = count( array_intersect( $aw, $bw ) );
    $union = max( 1, count( array_unique( array_merge( $aw, $bw ) ) ) );
    $added = array_slice( array_values( array_diff( $bw, $aw ) ), 0, 300 );
    $removed = array_slice( array_values( array_diff( $aw, $bw ) ), 0, 300 );
    $rows = array();
    foreach ( $added as $word ) if ( $word ) $rows[] = array( 'Only in second', $word );
    foreach ( $removed as $word ) if ( $word ) $rows[] = array( 'Only in first', $word );
    $a_length = function_exists( 'mb_strlen' ) ? mb_strlen( $a ) : strlen( $a );
    $b_length = function_exists( 'mb_strlen' ) ? mb_strlen( $b ) : strlen( $b );
    return array( 'summary' => array( 'First page characters' => $a_length, 'Second page characters' => $b_length, 'Vocabulary similarity' => round( 100 * $intersection / $union, 1 ) . '%' ), 'rows' => $rows, 'text' => "FIRST PAGE\n$a\n\nSECOND PAGE\n$b", 'downloadName' => 'webpage-content-comparison.txt', 'notice' => 'Readable vocabulary and extracted text were compared; layout and scripts were not.' );
}

function ufxots_remote_security_txt( $url ) {
    $clean = ufxie_clean_url( $url );
    if ( ! $clean ) throw new Exception( 'Enter a valid public website URL.' );
    $parts = wp_parse_url( $clean );
    $target = $parts['scheme'] . '://' . $parts['host'] . ( isset( $parts['port'] ) ? ':' . (int) $parts['port'] : '' ) . '/.well-known/security.txt';
    $response = ufxots_remote_get( $target, 262144, 2 );
    $rows = array();
    foreach ( preg_split( '/\r\n|\r|\n/', $response['body'] ) as $line ) {
        if ( preg_match( '/^([A-Za-z-]+):\s*(.+)$/', trim( $line ), $match ) ) $rows[] = array( $match[1], $match[2] );
    }
    $fields = array_map( function( $row ) { return strtolower( $row[0] ); }, $rows );
    $issues = array();
    if ( ! in_array( 'contact', $fields, true ) ) $issues[] = 'Missing Contact field';
    if ( ! in_array( 'expires', $fields, true ) ) $issues[] = 'Missing Expires field';
    return array( 'summary' => array( 'HTTP status' => $response['code'], 'Parsed fields' => count( $rows ), 'Basic issues' => count( $issues ) ), 'rows' => $rows, 'text' => $response['body'], 'downloadName' => 'security-txt-check.txt', 'notice' => $issues ? implode( '; ', $issues ) . '.' : 'Contact and Expires fields were found. Verify every published value manually.' );
}

function ufxots_remote_referrer( $url ) {
    $response = ufxots_remote_get( $url, 524288 );
    list( $dom, $xpath ) = ufxots_dom( $response['body'] );
    $header = isset( $response['headers']['referrer-policy'] ) ? (string) $response['headers']['referrer-policy'] : '';
    $meta = '';
    $nodes = $xpath->query( '//meta[translate(@name,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz")="referrer"]/@content' );
    if ( $nodes->length ) $meta = trim( $nodes->item( 0 )->nodeValue );
    return array( 'summary' => array( 'HTTP status' => $response['code'], 'Header policy' => $header ?: 'Not declared', 'Meta policy' => $meta ?: 'Not declared', 'Effective declaration found' => ( $header || $meta ) ? 'Yes' : 'No' ), 'rows' => array(), 'text' => "Referrer-Policy header: $header\nMeta referrer: $meta", 'downloadName' => 'referrer-policy-report.txt', 'notice' => 'The live header and HTML meta declaration were inspected.' );
}

function ufxots_remote_tables( $url ) {
    $response = ufxots_remote_get( $url );
    list( $dom, $xpath ) = ufxots_dom( $response['body'] );
    $tables = array();
    foreach ( $xpath->query( '//table' ) as $table_index => $table ) {
        if ( $table_index >= 10 ) break;
        $rows = array();
        foreach ( $xpath->query( './/tr', $table ) as $row ) {
            $cells = array();
            foreach ( $xpath->query( './th|./td', $row ) as $cell ) $cells[] = ufxots_node_text( $cell );
            if ( $cells ) $rows[] = $cells;
            if ( count( $rows ) >= 200 ) break;
        }
        if ( $rows ) $tables[] = $rows;
    }
    $flat = array();
    foreach ( $tables as $index => $table ) foreach ( $table as $row ) $flat[] = array_merge( array( 'Table ' . ( $index + 1 ) ), $row );
    $csv = '';
    foreach ( $flat as $row ) $csv .= implode( ',', array_map( function( $value ) { return '"' . str_replace( '"', '""', $value ) . '"'; }, $row ) ) . "\n";
    return array( 'summary' => array( 'Tables found' => count( $tables ), 'Rows extracted' => count( $flat ) ), 'rows' => array_slice( $flat, 0, 500 ), 'text' => $csv, 'json' => $tables, 'downloadName' => 'extracted-tables.csv', 'notice' => 'Accessible HTML table cells were extracted from the public source.' );
}

function ufxots_status_only( $raw ) {
    $url = ufxie_clean_url( $raw );
    if ( ! $url ) return array( 'url' => $raw, 'status' => 'Invalid', 'final' => '', 'location' => '' );
    $response = ufxots_remote_get( $url, 2048, 0 );
    $location = isset( $response['headers']['location'] ) ? (string) $response['headers']['location'] : '';
    return array( 'url' => $url, 'status' => $response['code'], 'final' => $url, 'location' => $location );
}

function ufxots_remote_migration( $text ) {
    $pairs = array();
    foreach ( preg_split( '/\r\n|\r|\n/', $text ) as $line ) {
        $line = trim( $line );
        if ( ! $line ) continue;
        $bits = preg_split( '/[\t, ]+/', $line, 2 );
        if ( count( $bits ) === 2 ) $pairs[] = $bits;
        if ( count( $pairs ) >= 10 ) break;
    }
    if ( ! $pairs ) throw new Exception( 'Enter at least one old and new URL pair.' );
    $rows = array();
    foreach ( $pairs as $pair ) {
        try { $old = ufxots_status_only( $pair[0] ); } catch ( Exception $e ) { $old = array( 'status' => 'Error', 'location' => $e->getMessage() ); }
        try { $new = ufxots_status_only( $pair[1] ); } catch ( Exception $e ) { $new = array( 'status' => 'Error' ); }
        $match = ! empty( $old['location'] ) && false !== strpos( rtrim( $old['location'], '/' ), rtrim( $pair[1], '/' ) );
        $rows[] = array( $pair[0], (string) $old['status'], isset( $old['location'] ) ? $old['location'] : '', $pair[1], (string) $new['status'], $match ? 'Matches' : 'Review' );
    }
    return array( 'summary' => array( 'Pairs checked' => count( $rows ), 'Safe run limit' => '10 pairs' ), 'columns' => array( 'Old URL', 'Old status', 'Redirect location', 'Expected new URL', 'New status', 'Result' ), 'rows' => $rows, 'text' => implode( "\n", array_map( function( $row ) { return implode( "\t", $row ); }, $rows ) ), 'downloadName' => 'migration-url-report.tsv', 'notice' => 'A bounded point-in-time check was completed. Review every launch-critical URL separately.' );
}

function ufxots_remote_sri( $url, $algorithm ) {
    $map = array( 'SHA-256' => 'sha256', 'SHA-384' => 'sha384', 'SHA-512' => 'sha512' );
    $algorithm = strtoupper( $algorithm );
    if ( ! isset( $map[ $algorithm ] ) ) $algorithm = 'SHA-384';
    $response = ufxots_remote_get( $url, 5242880 );
    if ( $response['code'] < 200 || $response['code'] >= 400 ) throw new Exception( 'The asset returned HTTP ' . $response['code'] . '.' );
    $integrity = strtolower( $algorithm ) . '-' . base64_encode( hash( $map[ $algorithm ], $response['body'], true ) );
    return array( 'summary' => array( 'HTTP status' => $response['code'], 'Bytes hashed' => strlen( $response['body'] ), 'Algorithm' => $algorithm ), 'rows' => array( array( 'Integrity value', $integrity ) ), 'text' => $integrity, 'downloadName' => 'sri-hash.txt', 'notice' => 'The hash represents the exact public bytes returned for this request.' );
}
