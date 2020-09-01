<?php

namespace ViewsCounterNoRaceConditionHelper\Classes;


use ViewsCounterNoRaceConditionHelper;

class ViewsBot2
{
    private $max = 14000;
    private $period = 12; //days
    private $level = 8000; //views
    private $postsExclude = [];
    private $metaViesKey;

    public function __construct($key, $exclude = [])
    {
        $this->metaViesKey = $key;
        $this->postsExclude = $exclude;
        add_action('ViewsCounterNoRaceConditionHelper__schedule_commonly', [$this, 'task']);
    }

    public static function calculateDay($array)
    {
        $days = array_map(function ($val) {

            return array_sum($val);
        }, $array);

        return array_sum($days);
    }

    private static function _switcher($array, $key)
    {
        if (empty($array)) {
            return 0;
        }

        $arrayKeys = array_keys($array);

        foreach ($arrayKeys as $val) {
            if ($key <= $val) {
                return $val;
            }
        }

        return 0;
    }

    public function task()
    {
        $posts = $this->taskPostList();

        $out = [];
        foreach ($posts as $post) {
            $offset = DateHelper::timeDiff($post->post_date);
            $dispersion = new Dispersion();
            $days = $dispersion->day();
            $currentHour = current_time('H');
            $currentMinute = current_time('i');
            $intervals = isset($days[$offset][$currentHour]) ? $days[$offset][$currentHour] : [];

            if (empty($intervals)) {
                continue;
            }

            $increment = $intervals[self::_switcher($intervals, $currentMinute)];
            $increment = (int)ceil($increment);

            $count = (int)get_post_meta(
                $post->ID,
                $this->metaViesKey,
                true
            );

            if (empty($count)) {
                $count = 0;
            }

            if ($count > self::calculateDay($days[$offset])) {
                continue;
            }

            if ($this->max > $count) {
                $count = $count + $increment;

                $out[$post->ID] = update_post_meta(
                    $post->ID,
                    $this->metaViesKey,
                    $count
                );
            }
        }
    }

    public function taskPostList()
    {
        $args = [
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'exclude' => $this->postsExclude,
            'post_type' => ViewsCounterNoRaceConditionHelper::$typesPosts,
            'date_query' => [
                [
                    'after' => "{$this->period} days ago",
                    'inclusive' => true,
                ]
            ],
            'meta_query' => [
                'relation' => 'OR',
                'views' => [
                    'key' => $this->metaViesKey,
                    'compare' => '<',
                    'value' => $this->level,
                    'type' => 'NUMERIC'
                ],
                'views_not' => [
                    'key' => $this->metaViesKey,
                    'compare' => 'NOT EXISTS',
                ],
            ],
        ];

        return get_posts($args);
    }


}
