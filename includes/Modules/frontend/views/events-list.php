<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="kq-events-list-container" style="max-width: 1200px; margin: 40px auto; padding: 0 20px;">
    
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 40px;">
        <div style="max-width: 600px;">
            <h1 style="font-size: 42px; font-weight: 800; margin-bottom: 12px; line-height: 1.1;">Upcoming Events</h1>
            <p style="color: #888; font-size: 16px;">Browse the latest events and book your tickets today.</p>
        </div>
        <div class="kq-filter-bar">
            <!-- Filter logic if needed, otherwise just placeholders -->
            <select class="kq-input" style="width: auto; margin-bottom: 0;">
                <option value="">All Categories</option>
                <option value="concerts">Concerts</option>
                <option value="business">Business</option>
                <option value="sports">Sports</option>
            </select>
        </div>
    </div>

    <!-- Events Grid -->
    <div class="kq-grid">
        <?php if ( ! empty( $events ) ) : foreach ( $events as $event ) : 
            $thumbnail = get_the_post_thumbnail_url( $event->ID, 'large' ) ?: KQ_PLUGIN_URL . 'assets/images/event-placeholder.jpg';
            $venue = get_post_meta( $event->ID, '_kq_venue_name', true );
            $start_date = get_post_meta( $event->ID, '_kq_start_date', true );
            $min_price = \KueueEvents\Core\Modules\Tickets\TicketTypeRepository::get_min_price( $event->ID );
            $terms = get_the_terms($event->ID, 'kq_event_category');
            $category = ( ! empty( $terms ) && ! is_wp_error( $terms ) ) ? $terms[0]->name : 'Event';
        ?>
        <div class="kq-event-card">
            <a href="<?php echo get_permalink( $event->ID ); ?>">
                <img src="<?php echo esc_url( $thumbnail ); ?>" class="kq-event-image" alt="<?php echo esc_attr( $event->post_title ); ?>">
            </a>
            <div class="kq-event-body">
                <span class="kq-event-category"><?php echo esc_html($category); ?></span>
                <a href="<?php echo get_permalink( $event->ID ); ?>" class="kq-event-title"><?php echo esc_html( $event->post_title ); ?></a>
                
                <div class="kq-event-meta">
                    <span style="display: flex; align-items: center; gap: 6px;">
                        <i class="fa-regular fa-calendar"></i> <?php echo esc_html( $start_date ); ?>
                    </span>
                    <span style="display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-location-dot"></i> <?php echo esc_html( $venue ); ?>
                    </span>
                </div>
            </div>
            
            <div class="kq-event-footer">
                <div class="kq-event-price">
                    <small style="color: #888; font-weight: 500; font-size: 12px; display: block; margin-bottom: -4px;">Starting from</small>
                    <?php echo function_exists('wc_price') ? wc_price( $min_price ) : $min_price; ?>
                </div>
                <a href="<?php echo get_permalink( $event->ID ); ?>" class="kq-btn kq-btn-primary" style="padding: 8px 18px; font-size: 14px;">Select Tickets</a>
            </div>
        </div>
        <?php endforeach; else : ?>
        <div style="text-align: center; padding: 60px; grid-column: 1 / -1; background: #fff; border-radius: 20px;">
            <i class="fa-regular fa-calendar-times" style="font-size: 64px; color: #eee; margin-bottom: 24px;"></i>
            <h3 style="margin:0;">No events found.</h3>
            <p style="color: #888;">Check back later for new updates.</p>
        </div>
        <?php endif; ?>
    </div>
</div>
