<?php
/**
 * Plugin Name: WPNotif – Ticket Link/Location Placeholders (Wait for Ticket File)
 * Description: Forces WPNotif to wait until ticket HTML/PDF is generated before sending links.
 * Version:     1.4.1
 */

if (!defined('ABSPATH')) { exit; }

if (!defined('KUEUE_TICKETS_DIR')) {
    define('KUEUE_TICKETS_DIR', trailingslashit(ABSPATH . 'app/tickets'));
}
if (!defined('KUEUE_TICKETS_URL')) {
    define('KUEUE_TICKETS_URL', trailingslashit(home_url('/app/tickets')));
}

/* Strict wait config */
if (!defined('KUEUE_TICKETS_WAIT_MAX_MS'))  define('KUEUE_TICKETS_WAIT_MAX_MS', 20000); // total 20s
if (!defined('KUEUE_TICKETS_WAIT_STEP_MS')) define('KUEUE_TICKETS_WAIT_STEP_MS', 300);  // step 300ms

function kueue_build_ticket_basename($first, $last, $ticket_id){
    $first = trim((string)$first);
    $last  = trim((string)$last);
    $id    = trim((string)$ticket_id);
    if ($id === '') return '';
    $raw = ($first === '' && $last === '') ? ($id.'-'.$id) : ($first.'-'.$last.'-'.$id);
    $raw = preg_replace('/\s+/', '-', $raw);
    return sanitize_file_name($raw);
}
function kueue_ticket_file_exists($base){
    return file_exists(KUEUE_TICKETS_DIR . $base . '.html');
}
function kueue_debug($msg){
    if (apply_filters('kueue_wpnotif_debug_log', false)) {
        error_log('[KUEUE-WPNotif] ' . (is_string($msg) ? $msg : wp_json_encode($msg)));
    }
}

/* Wait until HTML or PDF exists */
function kueue_wait_until_ticket_ready($base){
    $elapsed = 0;
    $html_fs = KUEUE_TICKETS_DIR . $base . '.html';
    $pdf_fs  = KUEUE_TICKETS_DIR . $base . '.pdf';
    while ($elapsed < KUEUE_TICKETS_WAIT_MAX_MS) {
        clearstatcache();
        if (file_exists($html_fs) || file_exists($pdf_fs)) return true;
        usleep(KUEUE_TICKETS_WAIT_STEP_MS * 1000);
        $elapsed += KUEUE_TICKETS_WAIT_STEP_MS;
    }
    return false;
}

/* Resolve link only if file exists; otherwise return empty string */
function kueue_resolve_ticket_view_link($base){
    $html_fs = KUEUE_TICKETS_DIR . $base . '.html';
    $pdf_fs  = KUEUE_TICKETS_DIR . $base . '.pdf';
    $html_url= KUEUE_TICKETS_URL . $base;          // no extension
    $pdf_url = KUEUE_TICKETS_URL . $base . '.pdf';

    if (file_exists($html_fs)) return $html_url;
    if (file_exists($pdf_fs))  return $pdf_url;

    if (kueue_wait_until_ticket_ready($base)) {
        return file_exists($html_fs) ? $html_url : (file_exists($pdf_fs) ? $pdf_url : '');
    }
    return '';
}

function kueue_ticket_data_from_order($order){
    $out = ['links'=>[], 'files'=>[], 'locations'=>[]];
    if (!($order instanceof WC_Order)) return $out;

    $order_id = (int) $order->get_id();
    $order_no = method_exists($order, 'get_order_number') ? $order->get_order_number() : $order_id;

    $post_types = apply_filters('kueue_wpnotif_ticket_post_types', [
        'event_magic_tickets','fooevents_ticket','wcf_event_ticket'
    ]);

    $tps = get_posts([
        'post_type'      => $post_types,
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'meta_query'     => [
            'relation' => 'OR',
            ['key'=>'WooCommerceEventsOrderID','value'=>$order_id],
            ['key'=>'WooCommerceEventsOrderId','value'=>$order_id],
            ['key'=>'WooCommerceEventsOrderid','value'=>$order_id],
            ['key'=>'WooCommerceEventsOrderNumber','value'=>$order_no],
        ],
        'orderby'        => 'ID',
        'order'          => 'ASC',
    ]);

    $require_exist = true; // hard enforce

    foreach ((array)$tps as $tp_id){
        $tid   = get_post_meta($tp_id,'WooCommerceEventsTicketID',true);
        $fname = get_post_meta($tp_id,'WooCommerceEventsAttendeeName',true);
        $lname = get_post_meta($tp_id,'WooCommerceEventsAttendeeLastName',true);

        $loc = trim((string)get_post_meta($tp_id,'WooCommerceEventsLocation',true));
        if ($loc!=='') $out['locations'][] = $loc;

        $pid = (int)get_post_meta($tp_id,'WooCommerceEventsProductID',true);
        if ($pid){
            $ploc = trim((string)get_post_meta($pid,'WooCommerceEventsLocation',true));
            if ($ploc!=='') $out['locations'][] = $ploc;
        }

        $base = kueue_build_ticket_basename($fname,$lname,$tid);
        if ($base==='') continue;

        $link = kueue_resolve_ticket_view_link($base);
        if (!$require_exist || $link !== '') {
            $out['files'][] = $base . '.html';
            if ($link !== '') $out['links'][] = $link;
        }
    }

    if (empty($out['links']) || empty($out['locations'])){
        if (method_exists($order,'get_items')){
            foreach ($order->get_items() as $item){
                if (!is_object($item)) continue;

                $tid   = $item->get_meta('WooCommerceEventsTicketID', true);
                $fname = $item->get_meta('WooCommerceEventsAttendeeName', true);
                $lname = $item->get_meta('WooCommerceEventsAttendeeLastName', true);

                $loc = trim((string)$item->get_meta('WooCommerceEventsLocation', true));
                if ($loc!=='') $out['locations'][] = $loc;

                $pid = method_exists($item,'get_product_id') ? (int)$item->get_product_id() : 0;
                if ($pid){
                    $ploc = trim((string)get_post_meta($pid,'WooCommerceEventsLocation',true));
                    if ($ploc!=='') $out['locations'][] = $ploc;
                }

                $base = kueue_build_ticket_basename($fname,$lname,$tid);
                if ($base==='') continue;

                $link = kueue_resolve_ticket_view_link($base);
                if (!$require_exist || $link !== '') {
                    $out['files'][] = $base . '.html';
                    if ($link !== '') $out['links'][] = $link;
                }
            }
        }
    }

    foreach ($out as $k=>$v){
        $out[$k] = array_values(array_unique(array_filter(array_map('trim',$v))));
    }
    return $out;
}

foreach (['wpnotif_custom_placeholders','wpnotif_additional_placeholders','wpnotif_placeholders_list'] as $reg_filter){
    add_filter($reg_filter, function($arr){
        if (!is_array($arr)) $arr=[];
        $arr['ticket-link']        = 'Ticket Link';
        $arr['ticket_link']        = 'Ticket Link';
        $arr['ticket-link-first']  = 'Ticket Link (first)';
        $arr['ticket-location']    = 'Ticket Location';
        $arr['ticket_ready']       = 'yes/no – link ready';
        return $arr;
    });
}

add_filter('wpnotif_placeholder_args', function($value, $placeholder, $msg, $order){
    $ph = str_replace('_','-', strtolower((string)$placeholder));
    if (!($order instanceof WC_Order)) return $value;

    $data = kueue_ticket_data_from_order($order);
    switch ($ph){
        case 'ticket-link':       return !empty($data['links']) ? implode(', ', $data['links']) : '';
        case 'ticket-link-first': return !empty($data['links']) ? $data['links'][0] : '';
        case 'ticket-location':   return !empty($data['locations']) ? implode(', ', $data['locations']) : '';
        case 'ticket-ready':      return !empty($data['links']) ? 'yes' : 'no';
    }
    return $value;
}, 10, 4);

function kueue_force_replace_message($text, ...$args){
    if (!is_string($text) || $text==='') return $text;

    $order = null;
    foreach ($args as $a){ if ($a instanceof WC_Order){ $order=$a; break; } }
    if (!$order) return $text;

    $data = kueue_ticket_data_from_order($order);
    $link = !empty($data['links']) ? $data['links'][0] : '';
    $loc  = !empty($data['locations']) ? $data['locations'][0] : '';

    $map = [
        '{{ticket-link}}'      => $link,
        '{{ticket_link}}'      => $link,
        '{{wc-ticket-link}}'   => $link,
        '{{ticket-location}}'  => $loc,
        '{{ticket_location}}'  => $loc,
    ];
    return strtr($text, $map);
}
foreach ([
    'wpnotif_message_before_send',
    'wpnotif_whatsapp_message',
    'wpnotif_sms_message',
    'wpnotif_message_body',
    'wpnotif_process_message',
] as $f){
    add_filter($f, 'kueue_force_replace_message', 9, 20);
}

/* Hard enforce: require actual file existence */
add_filter('kueue_wpnotif_require_ticket_file', '__return_true');
