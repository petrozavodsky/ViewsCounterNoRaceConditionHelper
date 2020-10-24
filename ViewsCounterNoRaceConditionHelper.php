<?php
/*
Plugin Name: ViewsCounterNoRaceConditionHelper plugin
Plugin URI: http://alkoweb.ru
Author: Petrozavodsky
Author URI: http://alkoweb.ru
Text Domain: ViewsCounterNoRaceConditionHelper
Domain Path: /languages
Requires PHP: 7.1
Version: 1.0.0
License: GPLv3
*/

if (!defined('ABSPATH')) {
    exit;
}

require_once(plugin_dir_path(__FILE__) . "includes/Autoloader.php");

if (file_exists(plugin_dir_path(__FILE__) . "vendor/autoload.php")) {
    require_once(plugin_dir_path(__FILE__) . "vendor/autoload.php");
}

use ViewsCounterNoRaceConditionHelper\Autoloader;

new Autoloader(__FILE__, 'ViewsCounterNoRaceConditionHelper');

use ViewsCounterNoRaceConditionHelper\Base\Wrap;
use ViewsCounterNoRaceConditionHelper\Classes\Cache;
use ViewsCounterNoRaceConditionHelper\Classes\Fixer;
use ViewsCounterNoRaceConditionHelper\Classes\FixerSp;
use ViewsCounterNoRaceConditionHelper\Classes\ViewsBot2;

class ViewsCounterNoRaceConditionHelper extends Wrap
{
    public $version = '1.0.0';
    public static $textdomine;
    public $metaViesKey = '_post_views_count';
    public $postsExclude = [];
    public static $typesPosts = ['advert_post', 'post', 'heroine', 'hub', 'husslenews'];


    public function __construct()
    {
        $this->postsExclude = apply_filters('ViewsCounterNoRaceCondition__mata-exclude-posts', []);
        $this->metaViesKey = apply_filters('ViewsCounterNoRaceCondition__mata-field', $this->metaViesKey);

        new Fixer($this->metaViesKey, $this->postsExclude);
        new FixerSp($this->metaViesKey, $this->postsExclude);
        new ViewsBot2($this->metaViesKey, $this->postsExclude);

        new Cache($this->metaViesKey);

        add_filter('cron_schedules', function ($schedules) {
            $schedules['ViewsCounterNoRaceConditionHelper__schedule_commonly'] = [
                'interval' => MINUTE_IN_SECONDS * 14,
                'display' => '14 min.'
            ];

            return $schedules;
        });
    }

    public static function install()
    {

        if (!wp_next_scheduled('ViewsCounterNoRaceConditionHelper__schedule_commonly')) {
            wp_schedule_event(
                time(),
                'ViewsCounterNoRaceConditionHelper__schedule_commonly',
                'ViewsCounterNoRaceConditionHelper__schedule_commonly'
            );
        }

        if (!wp_next_scheduled('ViewsCounterNoRaceConditionHelper__schedule_hourly')) {
            wp_schedule_event(
                time(),
                'hourly',
                'ViewsCounterNoRaceConditionHelper__schedule_hourly'
            );
        }
    }

    public static function uninstall()
    {
        wp_clear_scheduled_hook('ViewsCounterNoRaceConditionHelper__schedule_commonly');
        wp_clear_scheduled_hook('ViewsCounterNoRaceConditionHelper__schedule_hourly');
    }
}

register_activation_hook(__FILE__, ['ViewsCounterNoRaceConditionHelper', 'install']);
register_deactivation_hook(__FILE__, ['ViewsCounterNoRaceConditionHelper', 'uninstall']);

function ViewsCounterNoRaceConditionHelper__init()
{
    new ViewsCounterNoRaceConditionHelper();
}

add_action('plugins_loaded', 'ViewsCounterNoRaceConditionHelper__init', 30);
