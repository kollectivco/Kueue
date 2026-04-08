<?php

namespace KueueEvents\Core\Modules\Frontend;

class TemplateManager {

    public function run() {
        add_filter( 'template_include', [ $this, 'load_templates' ] );
    }

    public function load_templates( $template ) {
        if ( is_singular( 'kq_event' ) ) {
            $new_template = KQ_PLUGIN_DIR . 'includes/Modules/Frontend/views/single-kq_event.php';
            if ( file_exists( $new_template ) ) {
                return $new_template;
            }
        }

        if ( is_singular( 'kq_venue' ) ) {
            $new_template = KQ_PLUGIN_DIR . 'includes/Modules/Frontend/views/single-kq_venue.php';
            if ( file_exists( $new_template ) ) {
                return $new_template;
            }
        }

        if ( is_post_type_archive( 'kq_event' ) ) {
            $new_template = KQ_PLUGIN_DIR . 'includes/Modules/Frontend/views/archive-kq_event.php';
            if ( file_exists( $new_template ) ) {
                return $new_template;
            }
        }

        if ( is_post_type_archive( 'kq_venue' ) ) {
            $new_template = KQ_PLUGIN_DIR . 'includes/Modules/Frontend/views/archive-kq_venue.php';
            if ( file_exists( $new_template ) ) {
                return $new_template;
            }
        }

        return $template;
    }
}
