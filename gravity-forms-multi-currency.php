<?php
/**
 * Plugin Name: Gravity Forms Multi Currency
 * Plugin URI: https://github.com/ilanco/gravity-forms-multi-currency
 * Description: Per form currency for Gravity Forms.
 * Version: 1.7.1
 * Author: Ilan Cohen <ilanco@gmail.com>
 * Author URI: https://github.com/ilanco
 */

if (defined('WP_DEBUG') && (WP_DEBUG == true)) {
  error_reporting(E_ALL);
}

// don't load directly
if (!defined('ABSPATH'))
  die(false);

define('GF_MC_VERSION', '1.7.1');

define('GF_MC_MAINFILE', __FILE__);

add_action('init', array('GFMultiCurrency', 'init'), 9);

class GFMultiCurrency
{
    private static $instance;

    private $currency;

    private function __construct()
    {
        if (!$this->is_gravityforms_supported()) {
            return false;
        }

        add_action('wp', array(&$this, 'form_process'), 8);
        add_filter('gform_currency', array(&$this, 'form_currency'));

        if (is_admin()) {
            add_action('gform_admin_pre_render', array(&$this, 'admin_pre_render'));
            add_filter('gform_form_settings', array(&$this, 'custom_form_settings'), 10, 2);
            add_filter('gform_pre_form_settings_save', array(&$this, 'save_custom_form_settings'));
            add_action('gform_editor_js', array(&$this, 'admin_editor_js'));

            add_action('gform_entry_detail_content_before', array(&$this, 'admin_entry_detail'), 10, 2);
        }
        else {
            add_filter('gform_pre_render', array(&$this, 'pre_render'));
        }
    }

    public static function init()
    {
        if (!self::$instance) {
            self::$instance = new GFMultiCurrency();
        }

        return self::$instance;
    }

    public function form_process()
    {
        $form_id = isset($_POST["gform_submit"]) ? $_POST["gform_submit"] : 0;
        if ($form_id) {
            $form_info = RGFormsModel::get_form($form_id);
            $is_valid_form = $form_info && $form_info->is_active;

            if ($is_valid_form) {
                $form = RGFormsModel::get_form_meta($form_id);
                if (isset($form['currency']) && $form['currency']) {
                    $this->currency = $form['currency'];
                }
            }
        }
    }

    public function form_currency($currency)
    {
        if ($this->currency) {
            $currency = $this->currency;
        }

        return $currency;
    }

    public function admin_pre_render($form)
    {
        if (isset($form['currency']) && $form['currency']) {
            $this->currency = $form['currency'];
        }

        return $form;
    }

    public function custom_form_settings($settings, $form)
    {
        ob_start();
        include 'tpl/custom_form_settings.php';
        $settings['Form Basics']['form_currency_setting'] = ob_get_contents();
        ob_end_clean();

        return $settings;
    }

    public function save_custom_form_settings($form)
    {
        $form['currency'] = rgpost('form_currency');

        return $form;
    }

    public function admin_editor_js()
    {
        ?>
        <script type='text/javascript'>
        jQuery(function($) {
            $("#form_currency").change(function() {
                form.currency = this.value;
            });
            $("#form_currency").val(form.currency);
        });
        </script>
        <?php
    }

    public function admin_entry_detail($form, $lead)
    {
        if (isset($form['currency']) && $form['currency']) {
            $this->currency = $form['currency'];
        }

        return $form;
    }

    public function pre_render($form)
    {
        if (isset($form['currency']) && $form['currency']) {
            $this->currency = $form['currency'];
        }

        return $form;
    }

    protected function gf_get_default_currency()
    {
        $currency = get_option("rg_gforms_currency");
        $currency = empty($currency) ? "USD" : $currency;

        return $currency;
    }

    private function is_gravityforms_supported()
    {
        if (class_exists("GFCommon")) {
            return true;
        }

        return false;
    }

    private function set_currency($form_id, $currency)
    {
        $this->currency[$form_id] = $currency;
    }

    private function get_currency($form_id)
    {
        return $this->currency[$form_id];
    }
}

