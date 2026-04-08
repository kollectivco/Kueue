<?php
/**
 * Single Venue Template (Native)
 */

if ( ! defined( 'ABSPATH' ) ) exit;

get_header();

while ( have_posts() ) :
    the_post();
    $venue_id = get_the_ID();
    
    // Fetch Metadata
    $address   = get_post_meta( $venue_id, '_kq_venue_address', true );
    $city      = get_post_meta( $venue_id, '_kq_venue_city', true );
    $country   = get_post_meta( $venue_id, '_kq_venue_country', true );
    $gmaps_url = get_post_meta( $venue_id, '_kq_venue_gmaps_url', true );

    // Fetch Events at this venue
    $args = [
        'post_type'  => 'kq_event',
        'meta_key'   => '_kq_venue_id',
        'meta_value' => $venue_id,
        'numberposts' => -1,
        'post_status' => 'publish'
    ];
    $events = get_posts( $args );
    ?>

    <style>
        .kq-venue-container { max-width: 1200px; margin: 40px auto; padding: 0 20px; font-family: 'Inter', sans-serif; }
        .kq-venue-hero { display: grid; grid-template-columns: 1.5fr 1fr; gap: 40px; margin-bottom: 60px; }
        @media (max-width: 900px) { .kq-venue-hero { grid-template-columns: 1fr; } }
        .kq-venue-image { height: 450px; border-radius: 20px; overflow: hidden; background: #eee; }
        .kq-venue-info { padding: 20px 0; }
        .kq-event-card { background: #fff; border-radius: 16px; margin-bottom: 20px; padding: 25px; border: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; transition: all 0.3s; }
        .kq-event-card:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); border-color: var(--kq-primary); }
    </style>

    <div class="kq-venue-container">
        <div class="kq-venue-hero">
            <div class="kq-venue-image">
                <?php if ( has_post_thumbnail() ) : ?>
                    <?php the_post_thumbnail( 'full', [ 'style' => 'width: 100%; height: 100%; object-fit: cover;' ] ); ?>
                <?php endif; ?>
            </div>
            <div class="kq-venue-info">
                <h1 style="font-size: 42px; font-weight: 800; margin: 0 0 20px;"><?php the_title(); ?></h1>
                <p style="font-size: 18px; color: #555; line-height: 1.6; margin-bottom: 30px;">
                    <i class="fa-solid fa-location-dot" style="color: #ff3131; margin-right: 10px;"></i>
                    <?php echo esc_html( $address ); ?>, <?php echo esc_html( $city ); ?>, <?php echo esc_html( $country ); ?>
                </p>
                <?php if ( $gmaps_url ) : ?>
                    <a href="<?php echo esc_url( $gmaps_url ); ?>" target="_blank" class="kq-btn kq-btn-outline" style="display: inline-flex; align-items: center; gap: 10px;">
                        <i class="fa-solid fa-map"></i> <?php _e( 'Open in Google Maps', 'kueue-events-core' ); ?>
                    </a>
                <?php endif; ?>
                
                <div style="margin-top: 40px; border-top: 1px solid #eee; padding-top: 30px;">
                    <h4 style="margin: 0 0 10px; text-transform: uppercase; font-size: 12px; letter-spacing: 1px; color: #888;">Venue Description</h4>
                    <div style="font-size: 16px; line-height: 1.7; color: #444;">
                        <?php the_content(); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="kq-upcoming-events">
            <h2 style="font-size: 32px; font-weight: 800; margin-bottom: 40px;"><?php _e( 'Upcoming Events here', 'kueue-events-core' ); ?></h2>
            
            <?php if ( ! empty( $events ) ) : foreach ( $events as $event ) : 
                $e_date = get_post_meta( $event->ID, '_kq_start_date', true );
                $e_time = get_post_meta( $event->ID, '_kq_start_time', true );
                ?>
                <div class="kq-event-card">
                    <div>
                        <span style="font-size: 13px; font-weight: 700; color: #ff3131; text-transform: uppercase;">
                            <?php echo esc_html( date_i18n( 'M j, Y', strtotime($e_date) ) ); ?> @ <?php echo esc_html( $e_time ); ?>
                        </span>
                        <h3 style="margin: 5px 0 0; font-size: 20px;"><?php echo esc_html( $event->post_title ); ?></h3>
                    </div>
                    <div>
                        <a href="<?php echo get_permalink( $event ); ?>" class="kq-btn kq-btn-primary" style="margin: 0;"><?php _e( 'View Event', 'kueue-events-core' ); ?></a>
                    </div>
                </div>
            <?php endforeach; else : ?>
                <div class="kq-card" style="padding: 60px; text-align: center; color: #888;">
                    <i class="fa-regular fa-calendar-xmark" style="font-size: 40px; margin-bottom: 20px; display: block; opacity: 0.3;"></i>
                    <p><?php _e( 'No upcoming events scheduled at this venue yet.', 'kueue-events-core' ); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php
endwhile;

get_footer();
