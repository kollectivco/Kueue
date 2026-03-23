<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="kq-single-event" style="max-width: 1200px; margin: 40px auto; padding: 0 20px;">
    
    <!-- Hero Image -->
    <?php 
    $thumbnail = get_the_post_thumbnail_url( $event->ID, 'full' ) ?: KQ_PLUGIN_URL . 'assets/images/event-placeholder.jpg';
    $venue = get_post_meta( $event->ID, '_kq_venue_name', true );
    $start_date = get_post_meta( $event->ID, '_kq_start_date', true );
    $category = get_the_terms($event->ID, 'kq_event_category')[0]->name ?? 'Event';
    ?>
    <div style="position: relative; height: 500px; border-radius: 24px; overflow: hidden; margin-bottom: 50px;">
        <img src="<?php echo esc_url( $thumbnail ); ?>" style="width: 100%; height: 100%; object-fit: cover;">
        <div style="position: absolute; bottom: 0; left: 0; width: 100%; padding: 60px; background: linear-gradient(0deg, rgba(15,15,16,0.9) 0%, rgba(15,15,16,0.5) 40%, transparent 100%);">
            <span class="kq-event-category" style="background: var(--kq-primary); color: #fff; padding: 6px 14px; border-radius: 6px; font-weight: 700; font-size: 13px; margin-bottom: 20px; display: inline-block;">
                <?php echo esc_html($category); ?>
            </span>
            <h1 style="font-size: 48px; font-weight: 800; color: #fff; margin: 0; line-height: 1.1;"><?php echo esc_html( $event->post_title ); ?></h1>
            
            <div style="display: flex; gap: 30px; margin-top: 15px; color: #fff; opacity: 0.9;">
                <span style="display: flex; align-items: center; gap: 10px; font-size: 16px;">
                    <i class="fa-regular fa-calendar" style="color: var(--kq-primary);"></i> <?php echo esc_html( $start_date ); ?>
                </span>
                <span style="display: flex; align-items: center; gap: 10px; font-size: 16px;">
                    <i class="fa-solid fa-location-dot" style="color: var(--kq-primary);"></i> <?php echo esc_html( $venue ); ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Layout Grid -->
    <div style="display: grid; grid-template-columns: 2fr 1.2fr; gap: 50px;">
        
        <!-- Main Content -->
        <div class="kq-event-details">
            <div class="kq-card" style="margin-bottom: 30px; padding: 40px;">
                <h3 style="margin-top:0; margin-bottom: 24px;">About this Event</h3>
                <div style="font-size: 16px; line-height: 1.8; color: #444;">
                    <?php echo wpautop( $event->post_content ); ?>
                </div>
            </div>

            <!-- Organizer Info if needed -->
            <div class="kq-card" style="background: var(--kq-light); display: flex; align-items: center; gap: 24px; padding: 30px;">
                <div style="width: 64px; height: 64px; background: #ddd; border-radius: 50%; font-size: 24px; display: flex; align-items: center; justify-content: center; color: #999;">
                    <i class="fa fa-user"></i>
                </div>
                <div>
                    <span style="display: block; font-size: 14px; color: #888;">Hosted by</span>
                    <strong style="font-size: 18px;"><?php echo get_the_author_meta('display_name', $event->post_author); ?></strong>
                    <a href="#" style="color: var(--kq-primary); font-size: 13px; margin-left:15px; text-decoration: none; font-weight: 700;">Follow Organizer</a>
                </div>
            </div>
        </div>

        <!-- Sidebar / Ticket Selection -->
        <div class="kq-sidebar">
            <div class="kq-card" style="padding: 30px; border: 2px solid var(--kq-primary);">
                <h3 style="margin-top:0; margin-bottom: 24px;">Get Tickets</h3>
                
                <?php if ( ! empty( $ticket_types ) ) : foreach ( $ticket_types as $tt ) : ?>
                <div class="kq-ticket-item" style="border-bottom: 1px solid #eee; padding-bottom: 20px; margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                        <div>
                            <strong style="display: block; font-size: 18px;"><?php echo esc_html( $tt->name ); ?></strong>
                            <span style="color: #ff3131; font-weight: 700; font-size: 16px;"><?php echo wc_price( $tt->price ); ?></span>
                        </div>
                    </div>
                    
                    <div class="kq-ticket-selection-area">
                        <label style="font-size: 12px; color: #888; font-weight: 700; text-transform: uppercase;">Quantity</label>
                        <div style="display: flex; gap: 10px; margin-bottom: 20px;">
                            <input type="number" id="kq-qty-<?php echo $tt->id; ?>" 
                                   class="kq-qty-selector kq-input" 
                                   value="0" min="0" 
                                   max="<?php echo (int) $tt->stock_limit; ?>" 
                                   data-ticket-id="<?php echo $tt->id; ?>"
                                   style="width: 80px; margin:0;">
                            
                            <button class="kq-btn kq-btn-primary kq-add-to-cart-btn" 
                                    data-ticket-id="<?php echo $tt->id; ?>"
                                    style="flex-grow: 1; margin:0;">
                                Add to Cart
                            </button>
                        </div>
                        
                        <!-- Attendee Form Holder -->
                        <div id="kq-attendee-fields-<?php echo $tt->id; ?>" class="kq-attendee-fields">
                            <!-- Populated via JS -->
                        </div>
                    </div>
                </div>
                <?php endforeach; else : ?>
                <div style="text-align: center; color: #888; padding: 30px;">
                    No tickets available for this event yet.
                </div>
                <?php endif; ?>
                
                <p style="font-size: 12px; color: #888; text-align: center; margin-top: 20px;">
                    <i class="fa fa-lock" style="margin-right: 6px;"></i> Secure Checkout via WooCommerce
                </p>
            </div>
        </div>
    </div>
</div>
