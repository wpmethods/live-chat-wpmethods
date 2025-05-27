<?php
namespace LC_WPMethods;
if (!defined('ABSPATH')) exit;

class License_Lcw {

    private $option_key      = 'wpmlc_license_key';
    private $status_key      = 'wpmlc_license_status';
    private $token_key       = 'wpmlc_activation_token';
    private $plugin_slug     = 'lc-wpmethods-license';
    private $text_domain     = 'lc-wpmethods';

    private $api_url         = 'https://wpmethods.com/wp-json/lmfwc/v2/licenses/';
    private $consumer_key    = 'ck_2e0a2a0d50741489c4d2fe41678e205e7fce55ac';
    private $consumer_secret = 'cs_7b5a7a347e13b6e815f2ab0e4721bfcef5e5289a';

    public function __construct() {
        add_action('admin_menu', [$this, 'add_license_page'], 20);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_post_wpmlc_verify_license', [$this, 'verify_license']);
        add_action('admin_post_wpmlc_deactivate_license', [$this, 'deactivate_license']);
    }

    public function add_license_page() {
        add_submenu_page(
            'lc-wpmethods-settings',
            __('Live Chat License', $this->text_domain),
            __('Active License', $this->text_domain),
            'manage_options',
            $this->plugin_slug,
            [$this, 'render_license_page']
        );
    }

    public function register_settings() {
        register_setting('wpmlc_license_settings', $this->option_key);

        add_settings_section(
            'wpmlc_license_section',
            __('License Activation', $this->text_domain),
            '__return_false',
            $this->plugin_slug
        );

        add_settings_field(
            $this->option_key,
            __('License Key', $this->text_domain),
            [$this, 'license_key_field'],
            $this->plugin_slug,
            'wpmlc_license_section'
        );
    }

    public function license_key_field() {
        $key = get_option($this->option_key, '');
        echo '<input type="password" name="' . esc_attr($this->option_key) . '" value="' . esc_attr($key) . '" style="width: 400px;">';
    }

    public function render_license_page() {
        $license_key = get_option($this->option_key, '');
        $license_status = get_option($this->status_key, 'inactive');
        ?>
        <div class="wrap">
            
            <div style="background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1); max-width: 600px;">
                <form method="post" action="options.php">
                    <?php
                    settings_fields('wpmlc_license_settings');
                    do_settings_sections($this->plugin_slug);
                    submit_button(__('Save License Key', $this->text_domain));
                    ?>
                </form>

                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="wpmlc_verify_license">
                    <?php wp_nonce_field('wpmlc_verify_license_nonce', 'wpmlc_nonce'); ?>
                    <input type="password" name="license_key" value="<?php echo esc_attr($license_key); ?>" placeholder="Enter License Key" style="width: 100%; margin-top: 10px;">
                    <button type="submit" class="button button-primary" style="margin-top: 10px;"><?php esc_html_e('Verify License', $this->text_domain); ?></button>
                </form>

                <hr style="margin: 20px 0;">

                <?php if ($license_status === 'active') : ?>
                    <p style="color: green; font-weight: bold;">✔ <?php esc_html_e('License Activated', $this->text_domain); ?></p>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <input type="hidden" name="action" value="wpmlc_deactivate_license">
                        <?php wp_nonce_field('wpmlc_deactivate_license_nonce', 'wpmlc_nonce'); ?>
                        <button type="submit" class="button" style="background: #dc3545; color: #fff;"><?php esc_html_e('Deactivate License', $this->text_domain); ?></button>
                    </form>
                <?php else : ?>
                    <p style="color: red; font-weight: bold;">✖ <?php esc_html_e('License Not Activated', $this->text_domain); ?></p>
                    <h4>🛒 <a href="https://wpmethods.com/social-floating-icon" target="_blank">Click here</a> to buy the licnese key </h4>
                <?php endif; ?>
                
            </div>

        </div>
        <?php
    }

    public function verify_license() {
        
        if (!current_user_can('manage_options') || !check_admin_referer('wpmlc_verify_license_nonce', 'wpmlc_nonce')) {
            wp_die(__('Unauthorized request', $this->text_domain));
        }

        $license_key = sanitize_text_field($_POST['license_key'] ?? '');

        if (!$license_key) {
            wp_redirect(admin_url("admin.php?page={$this->plugin_slug}&error=empty_key"));
            exit;
        }

        update_option($this->option_key, $license_key);

        // Step 1: Validate the license key
        $validation_response = wp_remote_get($this->api_url . $license_key, [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($this->consumer_key . ':' . $this->consumer_secret),
                'Content-Type'  => 'application/json',
            ],
        ]);

        if (is_wp_error($validation_response)) {
            wp_redirect(admin_url("admin.php?page={$this->plugin_slug}&error=validation_api_error"));
            exit;
        }

        $data = json_decode(wp_remote_retrieve_body($validation_response), true);


        // Step 2: Check success and productId
        if ($data['data']['productId'] != 11677) {
            update_option($this->status_key, 'inactive');
            wp_redirect(admin_url("admin.php?page={$this->plugin_slug}&error=invalid_product"));
            exit;
        }

        // Step 3: Call activate only if validated
        $activation_response = wp_remote_get($this->api_url . 'activate/' . $license_key, [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($this->consumer_key . ':' . $this->consumer_secret),
                'Content-Type'  => 'application/json',
            ],
        ]);

        if (is_wp_error($activation_response)) {
            wp_redirect(admin_url("admin.php?page={$this->plugin_slug}&error=activation_api_error"));
            exit;
        }

        $activation_data = json_decode(wp_remote_retrieve_body($activation_response), true);

        if (!empty($activation_data['success']) && $activation_data['data']['licenseKey'] === $license_key) {
            update_option($this->status_key, 'active');
            if (isset($activation_data['data']['activationData']['token'])) {
                update_option($this->token_key, $activation_data['data']['activationData']['token']);
            }
            wp_redirect(admin_url("admin.php?page={$this->plugin_slug}&success=activated"));
        } else {
            update_option($this->status_key, 'inactive');
            wp_redirect(admin_url("admin.php?page={$this->plugin_slug}&error=" . urlencode($activation_data['message'] ?? 'activation_failed')));
        }
        exit;
    }


    public function deactivate_license() {
        if (!current_user_can('manage_options') || !check_admin_referer('wpmlc_deactivate_license_nonce', 'wpmlc_nonce')) {
            wp_die(__('Unauthorized request', $this->text_domain));
        }

        $license_key = get_option($this->option_key);
        $token = get_option($this->token_key);

        if (!$license_key || !$token) {
            wp_redirect(admin_url("admin.php?page={$this->plugin_slug}&error=missing_data"));
            exit;
        }

        $response = wp_remote_get($this->api_url . 'deactivate/' . $license_key, [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($this->consumer_key . ':' . $this->consumer_secret),
                'Content-Type'  => 'application/json',
            ],
            'body' => ['token' => $token],
        ]);

        if (is_wp_error($response)) {
            wp_redirect(admin_url("admin.php?page={$this->plugin_slug}&error=api_error"));
            exit;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if (!empty($data['success'])) {
            update_option($this->status_key, 'inactive');
            delete_option($this->option_key);
            delete_option($this->token_key);
            wp_redirect(admin_url("admin.php?page={$this->plugin_slug}&success=deactivated"));
        } else {
            wp_redirect(admin_url("admin.php?page={$this->plugin_slug}&error=" . urlencode($data['message'] ?? 'unknown')));
        }
        exit;
    }
}