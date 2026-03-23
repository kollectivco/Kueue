<h4><?php esc_attr_e( 'Tickets', 'fooevents-pdf-tickets' ); ?></h4>

<?php
/* ========== Helper: robust Row/Seat extractor ========== */
if ( ! function_exists('kueue_get_row_seat') ) {
  function kueue_get_row_seat( $ticket_id ) {
    $row  = '';
    $seat = '';

    $seating_fields = get_post_meta( $ticket_id, 'WooCommerceEventsSeatingFields', true );
    if ( is_array( $seating_fields ) ) {
      foreach ( $seating_fields as $k => $v ) {
        $key = strtolower( trim( (string) $k ) );
        $val = is_array( $v ) ? reset( $v ) : $v;
        $val = trim( (string) $val );
        if ( $val === '' ) continue;

        if ( $row === ''  && preg_match('/\b(row|صف|block|table|section|sector|zone)\b/u', $key) )  { $row  = $val; continue; }
        if ( $seat === '' && preg_match('/\b(seat|كرسي|مقعد|chair|seat_no|seatnumber)\b/u', $key) ) { $seat = $val; continue; }
      }
      if ( $row === '' || $seat === '' ) {
        $vals = array_values( array_unique( array_filter( array_map( fn($vv)=>trim((string)(is_array($vv)?reset($vv):$vv)), $seating_fields ) ) ) );
        if ( $row  === '' && isset($vals[0]) ) $row  = $vals[0];
        if ( $seat === '' && isset($vals[1]) ) $seat = $vals[1];
      }
    }

    $att = get_post_meta( $ticket_id, 'WooCommerceEventsAttendeeSeating', true );
    if ( ($row==='' || $seat==='') && is_string($att) && $att!=='' ) {
      if ( $row===''  && preg_match('/row[:\s\-#]*([A-Za-z0-9]+)\b/i',  $att, $m) ) $row  = $m[1];
      if ( $seat==='' && preg_match('/seat[:\s\-#]*([A-Za-z0-9]+)\b/i', $att, $m) ) $seat = $m[1];
      if ( ($row==='' || $seat==='') && preg_match('/^\s*([A-Za-z0-9]+)\s*[-\/,]\s*([A-Za-z0-9]+)\s*$/', trim($att), $m) ) {
        if ( $row  === '' ) $row  = $m[1];
        if ( $seat === '' ) $seat = $m[2];
      }
    }

    if ( $row !== '' && $seat !== '' && strtolower($row) === strtolower($seat) ) { $seat = ''; }

    return array( $row, $seat );
  }
}

/* ========== Group tickets by Order + Product ========== */
$groups = array();
foreach ( $tickets as $t ) {
  $pid      = get_post_meta( $t->ID, 'WooCommerceEventsProductID', true );
  $order_id = get_post_meta( $t->ID, 'WooCommerceEventsOrderID', true );
  $key      = $order_id . '|' . $pid;
  if ( ! isset( $groups[$key] ) ) $groups[$key] = array( 'product_id'=>$pid, 'order_id'=>$order_id, 'items'=>array() );
  $groups[$key]['items'][] = $t;
}

$g_index = 0;
foreach ( $groups as $grp ) :

  $product_id  = $grp['product_id'];
  $order_id    = $grp['order_id'];
  $acc_id      = 'acc_' . esc_attr( $order_id . '_' . $product_id );

  // Buyer
  $order      = function_exists('wc_get_order') ? wc_get_order( $order_id ) : null;
  $buyer_name = $order ? trim( $order->get_billing_first_name().' '.$order->get_billing_last_name() ) : '';
  if ( ! $buyer_name ) {
    $firstT = $grp['items'][0];
    $buyer_name = trim( get_post_meta($firstT->ID,'WooCommerceEventsAttendeeName',true).' '.get_post_meta($firstT->ID,'WooCommerceEventsAttendeeLastName',true) );
  }

  // Event data
  $event_title = get_the_title( $product_id );          // <-- هيظهر في الهيدر قبل الكاتيجوري
  $location    = get_post_meta( $product_id, 'WooCommerceEventsLocation', true );
  $start_date  = get_post_meta( $product_id, 'WooCommerceEventsDate', true );
  $event_hour  = get_post_meta( $product_id, 'WooCommerceEventsHour', true );
  $event_min   = get_post_meta( $product_id, 'WooCommerceEventsMinutes', true );
  $event_ampm  = get_post_meta( $product_id, 'WooCommerceEventsPeriod', true );
  $thumb       = get_post_meta( $product_id, 'WooCommerceEventsTicketHeaderImage', true );

  // Product categories (Event Category)
  $event_cats_arr = wp_get_post_terms( $product_id, 'product_cat', array('fields'=>'names') );
  $event_category = ! empty($event_cats_arr) ? implode(', ', $event_cats_arr) : $event_title;

  $date_str = $start_date ? date_i18n( 'D j M Y', strtotime($start_date) ) : '';
  $total_tickets = count( $grp['items'] );
?>
<div class="booking-card booking-accordion" data-acc="<?php echo esc_attr($acc_id); ?>">
  <!-- Toggle button (round chevron) -->
  <button class="acc-toggle" type="button" aria-expanded="false" aria-controls="<?php echo esc_attr($acc_id); ?>" title="<?php esc_attr_e('Toggle tickets','fooevents-pdf-tickets'); ?>">
    <span class="acc-chev" aria-hidden="true"></span>
  </button>

  <!-- Header: Name | Event | Event Category | Booking No. -->
  <div class="booking-head head-4">
    <div class="bh-cell">
      <div class="bh-label"><?php esc_html_e('Name','fooevents-pdf-tickets'); ?></div>
      <div class="bh-value bh-accent"><?php echo esc_html( $buyer_name ); ?></div>
    </div>

    <div class="bh-sep"></div>

    <div class="bh-cell">
      <div class="bh-label"><?php esc_html_e('Event','fooevents-pdf-tickets'); ?></div>
      <div class="bh-value"><?php echo esc_html( $event_title ); ?></div>
    </div>

    <div class="bh-sep"></div>

    <div class="bh-cell">
      <div class="bh-label"><?php esc_html_e('Event Category','fooevents-pdf-tickets'); ?></div>
      <div class="bh-value"><?php echo esc_html( $event_category ); ?></div>
    </div>

    <div class="bh-sep"></div>

    <div class="bh-cell bh-right">
      <div class="bh-label"><?php esc_html_e('Booking No.','fooevents-pdf-tickets'); ?></div>
      <div class="bh-value"><?php echo esc_html( $order_id ); ?></div>
      <div class="bh-badge"><?php echo esc_html( $total_tickets . ' ' . _n('Ticket','Tickets',$total_tickets,'fooevents-pdf-tickets') ); ?></div>
    </div>
  </div>

  <!-- Collapsible panel -->
  <div class="acc-panel" id="<?php echo esc_attr($acc_id); ?>">
    <div class="acc-inner">
      <div class="booking-list">
        <?php foreach ( $grp['items'] as $index => $ticket ) :
          $ticket_id   = get_post_meta( $ticket->ID, 'WooCommerceEventsTicketID', true );
          $ticket_hash = get_post_meta( $ticket->ID, 'WooCommerceEventsTicketHash', true );
          $attF        = get_post_meta( $ticket->ID, 'WooCommerceEventsAttendeeName', true );
          $attL        = get_post_meta( $ticket->ID, 'WooCommerceEventsAttendeeLastName', true );

          // Variations -> type
          $variations = get_post_meta( $ticket->ID, 'WooCommerceEventsVariations', true );
          $type = '';
          if ( is_array($variations) ) {
            if ( ! empty($variations['attribute_type']) ) $type = esc_html( $variations['attribute_type'] );
            else { foreach ( $variations as $k=>$v ) { if ( is_string($v) && $v!=='' ) { $type=esc_html($v); break; } } }
          }

          // Row/Seat
          list($row, $seat) = kueue_get_row_seat( $ticket->ID );

          // Links
          $ticket_path = ! empty( $ticket_hash )
            ? $this->config->pdf_ticket_url . "{$attF}-{$attL}-{$ticket_id}.pdf"
            : $this->config->pdf_ticket_url . "{$ticket_id}-{$ticket_id}.pdf";

          $ticket_html_path = ! empty( $ticket_hash )
            ? 'https://kueue.net/app/tickets/' . "{$attF}-{$attL}-{$ticket_id}"
            : 'https://kueue.net/app/tickets/' . "{$ticket_id}-{$ticket_id}";
        ?>
        <div class="ticket-line <?php echo $index>0 ? 'has-sep' : ''; ?>">
          <div class="tl-left">
            <?php if ( $thumb ) : ?>
              <img class="tl-thumb" src="<?php echo esc_url( $thumb ); ?>" alt="">
            <?php else: ?>
              <div class="tl-thumb tl-thumb--ph"></div>
            <?php endif; ?>
            <div class="tl-title"><?php echo esc_html( $event_title ); ?></div>
          </div>

          <div class="tl-mid">
            <?php if ( $location ) : ?>
              <div class="tl-meta">
                <img class="tl-ico" src="https://kueue.net/wp-content/uploads/2025/09/location-1.svg" alt="Location">
                <div class="tl-text"><div class="tl-label"><?php echo esc_html( $location ); ?></div></div>
              </div>
            <?php endif; ?>
            <div class="tl-meta">
              <img class="tl-ico" src="https://kueue.net/wp-content/uploads/2025/09/calendar.svg" alt="Date">
              <div class="tl-text">
                <div class="tl-label"><?php echo esc_html( $date_str ); ?></div>
                <?php if ( $event_hour !== '' ) : ?>
                  <div class="tl-sub"><?php
                    printf( esc_html__('Time: %s:%s %s','fooevents-pdf-tickets'),
                      str_pad($event_hour,2,'0',STR_PAD_LEFT),
                      str_pad($event_min,2,'0',STR_PAD_LEFT),
                      esc_html($event_ampm) );
                  ?></div>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <div class="tl-right">
            <div class="tl-seat">
              <img class="tl-ico" src="https://kueue.net/wp-content/uploads/2025/09/Tickett.svg" alt="Ticket Type">
              <div class="tl-seat-text">
                <?php if ( $type ) : ?><div class="tl-label tl-nowrap"><?php echo $type; ?></div><?php endif; ?>
                <?php if ( $row || $seat ) : ?>
                  <div class="tl-sub tl-vertical">
                    <div><strong><?php esc_html_e('Seat:','fooevents-pdf-tickets'); ?></strong> <?php echo $seat ? esc_html($seat) : '-'; ?></div>
                    <div><strong><?php esc_html_e('Row:','fooevents-pdf-tickets'); ?></strong>  <?php echo $row  ? esc_html($row)  : '-'; ?></div>
                  </div>
                <?php endif; ?>
              </div>
            </div>

            <div class="tl-actions">
              <a href="<?php echo esc_url( $ticket_html_path ); ?>"
                 class="kt-btn kt-btn--primary"
                 onclick="window.open('<?php echo esc_url( $ticket_html_path ); ?>','TicketPopup','width=800,height=600,resizable=yes,scrollbars=yes'); return false;">
                 <?php esc_html_e('View Ticket','fooevents-pdf-tickets'); ?>
              </a>
              <a href="<?php echo esc_url( $ticket_path ); ?>" class="kt-btn kt-btn--ghost" target="_blank">
                 <?php esc_html_e('Download','fooevents-pdf-tickets'); ?>
              </a>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
<?php
$g_index++;
endforeach;
?>

<style>
/* ================== TICKETS: FULL CSS (Accordion + Beauty + Mobile) ================== */
:root{
  --kueue-red:#ff3131; --kueue-red2:#ff6a4a;
  --ink:#111; --muted:#7a7a7a;
  --card:#ffffff; --soft:#fcfcfc; --stroke:#ececec;
  --chip:rgba(255,49,49,.08);
}

/* ---- Accordion host ---- */
.booking-accordion{ position:relative; }

/* Toggle (round button) */
.acc-toggle{
  position:absolute; top:10px; right:10px; z-index:3;
  width:36px; height:36px; border-radius:50%;
  background:#fff; border:0; cursor:pointer;
  display:inline-flex; align-items:center; justify-content:center;
  box-shadow:0 8px 18px rgba(255,49,49,.18);
  transition:transform .15s ease, box-shadow .15s ease, background .15s ease;
}
.acc-toggle::after{
  content:""; position:absolute; inset:-6px; border-radius:50%;
  background: radial-gradient(closest-side, rgba(255,49,49,.08), transparent 70%);
  opacity:0; transition:opacity .2s ease;
}
.acc-toggle:hover{ transform:translateY(-1px); box-shadow:0 10px 22px rgba(255,49,49,.23); }
.acc-toggle:hover::after{ opacity:1; }

.acc-chev{
  width:11px; height:11px; display:block;
  border-right:2px solid #ff4b2b; border-bottom:2px solid #ff4b2b;
  transform:rotate(-45deg); transition:transform .22s cubic-bezier(.2,.9,.2,1);
}
.booking-accordion.is-open .acc-chev{ transform:rotate(135deg); }

/* Collapsible panel: 0fr -> 1fr */
.acc-panel{ display:grid; grid-template-rows:0fr; transition:grid-template-rows .25s ease; }
.acc-panel > .acc-inner{ overflow:hidden; }

/* ---- Outer Card ---- */
.booking-card{
  background: radial-gradient(120% 100% at 0% 0%, #fff 0%, #fff 55%, #fefefe 100%);
  border:2px dashed var(--stroke);
  border-radius:14px;
  padding:14px 16px 18px;
  margin:22px 0;
  box-shadow:0 14px 36px rgba(0,0,0,.06), 0 2px 6px rgba(0,0,0,.04);
  overflow:hidden;
}
.booking-card::before, .booking-card::after{
  content:""; position:absolute; top:50%; transform:translateY(-50%);
  width:18px; height:18px; border-radius:50%; background:#fff;
  box-shadow:0 0 0 2px var(--stroke) inset; z-index:1;
}
.booking-card::before{ left:-9px; } .booking-card::after{ right:-9px; }

/* ---- Header (Name | Event | Category | Booking No.) ---- */
.booking-head.head-4{
  display:grid;
  grid-template-columns: 1fr 8px 1fr 8px 1fr 8px auto;
  align-items:start;
  gap:12px 10px;
  padding:8px 54px 14px 8px; /* space for toggle */
  border-bottom:2px dashed #f1f1f1;
}
.bh-sep{ height:100%; border-left:3px solid #efefef; border-radius:8px; opacity:.8; }
.bh-cell{ min-width:0; }
.bh-right{ text-align:right; }
.bh-label{ font-size:12px; color:#8c8c8c; margin-bottom:3px; letter-spacing:.01em; }
.bh-value{ font-size:22px; font-weight:700; color:var(--ink); line-height:1.2; word-break:break-word; }
.bh-accent{ color:#ff3535; }
.bh-right .bh-value{ font-size:24px; }
.bh-badge{
  display:inline-block; margin-top:6px; padding:4px 8px; font-size:12px; font-weight:700;
  color:#ff3131; background:var(--chip); border-radius:999px;
}

/* ---- Tickets list ---- */
.booking-list{ padding:12px 2px 4px; }

/* Row card */
.ticket-line{
  display:grid;
  grid-template-columns: minmax(220px,1fr) minmax(260px,1fr) auto;
  align-items:center; gap:18px;
  padding:14px 12px;
  background: linear-gradient(180deg, #fff, #fff) padding-box,
              radial-gradient(100% 140% at 0% 0%, rgba(255,49,49,.06), rgba(255,49,49,0) 55%) border-box;
  border: 1px solid #f0f0f0;
  border-radius:10px;
  box-shadow: inset 0 1px 0 #fff, 0 6px 18px rgba(0,0,0,.04);
  transition: transform .15s ease, box-shadow .15s ease;
}
.ticket-line:hover{ transform:translateY(-1px); box-shadow: inset 0 1px 0 #fff, 0 10px 24px rgba(0,0,0,.06); }
.ticket-line.has-sep{ margin-top:12px; position:relative; }
.ticket-line.has-sep::before{
  content:""; position:absolute; top:-10px; left:12px; right:12px;
  height:0; border-top:2px dashed #efefef;
}

/* Left area */
.tl-left{ display:flex; align-items:center; gap:12px; min-width:0; }
.tl-thumb{ width:64px; height:64px; border-radius:8px; object-fit:cover; box-shadow: inset 0 0 0 2px #e7e7e7; background:#f2f2f2; }
.tl-thumb--ph{ background: linear-gradient(135deg, #f2f2f2, #f9f9f9); }
.tl-title{ font-size:22px; font-weight:800; color:#222; }

/* Middle area */
.tl-mid{ display:flex; flex-direction:column; gap:10px; }
.tl-meta{ display:flex; align-items:flex-start; gap:10px; }
.tl-ico{ width:22px; height:22px; object-fit:contain; flex:0 0 auto; opacity:.9; filter:saturate(.9); margin-top:2px; }
.tl-text{ display:flex; flex-direction:column; gap:3px; }
.tl-label{ font-size:17px; font-weight:700; color:#222; }
.tl-sub{ font-size:14px; color:#909090; }

/* Right area */
.tl-right{ display:flex; align-items:center; gap:16px; min-width:340px; }
.tl-seat{ display:flex; align-items:flex-start; gap:10px; }
.tl-seat-text .tl-label{ font-size:19px; }
.tl-nowrap{ white-space:nowrap; }
.tl-sub.tl-vertical{ display:flex; flex-direction:column; gap:2px; }
.tl-sub.tl-vertical > div strong{ color:#6f6f6f; }

/* Actions */
.tl-actions{ display:flex; gap:10px; flex-wrap:nowrap; margin-left:auto; }
.kt-btn{
  --radius:28px;
  display:inline-flex; align-items:center; justify-content:center;
  padding:11px 20px; border-radius:var(--radius);
  font-weight:800; font-size:13px; line-height:1; white-space:nowrap;
  text-decoration:none;
  transition:transform .15s ease, box-shadow .15s ease, opacity .15s ease, background .15s ease, color .15s ease;
}
.kt-btn--primary{
  color:#fff; background:linear-gradient(90deg, var(--kueue-red), var(--kueue-red2));
  box-shadow:0 10px 24px rgba(255,73,60,.28);
}
.kt-btn--primary:hover{ opacity:.96; transform:translateY(-1px); box-shadow:0 12px 28px rgba(255,73,60,.34); }
.kt-btn--ghost{
  color:#ff4b2b; background:#fff; box-shadow: inset 0 0 0 2px #ff4b2b;
}
.kt-btn--ghost:hover{
  color:#fff; background:linear-gradient(90deg, var(--kueue-red), var(--kueue-red2));
  box-shadow:0 8px 18px rgba(255,73,60,.18), inset 0 0 0 0 transparent;
}

/* ---- Responsive ---- */
@media (max-width: 1100px){
  .ticket-line{ grid-template-columns: 1fr 1fr; }
  .tl-right{ grid-column: 1 / -1; justify-content:flex-start; min-width:0; }
  .tl-actions{ margin-left:0; }
}

@media (max-width: 760px){
  .booking-card{ padding:12px; border-radius:12px; }
  .booking-head.head-4{ grid-template-columns:1fr; gap:6px; padding-right:54px; }
  .bh-sep{ display:none; }
  .bh-right{ text-align:left; }
  .acc-toggle{ top:8px; right:8px; }

  .ticket-line{ grid-template-columns:1fr; padding:12px; gap:12px; border-radius:12px; }
  .tl-left{ align-items:flex-start; }
  .tl-thumb{ width:56px; height:56px; }
  .tl-title{ font-size:20px; word-break:break-word; }

  .tl-right{ min-width:0; flex-direction:column; align-items:flex-start; gap:10px; }
  .tl-actions{ width:100%; gap:10px; margin-left:0; }
  .tl-actions .kt-btn{ flex:1 1 50%; padding:12px 14px; }

  .booking-card::before, .booking-card::after{ width:14px; height:14px; }
  .booking-card::before{ left:-7px; } .booking-card::after{ right:-7px; }
}

/* Reduced motion */
@media (prefers-reduced-motion: reduce){
  .acc-toggle, .ticket-line, .acc-chev, .acc-panel{ transition:none !important; }
}
</style>

<script>
/* ================== TICKETS: FULL JS (Accordion + State memory) ================== */
(function(){
  const KEY = 'kueueTicketsOpen';

  function setOpen(card, open){
    const btn = card.querySelector('.acc-toggle');
    card.classList.toggle('is-open', open);
    if (btn) btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    const panel = card.querySelector('.acc-panel');
    if (panel) panel.style.gridTemplateRows = open ? '1fr' : '0fr';
  }

  // Click + keyboard toggle
  document.addEventListener('click', (e)=>{
    const btn = e.target.closest('.acc-toggle');
    if(!btn) return;
    const card = btn.closest('.booking-accordion');
    const open = !card.classList.contains('is-open');
    setOpen(card, open);

    // persist
    const id = card.getAttribute('data-acc');
    const map = JSON.parse(localStorage.getItem(KEY) || '{}');
    map[id] = open;
    localStorage.setItem(KEY, JSON.stringify(map));
  });

  document.addEventListener('keydown', (e)=>{
    const btn = e.target.closest && e.target.closest('.acc-toggle');
    if(!btn) return;
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      btn.click();
    }
  });

  // Restore saved states; default-open first if none
  const saved = JSON.parse(localStorage.getItem(KEY) || '{}');
  const cards = document.querySelectorAll('.booking-accordion');
  if (cards.length){
    let anyOpen = false;
    cards.forEach(card=>{
      const id = card.getAttribute('data-acc');
      const open = !!saved[id];
      if (open) anyOpen = true;
      setOpen(card, open);
    });
    if (!anyOpen) setOpen(cards[0], true); // open first by default
  }
})();
</script>

