<?php
/**
 * Single Event Template (Native)
 */

if ( ! defined( 'ABSPATH' ) ) exit;

get_header();

while ( have_posts() ) :
    the_post();
    $event_id = get_the_ID();
    
    // Fetch Metadata
    $organizer_id = get_post_meta( $event_id, '_kq_organizer_id', true );
    $venue_id     = get_post_meta( $event_id, '_kq_venue_id', true );
    $start_date   = get_post_meta( $event_id, '_kq_start_date', true );
    $start_time   = get_post_meta( $event_id, '_kq_start_time', true );
    $accent_color = get_post_meta( $event_id, '_kq_accent_color', true ) ?: '#ff3131';

    // Venue Logic
    if ( $venue_id ) {
        $venue_post = get_post( $venue_id );
        $venue_name = $venue_post ? $venue_post->post_title : '';
        $venue_url  = $venue_post ? get_permalink( $venue_post ) : '';
    } else {
        $venue_name = get_post_meta( $event_id, '_kq_venue_name', true );
        $venue_url  = '';
    }

    // Tickets & Addons
    $ticket_types    = \KueueEvents\Core\Modules\Tickets\TicketTypeRepository::get_by_event_id( $event_id );
    $enable_bookings = get_post_meta( $event_id, '_kq_enable_bookings', true );
    $enable_seating  = get_post_meta( $event_id, '_kq_enable_seating', true );
    
    $booking_dates = $enable_bookings ? \KueueEvents\Core\Modules\Bookings\BookingRepository::get_dates( $event_id ) : [];
    $seating_map   = $enable_seating ? \KueueEvents\Core\Modules\Seating\SeatingRepository::get_map_by_event( $event_id ) : null;
    ?>

    <style>
        :root { --kq-primary: <?php echo esc_attr( $accent_color ); ?>; }
        .kq-single-container { max-width: 1200px; margin: 40px auto; padding: 0 20px; font-family: 'Inter', sans-serif; }
        .kq-hero { position: relative; height: 500px; border-radius: 24px; overflow: hidden; margin-bottom: 50px; background: #1a1a1b; }
        .kq-hero-overlay { position: absolute; bottom: 0; left: 0; width: 100%; padding: 60px; background: linear-gradient(0deg, rgba(15,15,16,0.95) 0%, rgba(15,15,16,0.6) 50%, transparent 100%); }
        .kq-grid { display: grid; grid-template-columns: 2fr 1.2fr; gap: 50px; }
        @media (max-width: 900px) { .kq-grid { grid-template-columns: 1fr; } }
    </style>

    <div class="kq-single-container">
        <div class="kq-hero">
            <?php if ( has_post_thumbnail() ) : ?>
                <?php the_post_thumbnail( 'full', [ 'style' => 'width: 100%; height: 100%; object-fit: cover;' ] ); ?>
            <?php endif; ?>
            <div class="kq-hero-overlay">
                <h1 style="font-size: 48px; font-weight: 800; color: #fff; margin: 0; line-height: 1.1;"><?php the_title(); ?></h1>
                <div style="display: flex; gap: 30px; margin-top: 20px; color: #fff; opacity: 0.9;">
                    <span style="display: flex; align-items: center; gap: 10px; font-size: 16px;">
                        <i class="fa-regular fa-calendar" style="color: var(--kq-primary);"></i> <?php echo esc_html( $start_date ); ?> @ <?php echo esc_html( $start_time ); ?>
                    </span>
                    <span style="display: flex; align-items: center; gap: 10px; font-size: 16px;">
                        <i class="fa-solid fa-location-dot" style="color: var(--kq-primary);"></i> 
                        <?php if ( $venue_url ) : ?>
                            <a href="<?php echo esc_url( $venue_url ); ?>" style="color: #fff; text-decoration: none;"><?php echo esc_html( $venue_name ); ?></a>
                        <?php else : ?>
                            <?php echo esc_html( $venue_name ); ?>
                        <?php endif; ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="kq-grid">
            <div class="kq-content">
                <div class="kq-card" style="padding: 40px; margin-bottom: 30px;">
                    <h3 style="margin-top:0; margin-bottom: 24px;">About this Event</h3>
                    <div style="font-size: 17px; line-height: 1.8; color: #333;">
                        <?php the_content(); ?>
                    </div>
                </div>
            </div>

            <div class="kq-sidebar">
                <!-- Selection Card -->
                <?php if ( $enable_bookings || $enable_seating ) : ?>
                <div class="kq-card" style="padding: 30px; margin-bottom: 20px; border-left: 5px solid var(--kq-primary);">
                    <h3 style="margin-top:0; margin-bottom: 15px;"><?php _e( 'Selection Required', 'kueue-events-core' ); ?></h3>
                    
                    <?php if ( $enable_bookings ) : ?>
                    <div class="kq-booking-selector" style="margin-bottom: 20px;">
                        <label style="display:block; font-weight:700; margin-bottom:8px;"><?php _e( 'Select Date', 'kueue-events-core' ); ?></label>
                        <select id="kq-booking-date" class="kq-input" style="width:100%;">
                            <option value=""><?php _e( '-- Choose Date --', 'kueue-events-core' ); ?></option>
                            <?php foreach ( $booking_dates as $bd ) : ?>
                                <option value="<?php echo (int) $bd->id; ?>"><?php echo date_i18n( get_option('date_format'), strtotime($bd->event_date) ); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select id="kq-booking-slot" class="kq-input" style="width:100%; margin-top:10px;" disabled>
                            <option value=""><?php _e( '-- Select Date First --', 'kueue-events-core' ); ?></option>
                        </select>
                    </div>
                    <?php endif; ?>

                    <?php if ( $enable_seating && $seating_map ) : ?>
                    <div class="kq-seating-selector">
                        <label style="display:block; font-weight:700; margin-bottom:8px;"><?php _e( 'Choose Your Seat', 'kueue-events-core' ); ?></label>
                        <div id="kq-seating-container" style="background:#f9f9f9; padding:20px; border-radius:12px; border:1px dashed #ddd; text-align:center;">
                            <button type="button" class="kq-btn kq-btn-outline" id="kq-open-seating-map"><?php _e( 'Open Map', 'kueue-events-core' ); ?></button>
                        </div>
                        <input type="hidden" id="kq-selected-seat-id" value="">
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Tickets Card -->
                <div class="kq-card" style="padding: 30px; border: 2px solid var(--kq-primary); border-radius: 16px;">
                    <h3 style="margin-top:0; margin-bottom: 24px;">Buy Tickets</h3>
                    <?php if ( ! empty( $ticket_types ) ) : foreach ( $ticket_types as $tt ) : ?>
                        <div class="kq-ticket-item" style="border-bottom: 1px solid #eee; padding-bottom: 20px; margin-bottom: 20px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                                <div>
                                    <strong style="display: block; font-size: 18px;"><?php echo esc_html( $tt->name ); ?></strong>
                                    <span style="color: var(--kq-primary); font-weight: 700; font-size: 20px;"><?php echo kq_price( $tt->price ); ?></span>
                                </div>
                            </div>
                            <div style="display: flex; gap: 10px;">
                                <input type="number" id="kq-qty-<?php echo $tt->id; ?>" class="kq-qty-selector kq-input" value="1" min="1" style="width: 70px;">
                                <button class="kq-btn kq-btn-primary kq-add-to-cart-btn" data-ticket-id="<?php echo $tt->id; ?>" style="flex-grow:1;">Add to Cart</button>
                            </div>
                        </div>
                    <?php endforeach; else : ?>
                        <p style="text-align:center; color:#888;">No tickets available.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php
endwhile;

get_footer();
