<div class="kq-dashboard-container" style="max-width: 1200px; margin: 40px auto; padding: 0 20px;">
    
    <!-- Header Section -->
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 40px;">
        <div>
            <h2 style="font-size: 32px; font-weight: 800; margin-bottom: 8px;">Welcome, <?php echo esc_html( $organizer->organizer_name ?? 'Organizer' ); ?></h2>
            <p style="color: #888;">Manage your events, sales, and payouts in one central hub.</p>
        </div>
        <div>
            <a href="<?php echo admin_url('post-new.php?post_type=kq_event'); ?>" class="kq-btn kq-btn-primary" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fa fa-plus"></i> <?php _e( 'Create New Event', 'kueue-events-core' ); ?>
            </a>
        </div>
    </div>

    <!-- Bento Stats Grid -->
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 40px;">
        <div class="kq-card">
            <span style="display: block; font-size: 13px; color: #888; font-weight: 700; text-transform: uppercase; margin-bottom: 10px;">Gross Revenue</span>
            <span style="font-size: 28px; font-weight: 800;"><?php echo kq_price( $stats->gross ?? 0 ); ?></span>
        </div>
        <div class="kq-card">
            <span style="display: block; font-size: 13px; color: #888; font-weight: 700; text-transform: uppercase; margin-bottom: 10px;">Commission Fee</span>
            <span style="font-size: 28px; font-weight: 800; color: #ff3131;">-<?php echo kq_price( $stats->commission ?? 0 ); ?></span>
        </div>
        <div class="kq-card">
            <span style="display: block; font-size: 13px; color: #888; font-weight: 700; text-transform: uppercase; margin-bottom: 10px;">Net Earnings</span>
            <span style="font-size: 28px; font-weight: 800; color: #4cd137;"><?php echo kq_price( $stats->net ?? 0 ); ?></span>
        </div>
    </div>

    <!-- Main Grid -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
        
        <div class="kq-main-column">
            <!-- My Events -->
            <div class="kq-card" style="margin-bottom: 30px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                    <h3 style="margin:0;">My Events</h3>
                    <a href="<?php echo admin_url('edit.php?post_type=kq_event'); ?>" class="kq-link" style="font-size: 13px; font-weight: 700;">Manage All</a>
                </div>
                
                <div class="kq-table-wrapper">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="text-align: left; background: #fafafa; border-bottom: 1px solid #eee;">
                                <th style="padding: 15px; font-size: 12px; color: #888;">EVENT</th>
                                <th style="padding: 15px; font-size: 12px; color: #888;">DATE</th>
                                <th style="padding: 15px; font-size: 12px; color: #888;">SOLD</th>
                                <th style="padding: 15px; font-size: 12px; color: #888;">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if ($events) : foreach ($events as $ev) : 
                                $summary = \KueueEvents\Core\Modules\Reports\ReportsService::get_event_summary($ev->ID);
                                $status = get_post_meta($ev->ID, '_kq_event_status', true) ?: 'draft';
                            ?>
                            <tr style="border-bottom: 1px solid #f9f9f9;">
                                <td style="padding: 15px;">
                                    <strong><?php echo esc_html($ev->post_title); ?></strong>
                                    <span style="display:block; font-size:11px; text-transform: uppercase;" class="status-badge status-<?php echo $status; ?>"><?php echo $status; ?></span>
                                </td>
                                <td style="padding: 15px; font-size: 14px;"><?php echo get_post_meta($ev->ID, '_kq_start_date', true); ?></td>
                                <td style="padding: 15px; font-weight: 700;"><?php echo $summary['active_tickets']; ?></td>
                                <td style="padding: 15px;">
                                    <a href="<?php echo get_edit_post_link($ev->ID); ?>" class="kq-btn-icon" title="Edit"><i class="fa fa-edit"></i></a>
                                    <a href="<?php echo admin_url('admin.php?page=kq-attendees&event_id='.$ev->ID); ?>" class="kq-btn-icon" title="Attendees"><i class="fa fa-users"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; else : ?>
                            <tr><td colspan="4" style="padding: 30px; text-align: center; color: #888;">No events found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Tickets -->
            <div class="kq-card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                    <h3 style="margin:0;">Recent Tickets</h3>
                </div>
                
                <div class="kq-table-wrapper">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="text-align: left; background: #fafafa; border-bottom: 1px solid #eee;">
                                <th style="padding: 15px; font-size: 12px; color: #888;">TICKET</th>
                                <th style="padding: 15px; font-size: 12px; color: #888;">ATTENDEE</th>
                                <th style="padding: 15px; font-size: 12px; color: #888;">STATUS</th>
                                <th style="padding: 15px; font-size: 12px; color: #888;">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $recent_tickets = \KueueEvents\Core\Modules\Tickets\TicketRepository::get_paged( 1, 10, $organizer->id );
                            if ($recent_tickets) : foreach ($recent_tickets as $t) : 
                                $att = \KueueEvents\Core\Modules\Attendees\AttendeeRepository::get_by_id($t->attendee_id);
                            ?>
                            <tr style="border-bottom: 1px solid #f9f9f9;">
                                <td style="padding: 15px;"><code><?php echo esc_html($t->ticket_number); ?></code></td>
                                <td style="padding: 15px;"><?php echo esc_html($att->first_name . ' ' . $att->last_name); ?></td>
                                <td style="padding: 15px;">
                                    <span class="kq-badge kq-badge-<?php echo esc_attr($t->ticket_status); ?>">
                                        <?php echo esc_html(ucfirst($t->ticket_status)); ?>
                                    </span>
                                </td>
                                <td style="padding: 15px;">
                                    <?php if ($t->ticket_status === 'active') : ?>
                                    <button class="kq-btn-icon kq-resend-ticket" data-id="<?php echo $t->id; ?>" title="Resend"><i class="fa fa-paper-plane"></i></button>
                                    <button class="kq-btn-icon kq-cancel-ticket" data-id="<?php echo $t->id; ?>" title="Cancel" style="color:#ff3131;"><i class="fa fa-times-circle"></i></button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; else : ?>
                            <tr><td colspan="4" style="padding: 30px; text-align: center; color: #888;">No tickets issued yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="kq-sidebar-column">
            <!-- Payouts Card -->
            <div class="kq-card" style="background: var(--kq-dark); color: #fff; margin-bottom: 30px;">
                <h3 style="margin-top:0; color: #fff;">Withdrawals</h3>
                <p style="color: #666; font-size: 14px; margin-bottom: 24px;">Manage your earnings and payout history.</p>
                
                <div class="payout-list" style="margin-bottom: 20px;">
                    <?php if (!empty($payouts)) : foreach ($payouts as $p) : ?>
                    <div style="background: #1a1a1c; padding: 15px; border-radius: 12px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong style="display:block; font-size:16px;"><?php echo kq_price($p->amount); ?></strong>
                            <span style="font-size:11px; color:#555;"><?php echo date('M d, Y', strtotime($p->created_at)); ?></span>
                        </div>
                        <span style="font-size: 10px; text-transform: uppercase; letter-spacing: 1px; padding: 4px 8px; border-radius: 4px; background: <?php echo $p->status === 'completed' ? '#4cd137' : '#ffa502'; ?>; color: #000;">
                            <?php echo $p->status; ?>
                        </span>
                    </div>
                    <?php endforeach; else : ?>
                    <p style="text-align: center; color: #444; font-size: 13px; border: 1px dashed #333; padding: 20px; border-radius: 12px;">No payout history.</p>
                    <?php endif; ?>
                </div>

                <button class="kq-btn kq-btn-primary" id="kq-request-payout" style="width: 100%; border:none; cursor:pointer;">
                    <?php _e( 'Request Payout', 'kueue-events-core' ); ?>
                </button>
            </div>
            
            <!-- Quick Actions -->
            <div class="kq-card">
                <h4 style="margin-top:0;">Quick Links</h4>
                <ul style="list-style: none; padding:0; margin:0;">
                    <li style="margin-bottom:10px;"><a href="<?php echo admin_url('admin.php?page=kq-reports'); ?>" class="kq-link"><i class="fa fa-chart-line"></i> Full Reports</a></li>
                    <li style="margin-bottom:10px;"><a href="<?php echo admin_url('admin.php?page=kq-settings'); ?>" class="kq-link"><i class="fa fa-cog"></i> Account Settings</a></li>
                    <li><a href="<?php echo admin_url('admin.php?page=kq-pos'); ?>" class="kq-link"><i class="fa fa-cash-register"></i> Open POS</a></li>
                </ul>
            </div>
        </div>

    </div>
</div>
