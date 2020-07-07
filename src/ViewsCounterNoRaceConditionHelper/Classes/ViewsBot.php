<?php

namespace ViewsCounterNoRaceConditionHelper\Classes;


use ViewsCounterNoRaceConditionHelper;

class ViewsBot
{

    private $period = 10; //days
    private $level = 8000; //views
    private $levelSp = 15000; //views
    private $postsExclude = [];
    private $metaViesKey;

    public function __construct($key, $exclude = [])
    {
        $this->metaViesKey = $key;
        $this->postsExclude = $exclude;
        self::interval();

        add_action('ViewsCounterNoRaceConditionHelper__schedule_commonly', [$this, 'task']);
        add_action('ViewsCounterNoRaceConditionHelper__schedule_hourly', [$this, 'errorsCorrection']);
        add_action('ViewsCounterNoRaceConditionHelper__schedule_hourly', [$this, 'errorsCorrectionSp']);
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
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

            $count = $count + $increment;

            $out[$post->ID] = update_post_meta(
                $post->ID,
                $this->metaViesKey,
                $count
            );
        }
    }

    public function taskPostList()
    {
        $days = $this->period;
        $args = [
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'exclude' => $this->postsExclude,
            'post_type' => ViewsCounterNoRaceConditionHelper::$typesPosts,
            'date_query' => [
                [
                    'after' => "{$days} days ago",
                    'inclusive' => true,
                ]
            ],
            'meta_query' => [
                'relation' => 'OR',
                'views' => [
                    'key' => $this->metaViesKey,
                    'compare' => '<',
                    'value' => Dispersion::randHelper($this->level * 44),
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

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
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

    public static function interval()
    {

        // 15-ти минутный интервал
        add_filter('cron_schedules', function ($schedules) {
            $schedules['ViewsCounterNoRaceConditionHelper__schedule_commonly'] = [
                'interval' => MINUTE_IN_SECONDS * 15,
                'display' => '15 min.'
            ];

            return $schedules;
        });
    }


    public function errorsCorrection()
    {

        $posts = $this->postsList();

        if (!empty($posts)) {
            foreach ($posts as $post) {

                $count = (int)get_post_meta(
                    $post->ID,
                    $this->metaViesKey,
                    true);

                $rand = (int)rand(900, 4100);
                $result = $rand + $count;

                update_post_meta(
                    $post->ID,
                    $this->metaViesKey,
                    $result
                );

            }
        }

    }

    public function postsList()
    {
        $days = $this->period;
        $args = [
            'order' => 'DESC',
            'orderby' => 'date',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'post_type' => ViewsCounterNoRaceConditionHelper::$typesPosts,
            'exclude' => $this->postsExclude,
            'date_query' => [
                [
                    'before' => "{$days} days ago",
                    'after' => '150 days ago',
                    'inclusive' => true,
                ]
            ],
            'meta_query' => [
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
            ]
        ];

        return get_posts($args);
    }

    public function errorsCorrectionSp()
    {
        $posts = $this->listSp();

        if (!empty($posts)) {
            foreach ($posts as $post) {
                $count = (int)get_post_meta(
                    $post->ID,
                    $this->metaViesKey,
                    true
                );
                $rand = (int)rand(900, 4100);
                $result = $rand + $count;
                update_post_meta(
                    $post->ID,
                    $this->metaViesKey,
                    $result);
            }
        }
    }

    public function listSp()
    {
        $args = [
            'order' => 'DESC',
            'orderby' => 'date',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'exclude' => $this->postsExclude,
            'post_type' => ['advert_post'],
            'date_query' => [
                [
                    'before' => "9 days ago",
                    'after' => '45 days ago',
                    'inclusive' => true,
                ]
            ],
            'meta_query' => [
                'views' => [
                    'key' => $this->metaViesKey,
                    'compare' => '<',
                    'value' => $this->levelSp,
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