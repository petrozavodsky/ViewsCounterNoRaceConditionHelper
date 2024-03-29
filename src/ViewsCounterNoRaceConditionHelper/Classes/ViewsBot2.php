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
        add_action('transition_post_status', [$this, 'statusChangeWatcher'], 10, 3);
        add_action('save_post', [$this, 'update']);
        add_action('ViewsCounterNoRaceConditionHelper__schedule_single_events', [$this, 'singleIncrement'], 10, 1);

    }

    /**
     * Добавляем при сохранении поста если метаполе совсем пустое
     *
     * @param $pid
     * @return false
     */
    public function update($pid)
    {
        if (wp_is_post_autosave($pid) || wp_is_post_revision($pid)) {
            return false;
        }

        $count = (int)get_post_meta($pid, $this->metaViesKey, true);

        if (empty($count)) {
            update_post_meta($pid, $this->metaViesKey, rand(11, 23));
        }

        return $pid;
    }

    public function statusChangeWatcher($new, $old, $post)
    {
        if ($old !== $new && 'publish' === $new) {
            $this->createSingleEvent($post->ID);
        }
    }

    /**
     * Регистрируем не повторяющееся событие дважды через примерно рандомные промежутки времени
     *
     * @param $pid
     */
    public function createSingleEvent($pid)
    {
        $startTime = time() + (MINUTE_IN_SECONDS * 20 + rand(1, 15));

        wp_schedule_single_event((time() + ( 5 * MINUTE_IN_SECONDS) ), 'ViewsCounterNoRaceConditionHelper__schedule_single_events',
            [$pid]);
        wp_schedule_single_event($startTime, 'ViewsCounterNoRaceConditionHelper__schedule_single_events', [$pid]);
        wp_schedule_single_event(($startTime + HOUR_IN_SECONDS), 'ViewsCounterNoRaceConditionHelper__schedule_single_events', [$pid]);
    }

    public function singleIncrement($pid)
    {

        if ('publish' !== get_post_status($pid)) {
            return false;
        }

        $count = (int)get_post_meta($pid, $this->metaViesKey, true);

        // если просмотров уже много то ничего не делать
        if (2400 > $count) {

            if(300 > $count){
                $count = $count + rand(300, 423);
            }else {

                if (1000 > $count) {
                    $count = $count + rand(800, 1300);
                } else if (400 > $count) {
                    $count = $count + rand(300, 600);
                }
            }

            update_post_meta($pid, $this->metaViesKey, $count);
        }
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
