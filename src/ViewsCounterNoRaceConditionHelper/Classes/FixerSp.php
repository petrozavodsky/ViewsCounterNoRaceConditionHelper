<?php

namespace ViewsCounterNoRaceConditionHelper\Classes;


class FixerSp
{
    private $period = 9; //days
    private $level = 15000; //views

    private $metaViesKey;
    private $postsExclude = [];

    public function __construct($key, $exclude = [])
    {
        $this->metaViesKey = $key;
        $this->postsExclude = $exclude;

        add_action('ViewsCounterNoRaceConditionHelper__schedule_hourly', [$this, 'errorsCorrectionSp']);

    }

    public function errorsCorrectionSp()
    {
        $posts = $this->postsList();

        if (!empty($posts)) {
            foreach ($posts as $post) {

                $count = (int)get_post_meta(
                    $post->ID,
                    $this->metaViesKey,
                    true
                );

                $rand = rand(12, 25) / 10;

                $result = ($count * $rand);

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
        $args = [
            'order' => 'DESC',
            'orderby' => 'date',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'exclude' => $this->postsExclude,
            'post_type' => ['advert_post'],
            'date_query' => [
                [
                    'before' => "{$this->period} days ago",
                    'after' => '45 days ago',
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
            ],

        ];

        return get_posts($args);
    }

}
