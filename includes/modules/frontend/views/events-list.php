<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="kq-events-list">
    <style>
        .kq-events-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; padding: 20px 0; }
        .kq-event-card { background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1); transition: transform 0.3s ease; display: flex; flex-direction: column; }
        .kq-event-card:hover { transform: translateY(-5px); }
        .kq-event-thumb { aspect-ratio: 16/9; width: 100%; position: relative; }
        .kq-event-thumb img { width: 100%; height: 100%; object-fit: cover; }
        .kq-event-badge { position: absolute; top: 10px; right: 10px; background: #0073aa; color: #fff; padding: 4px 12px; border-radius: 20px; font-size: 0.8em; font-weight: bold; }
        .kq-event-body { padding: 15px; flex: 1; }
        .kq-event-title { font-size: 1.25em; font-weight: bold; margin: 0 0 10px; color: #333; }
        .kq-event-meta { font-size: 0.9em; color: #666; margin-bottom: 15px; }
        .kq-event-footer { padding: 15px; border-top: 1px solid #efefef; text-align: center; }
        .kq-btn { background: #0073aa; color: #fff; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: bold; display: block; }
        .kq-btn:hover { background: #005177; }
    </style>

    <?php if ( empty( $events ) ) : ?>
        <p><?php _e( 'No upcoming events found.', 'kueue-events-core' ); ?></p>
    <?php else : ?>
        <?php foreach ( $events as $event ) : 
            $thumb_url = get_the_post_thumbnail_url( $event->ID, 'medium_large' ) ?: 'https://via.placeholder.com/800x450';
            $meta = get_post_meta( $event->ID, '_kq_event_settings', true );
            $date = $meta['event_start_date'] ?? 'TBD';
            $venue = $meta['event_venue_name'] ?? 'TBD';
        ?>
            <div class="kq-event-card">
                <div class="kq-event-thumb">
                    <img src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php echo esc_attr( $event->post_title ); ?>">
                    <div class="kq-event-badge"><?php echo esc_html( $date ); ?></div>
                </div>
                <div class="kq-event-body">
                    <h3 class="kq-event-title"><?php echo esc_html( $event->post_title ); ?></h3>
                    <div class="kq-event-meta">
                        <span class="dashicons dashicons-location"></span> <?php echo esc_html( $venue ); ?>
                    </div>
                    <?php echo wp_trim_words( $event->post_content, 20 ); ?>
                </div>
                <div class="kq-event-footer">
                    <a href="<?php echo get_permalink( $event->ID ); ?>" class="kq-btn"><?php _e( 'VIEW TICKETS', 'kueue-events-core' ); ?></a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
