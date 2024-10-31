<?php if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

require_once dirname(__FILE__).'/classes/setup.class.php';

if (OVIC::get_config('demo_mode') == true) {
    require_once dirname(__FILE__).'/samples/options.sample.php';
    require_once dirname(__FILE__).'/samples/profile.sample.php';
    require_once dirname(__FILE__).'/samples/customize.sample.php';
    require_once dirname(__FILE__).'/samples/shortcode.sample.php';
    require_once dirname(__FILE__).'/samples/metabox.sample.php';
    require_once dirname(__FILE__).'/samples/taxonomy.sample.php';
}