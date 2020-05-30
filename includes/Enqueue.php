<?php

/**
 * @package WoocommerceProductOptions
 */

class Enqueue
{
    public function register()
    {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    public function enqueue_scripts()
    {
        wp_enqueue_script('pluginScript101', PLUGIN_DIR_URL . "assets/scripts.js", array('jquery'));
    }
}
