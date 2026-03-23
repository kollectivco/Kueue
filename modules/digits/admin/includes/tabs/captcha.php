<?php

if (!defined('ABSPATH')) {
    exit;
}

function digits_settings_recaptcha()
{

    $recaptcha_site_key = get_option('digits_recaptcha_site_key', '');
    $recaptcha_secret_key = get_option('digits_recaptcha_secret_key', '');
    $recaptcha_type = get_option('digits_recaptcha_type', 'v3');

    $captcha_provider = get_option('digits_captcha_provider', 'recaptcha');
    if (empty($captcha_provider)) {
        $captcha_provider = 'recaptcha';
    }
    $turnstile_site_key = get_option('digits_turnstile_site_key', '');
    $turnstile_secret_key = get_option('digits_turnstile_secret_key', '');

    $hcaptcha_site_key = get_option('digits_hcaptcha_site_key', '');
    $hcaptcha_secret_key = get_option('digits_hcaptcha_secret_key', '');
    $hcaptcha_type = get_option('digits_hcaptcha_type', 'checkbox');
    ?>
    <div class="dig_admin_head">
        <span><?php _e('CAPTCHA', 'digits'); ?></span>
    </div>

    <div class="dig_admin_tab_grid">
        <div class="dig_admin_tab_grid_elem">
            <div class="dig_admin_section">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="digits_captcha_provider"><?php _e("Captcha Provider", "digits"); ?></label>
                        </th>
                        <td>
                            <select name="digits_captcha_provider" id="digits_captcha_provider">
                                <option value="recaptcha" <?php if ($captcha_provider == 'recaptcha') echo 'selected'; ?>>
                                    <?php esc_attr_e('Google reCAPTCHA', 'digits'); ?>
                                </option>
                                <option value="turnstile" <?php if ($captcha_provider == 'turnstile') echo 'selected'; ?>>
                                    <?php esc_attr_e('Cloudflare Turnstile', 'digits'); ?>
                                </option>
                                <option value="hcaptcha" <?php if ($captcha_provider == 'hcaptcha') echo 'selected'; ?>>
                                    <?php esc_attr_e('hCaptcha', 'digits'); ?>
                                </option>
                            </select>
                            <p class="description">
                                <?php esc_attr_e('Select which CAPTCHA to use across Digits forms.', 'digits'); ?>
                            </p>
                        </td>
                    </tr>
                </table>

                <div class="dig_admin_sec_head dig_admin_sec_head_margin">
                    <span><?php esc_attr_e('reCAPTCHA Settings', 'digits'); ?></span>
                </div>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="recaptcha_site_key"><?php _e("Site Key", "digits"); ?></label>
                        </th>
                        <td>
                            <input type="text" id="recaptcha_site_key" name="digits_recaptcha_site_key"
                                   class="regular-text"
                                   value="<?php echo esc_attr($recaptcha_site_key); ?>"/>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="recaptcha_secret_key"><?php _e("Secret Key", "digits"); ?></label>
                        </th>
                        <td>
                            <input type="text" id="recaptcha_secret_key" name="digits_recaptcha_secret_key"
                                   class="regular-text"
                                   value="<?php echo esc_attr($recaptcha_secret_key); ?>"/>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="recaptcha_type"><?php _e("reCAPTCHA Type", "digits"); ?></label>
                        </th>
                        <td>

                            <select name="digits_recaptcha_type" id="recaptcha_type">
                                <option value="v3" <?php if ($recaptcha_type == 'v3') echo 'selected'; ?>>
                                    <?php esc_attr_e('v3', 'digits'); ?>
                                </option>
                                <option value="checkbox" <?php if ($recaptcha_type == 'checkbox') echo 'selected'; ?>>
                                    <?php esc_attr_e('Checkbox (v2)', 'digits'); ?>
                                </option>
                                <option value="invisible" <?php if ($recaptcha_type == 'invisible') echo 'selected'; ?>>
                                    <?php esc_attr_e('Invisible (v2)', 'digits'); ?>
                                </option>
                            </select>
                        </td>
                    </tr>
                </table>

                <div class="dig_admin_sec_head dig_admin_sec_head_margin">
                    <span><?php esc_attr_e('Turnstile Settings', 'digits'); ?></span>
                </div>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="turnstile_site_key"><?php _e("Site Key", "digits"); ?></label>
                        </th>
                        <td>
                            <input type="text" id="turnstile_site_key" name="digits_turnstile_site_key"
                                   class="regular-text"
                                   value="<?php echo esc_attr($turnstile_site_key); ?>"/>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="turnstile_secret_key"><?php _e("Secret Key", "digits"); ?></label>
                        </th>
                        <td>
                            <input type="text" id="turnstile_secret_key" name="digits_turnstile_secret_key"
                                   class="regular-text"
                                   value="<?php echo esc_attr($turnstile_secret_key); ?>"/>
                            <p class="description">
                                <?php esc_attr_e('Create a Turnstile widget in Cloudflare and paste the keys here.', 'digits'); ?>
                            </p>
                        </td>
                    </tr>
                </table>

                <div class="dig_admin_sec_head dig_admin_sec_head_margin">
                    <span><?php esc_attr_e('hCaptcha Settings', 'digits'); ?></span>
                </div>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="hcaptcha_site_key"><?php _e("Site Key", "digits"); ?></label>
                        </th>
                        <td>
                            <input type="text" id="hcaptcha_site_key" name="digits_hcaptcha_site_key"
                                   class="regular-text"
                                   value="<?php echo esc_attr($hcaptcha_site_key); ?>"/>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="hcaptcha_secret_key"><?php _e("Secret Key", "digits"); ?></label>
                        </th>
                        <td>
                            <input type="text" id="hcaptcha_secret_key" name="digits_hcaptcha_secret_key"
                                   class="regular-text"
                                   value="<?php echo esc_attr($hcaptcha_secret_key); ?>"/>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="hcaptcha_type"><?php _e("hCaptcha Type", "digits"); ?></label>
                        </th>
                        <td>
                            <select name="digits_hcaptcha_type" id="hcaptcha_type">
                                <option value="checkbox" <?php if ($hcaptcha_type == 'checkbox') echo 'selected'; ?>>
                                    <?php esc_attr_e('Checkbox', 'digits'); ?>
                                </option>
                                <option value="invisible" <?php if ($hcaptcha_type == 'invisible') echo 'selected'; ?>>
                                    <?php esc_attr_e('Invisible', 'digits'); ?>
                                </option>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <?php
}
