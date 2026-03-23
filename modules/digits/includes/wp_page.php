<?php

if (!defined('ABSPATH')) {
    exit;
}


add_action('digits_activation_hooks', 'digits_create_default_pages');

function digits_create_default_pages()
{
    $page_title = 'Account Security';
    $page_content = '[df-account-manage]';
    $page_check = get_option('digits_manage_account_page_id', 0);

    if (empty($page_check)) {
        $page_id = wp_insert_post([
            'post_title' => $page_title,
            'post_content' => $page_content,
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_name' => 'account-security',
        ]);
        update_option('digits_manage_account_page_id', $page_id);
    }
}

add_filter('display_post_states', function ($post_states, $post) {
    $manage_account_page = get_option('digits_manage_account_page_id', 0);
    if ($manage_account_page == $post->ID) {
        $post_states['digits'] = __('Digits Account Security Page', 'digits');
    }
    return $post_states;
}, 10, 2);