<?php
namespace LC_WPMethods;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Front_End {

    public function __construct() {
        add_action('wp_footer', [$this, 'render_chat_buttons']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets() {
        $this->maybe_enqueue_fontawesome();
        wp_enqueue_style('lc-wpmethods-css', LC_WPMETHODS_URL . 'assets/css/front-end.css', [], '1.0');
        wp_enqueue_script('lc-wpmethods-js', LC_WPMETHODS_URL . 'assets/js/front-end.js', ['jquery'], '1.0', true);
    }

    public function render_chat_buttons()
    {
        // Load settings
        $options = get_option('lc_wpmethods_settings');
        $lc_wpmethods_links = $options['lc_wpmethods_links'] ?? []; // repeater field

        $toggle_color = $options['toggle_bg_color'] ?? '#00DA62';
        $icon_color = $options['icon_color'] ?? '#FFFFFF';
        $hover_color = $options['hover_color'] ?? '#128C7E';

        if (empty($lc_wpmethods_links)) {
            return; // Nothing to render
        }

        ?>
        <div class="lc-wpmethods-chat-container" id="lcWpmethodsChatContainer">
            <div class="lc-wpmethods-chat-options" id="chatOptions">
                <?php foreach ($lc_wpmethods_links as $link) : 
                    $url = $link['url'] ?? '';
                    $icon_class = $link['icon'] ?? 'fas fa-comment-dots';
                    $label = $link['label'] ?? '';
                    $color = $link['color'] ?? '';
                    $bg_color = $link['bg_color'] ?? '';
                    
                    if (empty($url) || empty($icon_class)) {
                        continue;
                    }
                ?>
                    <a href="<?php echo esc_url($url); ?>" target="_blank" style="background: <?php echo esc_attr($bg_color); ?>;" class="lc-wpmethods-chat-btn <?php echo sanitize_html_class(strtolower($label)); ?>" title="<?php echo esc_attr($label); ?>">
                        <i class="<?php echo esc_attr($icon_class); ?>" style="color: <?php echo esc_attr($color); ?>;"></i>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="lc-wpmethods-chat-toggle lc-wpmethods-pulse" id="lcWpmethodsChatToggle" style="background: <?php echo esc_attr($toggle_color); ?>;">
                <i class="fas fa-comment-dots" id="lcWpmethodsChatIcon" style="color: <?php echo esc_attr($icon_color); ?>;"></i>
            </div>
        </div>
        <?php
    }




    private function maybe_enqueue_fontawesome() {
        global $wp_styles;
        $already_loaded = false;

        if (!empty($wp_styles->registered)) {
            foreach ($wp_styles->registered as $handle => $style) {
                if (strpos($handle, 'fontawesome') !== false || strpos($handle, 'font-awesome') !== false) {
                    $already_loaded = true;
                    break;
                }
            }
        }

        if (!$already_loaded) {
            wp_enqueue_style('fontawesome', LC_WPMETHODS_URL . 'assets/css/all.min.css', [], '6.7.2');
        }
    }
}
