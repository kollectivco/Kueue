<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="kq-single-event modern-layout" style="max-width: 1200px; margin: 60px auto; padding: 0 20px; font-family: 'Inter', sans-serif;">
    
    <?php 
    $thumbnail = get_the_post_thumbnail_url( $event->ID, 'full' ) ?: KQ_PLUGIN_URL . 'assets/images/event-placeholder.jpg';
    $venue = get_post_meta( $event->ID, '_kq_venue_name', true );
    $start_date = get_post_meta( $event->ID, '_kq_start_date', true );
    $category = get_the_terms($event->ID, 'kq_event_category')[0]->name ?? 'Event';
    ?>

    <!-- Header Section -->
    <header style="text-align: center; margin-bottom: 50px;">
        <span style="color: var(--kq-primary); font-weight: 800; text-transform: uppercase; letter-spacing: 2px; font-size: 14px; display: block; margin-bottom: 15px;">
            <?php echo esc_html($category); ?>
        </span>
        <h1 style="font-size: 56px; font-weight: 900; margin: 0 0 20px 0; color: #1a1a1a; letter-spacing: -1px;"><?php echo esc_html( $event->post_title ); ?></h1>
        <div style="display: flex; justify-content: center; gap: 40px; color: #666; font-size: 18px;">
            <span style="display: flex; align-items: center; gap: 8px;">
                <i class="fa-regular fa-calendar"></i> <?php echo esc_html( $start_date ); ?>
            </span>
            <span style="display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-location-dot"></i> <?php echo esc_html( $venue ); ?>
            </span>
        </div>
    </header>

    <!-- Main Image (Full Width) -->
    <div style="height: 600px; border-radius: 32px; overflow: hidden; margin-bottom: 60px; box-shadow: 0 20px 40px rgba(0,0,0,0.1);">
        <img src="<?php echo esc_url( $thumbnail ); ?>" style="width: 100%; height: 100%; object-fit: cover;">
    </div>

    <!-- Content Section -->
    <div style="max-width: 800px; margin: 0 auto 80px auto;">
        <div style="font-size: 20px; line-height: 1.8; color: #333; margin-bottom: 40px;">
            <?php echo wpautop( $event->post_content ); ?>
        </div>

        <div style="padding: 30px; border-top: 1px solid #eee; display: flex; align-items: center; justify-content: center; gap: 20px;">
            <div style="width: 48px; height: 48px; background: #eee; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #888;">
                <i class="fa fa-user"></i>
            </div>
            <span style="color: #666;">Hosted by <strong><?php echo get_the_author_meta('display_name', $event->post_author); ?></strong></span>
        </div>
    </div>

    <!-- Ticket & Interactive Section (Horizontal Layout) -->
    <div class="kq-modern-tickets" style="background: #1a1a1c; border-radius: 40px; padding: 60px; color: #fff; margin-bottom: 60px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px;">
            <h2 style="font-size: 32px; font-weight: 800; color: #fff; margin:0;">Reserve Your Spot</h2>
            <?php if ( $enable_bookings || $enable_seating ) : ?>
                <span style="color: #888; font-size: 14px;"><?php _e( 'Selection required before adding tickets', 'kueue-events-core' ); ?></span>
            <?php endif; ?>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 60px;">
            <!-- Selection Side -->
            <div class="kq-sidebar">
                <?php if ( $enable_bookings ) : ?>
                <div style="margin-bottom: 30px;">
                    <label style="display:block; font-weight:700; color: #999; text-transform:uppercase; font-size:12px; margin-bottom:12px;"><?php _e( 'Choose Date', 'kueue-events-core' ); ?></label>
                    <select id="kq-booking-date" class="kq-input" style="width:100%; background: #2a2a2c; border:none; color:#fff; height:50px;">
                        <option value=""><?php _e( '-- Select --', 'kueue-events-core' ); ?></option>
                        <?php foreach ( $booking_dates as $bd ) : ?>
                            <option value="<?php echo (int) $bd->id; ?>"><?php echo date_i18n( get_option('date_format'), strtotime($bd->event_date) ); ?></option>
                        <?php endforeach; ?>
                    </select>
                    
                    <label style="display:block; font-weight:700; color: #999; text-transform:uppercase; font-size:12px; margin-bottom:12px; margin-top:20px;"><?php _e( 'Time Slot', 'kueue-events-core' ); ?></label>
                    <select id="kq-booking-slot" class="kq-input" style="width:100%; background: #2a2a2c; border:none; color:#fff; height:50px;" disabled>
                        <option value=""><?php _e( '-- Waiting --', 'kueue-events-core' ); ?></option>
                    </select>
                </div>
                <?php endif; ?>

                <?php if ( $enable_seating && $seating_map ) : ?>
                <div>
                    <label style="display:block; font-weight:700; color: #999; text-transform:uppercase; font-size:12px; margin-bottom:12px;"><?php _e( 'Seat Map', 'kueue-events-core' ); ?></label>
                    <button type="button" class="kq-btn kq-btn-primary" id="kq-open-seating-map" style="width:100%; height:50px;"><?php _e( 'Choose Seat', 'kueue-events-core' ); ?></button>
                    <input type="hidden" id="kq-selected-seat-id" value="">
                    <div id="kq-selected-seat-display" style="margin-top:10px; font-weight:bold; color:var(--kq-primary); display:none;"></div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Tickets List -->
            <div class="kq-tickets-column">
                <?php if ( ! empty( $ticket_types ) ) : foreach ( $ticket_types as $tt ) : ?>
                <div class="kq-ticket-item" style="background: #2a2a2c; padding: 30px; border-radius: 20px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h4 style="margin:0; font-size: 20px; color: #fff;"><?php echo esc_html( $tt->name ); ?></h4>
                        <span style="color: var(--kq-primary); font-weight: 800; font-size: 24px; display: block; margin-top: 5px;"><?php echo kq_price( $tt->price ); ?></span>
                    </div>
                    
                    <div style="display: flex; gap: 15px; align-items: center;">
                        <input type="number" id="kq-qty-<?php echo $tt->id; ?>" 
                               class="kq-qty-selector" 
                               value="0" min="0" 
                               max="<?php echo (int) $tt->stock_limit; ?>" 
                               data-ticket-id="<?php echo $tt->id; ?>"
                               style="width: 70px; height: 50px; background:transparent; border: 1px solid #444; border-radius: 10px; color: #fff; text-align: center;">
                        
                        <button class="kq-btn kq-btn-primary kq-add-to-cart-btn" 
                                data-ticket-id="<?php echo $tt->id; ?>"
                                style="height: 50px; padding: 0 30px; margin: 0;">
                            Buy
                        </button>
                    </div>
                    
                    <div id="kq-attendee-fields-<?php echo $tt->id; ?>" class="kq-attendee-fields"></div>
                </div>
                <?php endforeach; else : ?>
                <p style="color: #666; text-align: center;">No tickets available.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
