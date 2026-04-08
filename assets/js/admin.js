/**
 * Kueue Events Admin Interactions
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        
        // Handle Payout Approval
        $(document).on('click', '.kq-approve-payout', function(e) {
            e.preventDefault();
            const $btn = $(this);
            const id = $btn.data('id');

            if (!confirm('Mark this payout as PAID?')) return;

            $btn.prop('disabled', true).text('Updating...');

            $.post(kq_admin.ajax_url, {
                action: 'kq_process_payout',
                nonce: kq_admin.nonce,
                id: id,
                status: 'paid'
            }, function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert(response.data.message || 'Error processing payout');
                    $btn.prop('disabled', false).text('Approve');
                }
            });
        });

        // Handle Payout Rejection
        $(document).on('click', '.kq-reject-payout', function(e) {
            e.preventDefault();
            const $btn = $(this);
            const id = $btn.data('id');

            if (!confirm('REJECT this payout request?')) return;

            $btn.prop('disabled', true).text('Updating...');

            $.post(kq_admin.ajax_url, {
                action: 'kq_process_payout',
                nonce: kq_admin.nonce,
                id: id,
                status: 'rejected'
            }, function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert(response.data.message || 'Error rejecting payout');
                    $btn.prop('disabled', false).text('Reject');
                }
            });
        });

    });

})(jQuery);
