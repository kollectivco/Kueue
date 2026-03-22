<?php

namespace KueueEvents\Core\Core;

class GitHubUpdater {

    private $repo = 'kollectivco/Kueue';
    private $slug = 'kueue-events-core/kueue-events-core.php';
    private $latest_version = null;

    public function run() {
        // Hooks for WordPress update transient
        add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'inject_update_info' ] );
        add_filter( 'plugins_api', [ $this, 'inject_plugin_info' ], 10, 3 );
        add_action( 'upgrader_process_complete', [ $this, 'clear_transients' ], 10, 2 );
    }

    /**
     * Check GitHub for updates and inject into the WordPress update transient.
     */
    public function inject_update_info( $transient ) {
        if ( empty( $transient->checked ) ) return $transient;

        $remote_release = $this->get_latest_release();
        if ( ! $remote_release ) return $transient;

        $current_version = VersionManager::get_current_version();
        $remote_version = ltrim( $remote_release->tag_name, 'v' );

        if ( version_compare( $current_version, $remote_version, '<' ) ) {
            $obj = new \stdClass();
            $obj->slug = 'kueue-events-core';
            $obj->plugin = $this->slug;
            $obj->new_version = $remote_version;
            $obj->url = 'https://github.com/' . $this->repo;
            $obj->package = $remote_release->zipball_url;
            $obj->tested = get_bloginfo( 'version' );

            $transient->response[ $this->slug ] = $obj;
        }

        return $transient;
    }

    /**
     * Fetch latest release from GitHub API.
     */
    private function get_latest_release() {
        if ( ! is_null( $this->latest_version ) ) {
            return $this->latest_version;
        }

        $url = "https://api.github.com/repos/{$this->repo}/releases/latest";
        
        $response = wp_remote_get( $url, [
            'timeout' => 10,
            'headers' => [ 'Accept' => 'application/vnd.github.v3+json' ]
        ] );

        if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
            return false;
        }

        $this->latest_version = json_decode( wp_remote_retrieve_body( $response ) );
        return $this->latest_version;
    }

    /**
     * Provide plugin details in the updates popup window.
     */
    public function inject_plugin_info( $res, $action, $args ) {
        if ( $action !== 'plugin_information' || ( $args->slug !== 'kueue-events-core' && ! strpos( $this->slug, $args->slug ) ) ) {
            return $res;
        }

        $remote = $this->get_latest_release();
        if ( ! $remote ) return $res;

        $res = new \stdClass();
        $res->name = 'Kueue Events Core';
        $res->slug = 'kueue-events-core';
        $res->version = ltrim( $remote->tag_name, 'v' );
        $res->author = 'Antigravity';
        $res->homepage = 'https://github.com/' . $this->repo;
        $res->download_link = $remote->zipball_url;
        $res->sections = [
            'description' => 'Professional event marketplace system.',
            'changelog'   => $remote->body ?? 'Latest stable release.',
        ];

        return $res;
    }

    /**
     * Clear transients after update completion.
     */
    public function clear_transients( $upgrader, $options ) {
        if ( $options['action'] === 'update' && $options['type'] === 'plugin' ) {
            delete_site_transient( 'update_plugins' );
        }
    }
}
