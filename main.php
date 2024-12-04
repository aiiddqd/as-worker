<?php
/*
Plugin Name: Action Scheduler as Worker
Description: Run Action Scheduler queue as worker mode (wp as-worker)
Version: 0.1.241201
Author: aiiddqd
Author URI: https://github.com/aiiddqd/as-worker
*/

namespace ASWorker;

use DOMDocument, ActionScheduler_QueueRunner;


add_action('init', function () {

    add_filter('action_scheduler_allow_async_request_runner', '__return_false');

    if (class_exists('WP_CLI')) {


        \WP_CLI::add_command('as-worker', function ($argv, $assoc_args) {
            $is_active = get_transient('as-worker');
            if ($is_active) {
                \WP_CLI::error('as-worker - already running');
                return false;
            }

            while (true) {

                try {
                    
                    $is_active = get_transient('as-worker');
                    
                    if (! $is_active) {
                        set_transient('as-worker', 1, 60);
                    }
                    
                    $jobsNumber = ActionScheduler_QueueRunner::instance()->run();
                    
                    \WP_CLI::log('Jobs: ' . $jobsNumber);
                    wc_get_logger()->info('Jobs: ' . $jobsNumber, ['context' => 'as-worker']);
                    
                    if (empty($jobsNumber)) {
                        sleep(30);
                    }
                    
                } catch (\Throwable $th) {
                    wc_get_logger()->error($th->getMessage(), ['context' => 'as-worker']);
                    // var_dump($th);
                    break;
                }
            }

            
        });
    }
});