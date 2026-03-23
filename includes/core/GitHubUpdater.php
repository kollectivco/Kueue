<?php

namespace KueueEvents\Core\Core;

class GitHubUpdater {

    private $repo = 'kollectivco/Kueue';
    private $cache_key = 'kq_gh_release_latest';

    public function run() {
        $library_path = KQ_PLUGIN_DIR . 'includes/Vendor/plugin-update-checker/plugin-update-checker.php';
        
        if ( file_exists( $library_path ) ) {
            require_once $library_path;
            
            $update_checker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
                'https://github.com/' . $this->repo,
                KQ_PLUGIN_FILE,
                'kueue-events-core'
            );

            // Optional: Set the branch that contains the stable code
            $update_checker->setBranch('main');
        } else {
            // Fallback: use manual implementation if library is missing
            add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'inject_update_info' ] );
            add_filter( 'plugins_api', [ $this, 'inject_plugin_info' ], 10, 3 );
            add_filter( 'upgrader_source_selection', [ $this, 'upgrader_source_selection' ], 10, 3 );
            add_action( 'upgrader_process_complete', [ $this, 'clear_transients' ], 10, 2 );
        }
    }

    /**
     * Manual Fallback Implementation below
     */

    /**
     * Check GitHub for updates and inject into the WordPress update transient.
     */
    public function inject_update_info( $transient ) {
        if ( empty( $transient->checked ) ) {
            return $transient;
        }

        $remote_release = $this->get_latest_release();
        if ( ! $remote_release || is_wp_error( $remote_release ) ) {
            return $transient;
        }

        $current_version = VersionManager::get_current_version();
        $remote_version = ltrim( $remote_release->tag_name, 'v' );

        if ( version_compare( $current_version, $remote_version, '<' ) ) {
            $plugin_slug = KQ_PLUGIN_BASENAME;

            $obj = new \stdClass();
            $obj->id = $plugin_slug;
            $obj->slug = dirname( $plugin_slug );
            $obj->plugin = $plugin_slug;
            $obj->new_version = $remote_version;
            $obj->url = 'https://github.com/' . $this->repo;
            $obj->package = $remote_release->zipball_url;
            $obj->tested = get_bloginfo( 'version' );
            $obj->requires = '5.8';
            $obj->requires_php = '7.4';

            $transient->response[ $plugin_slug ] = $obj;
        }

        return $transient;
    }

    /**
     * Fetch latest release from GitHub API with caching.
     */
    private function get_latest_release() {
        $cached = get_site_transient( $this->cache_key );
        if ( $cached !== false ) {
            return $cached;
        }

        $url = "https://api.github.com/repos/{$this->repo}/releases/latest";
        
        $response = wp_remote_get( $url, [
            'timeout' => 15,
            'headers' => [ 'Accept' => 'application/vnd.github.v3+json' ]
        ] );

        if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
            // Cache the failure briefly so we don't spam GitHub on rate limit
            set_site_transient( $this->cache_key, null, 15 * MINUTE_IN_SECONDS );
            error_log( "[Kueue Updater] Failed to fetch latest release from GitHub." );
            return false;
        }

        $body = wp_remote_retrieve_body( $response );
        $release = json_decode( $body );

        if ( ! $release || empty( $release->tag_name ) ) {
            return false;
        }

        // Cache successful response for 12 hours
        set_site_transient( $this->cache_key, $release, 12 * HOUR_IN_SECONDS );
        return $release;
    }

    /**
     * Provide plugin details in the "View details" popup window.
     */
    public function inject_plugin_info( $res, $action, $args ) {
        if ( $action !== 'plugin_information' ) {
            return $res;
        }

        // Check if it's our plugin
        $plugin_dir = dirname( KQ_PLUGIN_BASENAME );
        if ( empty( $args->slug ) || $args->slug !== $plugin_dir ) {
            return $res;
        }

        $remote = $this->get_latest_release();
        if ( ! $remote || is_wp_error( $remote ) ) {
            return $res;
        }

        $res = new \stdClass();
        $res->name = 'Kueue Events Core';
        $res->slug = $plugin_dir;
        $res->version = ltrim( $remote->tag_name, 'v' );
        $res->author = '<a href="https://antigravity.ai">Antigravity</a>';
        $res->homepage = 'https://github.com/' . $this->repo;
        $res->download_link = $remote->zipball_url;
        $res->tested = get_bloginfo( 'version' );
        $res->requires = '5.8';
        $res->requires_php = '7.4';
        
        $changelog = nl2br( esc_html( $remote->body ?? 'Latest stable release.' ) );

        $res->sections = [
            'description' => '<p>Full event marketplace system for ticket management, bookings, and multi-vendor support.</p>',
            'changelog'   => '<h4>Release ' . esc_html( $remote->tag_name ) . '</h4><p>' . $changelog . '</p>',
        ];

        return $res;
    }

    /**
     * Rename the extracted GitHub folder to match our plugin's directory.
     * GitHub zipballs extract to something like: kollectivco-Kueue-abc1234
     */
    public function upgrader_source_selection( $source, $remote_source, $upgrader ) {
        global $wp_filesystem;

        // Ensure this is a plugin update
        if ( ! isset( $upgrader->skin->plugin_info ) ) {
            // In WP 5.5+ the skin might not always have plugin_info if it's a theme/core update
            return $source;
        }

        // Check if the source directory name contains clues it's from our GitHub repo
        // E.g., 'kollectivco-Kueue'
        $repo_name = explode( '/', $this->repo )[1]; // "Kueue"
        if ( strpos( basename( $source ), $repo_name ) !== false ) {
            $plugin_dir = dirname( KQ_PLUGIN_BASENAME );
            $new_source = trailingslashit( $remote_source ) . $plugin_dir;

            // Rename the folder
            if ( $wp_filesystem->move( $source, $new_source, true ) ) {
                return trailingslashit( $new_source );
            }
        }

        return $source;
    }

    /**
     * Clear transients after update completion or whenever necessary.
     */
    public function clear_transients( $upgrader_object, $options ) {
        if ( $options['action'] === 'update' && $options['type'] === 'plugin' ) {
            delete_site_transient( 'update_plugins' );
            delete_site_transient( $this->cache_key );
        }
    }
}
