<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php if ( ! isset( $event ) || ! is_object( $event ) ) return; ?>

<div class="kq-event-single">
    <style>
        .kq-event-header { margin-bottom: 30px; }
        .kq-event-hero { width: 100%; aspect-ratio: 21/9; border-radius: 15px; overflow: hidden; margin-bottom: 20px; }
        .kq-event-hero img { width: 100%; height: 100%; object-fit: cover; }
        .kq-ticket-selector { background: #f9f9f9; padding: 25px; border-radius: 12px; border: 1px solid #ddd; }
        .kq-ticket-row { display: flex; align-items: center; justify-content: space-between; padding: 15px 0; border-bottom: 1px solid #eee; }
        .kq-ticket-info h4 { margin: 0; font-size: 1.1em; color: #333; }
        .kq-ticket-price { font-weight: bold; color: #0073aa; font-size: 1.25em; }
        .kq-ticket-action select { padding: 5px 10px; border-radius: 4px; border: 1px solid #ccc; width: 60px; }
        .kq-checkout-btn { background: #28a745; color: #fff; padding: 15px 30px; border: none; border-radius: 8px; font-weight: bold; font-size: 1.1em; cursor: pointer; width: 100%; margin-top: 20px; }
        .kq-checkout-btn:disabled { background: #ccc; cursor: not-allowed; }
        .kq-attendee-form { margin-top: 20px; display: none; background: #fff; padding: 15px; border-radius: 8px; border: 1px solid #eee; }
        .kq-form-row { margin-bottom: 15px; }
        .kq-form-row label { display: block; font-size: 0.85em; color: #666; margin-bottom: 5px; }
        .kq-form-row input { width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #ddd; }
    </style>

    <div class="kq-event-header">
        <h1><?php echo esc_html( $event->post_title ); ?></h1>
        <div class="kq-event-meta">
            <?php $meta = get_post_meta($event->ID, '_kq_event_settings', true); ?>
            <p><strong>Date:</strong> <?php echo esc_html($meta['event_start_date'] ?? 'TBD'); ?></p>
            <p><strong>Venue:</strong> <?php echo esc_html($meta['event_venue_name'] ?? 'TBD'); ?></p>
        </div>
    </div>

    <div class="kq-event-content">
        <?php echo apply_filters('the_content', $event->post_content); ?>
    </div>

    <div class="kq-ticket-selector">
        <h3><?php _e( 'Select Tickets', 'kueue-events-core' ); ?></h3>
        
        <?php if ( empty( $ticket_types ) ) : ?>
            <p><?php _e( 'No ticket types available for this event.', 'kueue-events-core' ); ?></p>
        <?php else : ?>
            <form id="kq-ticket-form">
                <?php foreach ( $ticket_types as $tt ) : ?>
                    <div class="kq-ticket-row">
                        <div class="kq-ticket-info">
                            <h4><?php echo esc_html( $tt->name ); ?></h4>
                            <p><?php echo esc_html( $tt->description ); ?></p>
                            <span class="kq-ticket-price"><?php echo number_format($tt->price, 2); ?> <?php echo esc_html($tt->currency); ?></span>
                        </div>
                        <div class="kq-ticket-action">
                            <select name="qty_<?php echo $tt->id; ?>" class="kq-qty-select" data-id="<?php echo $tt->id; ?>" data-price="<?php echo $tt->price; ?>">
                                <option value="0">0</option>
                                <?php for ($i=1; $i<=10; $i++) echo "<option value='$i'>$i</option>"; ?>
                            </select>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div id="kq-attendee-fields" class="kq-attendee-form">
                    <h4><?php _e( 'Attendee Information', 'kueue-events-core' ); ?></h4>
                    <div id="attendee-inputs-container"></div>
                </div>

                <input type="hidden" name="event_id" value="<?php echo $event->ID; ?>">
                
                <div class="kq-form-row" style="margin-top:20px;">
                    <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                        <input type="checkbox" id="kq-terms-check" required style="width:auto;">
                        <span><?php _e( 'I agree to the Terms & Conditions and Privacy Policy.', 'kueue-events-core' ); ?></span>
                    </label>
                </div>

                <button type="button" id="kq-buy-btn" class="kq-checkout-btn" disabled><?php _e( 'BOOK NOW', 'kueue-events-core' ); ?></button>
            </form>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('kq-ticket-form');
            if(!form) return;
            
            const qtySelects = document.querySelectorAll('.kq-qty-select');
            const buyBtn = document.getElementById('kq-buy-btn');
            const container = document.getElementById('attendee-inputs-container');
            const attendeeForm = document.getElementById('kq-attendee-fields');

            qtySelects.forEach(select => {
                select.addEventListener('change', updateForm);
            });

            function updateForm() {
                container.innerHTML = '';
                let totalQty = 0;
                
                qtySelects.forEach(select => {
                    const id = select.dataset.id;
                    const qty = parseInt(select.value);
                    totalQty += qty;

                    for(let i=0; i<qty; i++) {
                        const html = `
                            <div class="kq-form-row">
                                <label>Guest ${i+1} (${select.parentElement.parentElement.querySelector('h4').innerText}) Name:</label>
                                <div style="display:flex; gap:10px;">
                                    <input type="text" name="att[${id}][${i}][first_name]" placeholder="First Name" required>
                                    <input type="text" name="att[${id}][${i}][last_name]" placeholder="Last Name" required>
                                </div>
                                <input type="email" name="att[${id}][${i}][email]" placeholder="Email Address" required style="margin-top:5px;">
                            </div>
                        `;
                        container.insertAdjacentHTML('beforeend', html);
                    }
                });

                attendeeForm.style.display = totalQty > 0 ? 'block' : 'none';
                buyBtn.disabled = totalQty === 0;
            }

            buyBtn.addEventListener('click', async () => {
                const formData = new FormData(form);
                formData.append('action', 'kq_add_to_cart');
                
                buyBtn.innerText = 'Redirecting...';
                buyBtn.disabled = true;

                try {
                    const response = await fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
                        method: 'POST',
                        body: formData
                    });
                    const res = await response.json();
                    if(res.success) {
                        window.location.href = res.data.redirect_url;
                    } else {
                        alert(res.data.message);
                        buyBtn.innerText = 'BOOK NOW';
                        buyBtn.disabled = false;
                    }
                } catch(e) {
                    alert("Communication error occurred.");
                    buyBtn.disabled = false;
                }
            });
        });
    </script>
</div>
