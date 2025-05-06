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
        wp_enqueue_style('lc-wpmethods-css', LC_WPMETHODS_URL . 'assets/css/front-end.css', [], VERSION_SFIW);
        wp_enqueue_script('lc-wpmethods-js', LC_WPMETHODS_URL . 'assets/js/front-end.js', ['jquery'], VERSION_SFIW, true);
    }

    public function render_chat_buttons()
    {// Load settings
        $options = get_option('lc_wpmethods_settings');

        // Repeater field should be an array of arrays
        $lc_wpmethods_links = $options['lc_wpmethods_links'] ?? [[
            'url' => 'https://wa.me/88017900000',
            'icon' => 'fab fa-whatsapp',
            'label' => 'WhatsApp',
            'color' => '#ffffff',
            'bg_color' => '#00DA62',
        ]];

        $toggle_color = $options['toggle_bg_color'] ? $options['toggle_bg_color'] : '#00DA62';
        $icon_color = $options['icon_color'] ? $options['icon_color'] : '#FFFFFF';
        $hover_color = $options['hover_color'] ?  $options['hover_color'] : '#128C7E';
        $custom_text = $options['custom_text'] ?  $options['custom_text'] : '';
        $custom_ver = '?text=';
        $pulse_animation_border_color = $options['pulse_animation_border_color'] ?  $options['pulse_animation_border_color'] : '#25D366';

        if (empty($lc_wpmethods_links) || !is_array($lc_wpmethods_links)) {
            return; // Nothing to render
        }
        ?>

        <div class="lc-wpmethods-chat-container" id="lcWpmethodsChatContainer">
            <div class="lc-wpmethods-chat-options" id="chatOptions">
                <?php foreach ($lc_wpmethods_links as $link) : 
                    $url = !empty($link['url']) ? $link['url'] : 'https://wa.me/your-whatsapp-number';
                    $icon_class = !empty($link['icon']) ? $link['icon'] : 'fab fa-whatsapp';
                    $label = !empty($link['label']) ? $link['label'] : 'WhatsApp';
                    $color = !empty($link['color']) ? $link['color'] : '#ffffff';
                    $bg_color = !empty($link['bg_color']) ? $link['bg_color'] : '#00DA62';

                    if (empty($url) || empty($icon_class)) {
                        continue;
                    }
                ?>
                    <div class="sfiw-icons">
                        <p class="label-sfiw"><?php echo esc_attr($label); ?></p>
                        <a href="<?php echo esc_url($url); if (!empty($custom_text)) : echo esc_html('?text='.$custom_text); endif; ?>" target="_blank" style=" background: linear-gradient(45deg, <?php echo esc_attr($bg_color);?> 50%, #00000075 100%); ?>;" class="lc-wpmethods-chat-btn <?php echo sanitize_html_class(strtolower($label)); ?>">
                            <i class="<?php echo esc_attr($icon_class); ?>" style="color: <?php echo esc_attr($color); ?>;"></i>
                        </a>
                        
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="lc-wpmethods-chat-toggle lc-wpmethods-pulse" id="lcWpmethodsChatToggle">
                <i class="fas fa-comment-dots" id="lcWpmethodsChatIcon"></i>
            </div>

            <style>
                .lc-wpmethods-chat-toggle{
                    color: <?php echo esc_attr($icon_color); ?>;
                    background: <?php echo esc_attr($toggle_color); ?>;
                }
                .lc-wpmethods-chat-toggle:hover{
                    background: <?php echo esc_attr($hover_color); ?>;
                }


               @keyframes lc-wpmethods-pulse {
                    0% {
                        box-shadow: 0 0 0 0 <?php echo esc_attr($pulse_animation_border_color); ?>;
                        transform: scale(1);
                    }

                    70% {
                        transform: scale(1.2);
                        box-shadow: 0 0 0 7px rgba(242, 105, 34, 0);
                    }

                    100% {
                        transform: scale(1);
                        box-shadow: 0 0 0 0 rgba(242, 105, 34, 0);
                    }
                }
            </style>
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
