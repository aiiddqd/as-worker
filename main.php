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
                \WP_CLI::log('as-worker - already running');
                return false;
            }

            $couters = [
                'total' => 0,
                'iterations' => 0
            ];
            while (true) {

                $couters['iterations']++;
                try {
                    
                    $is_active = get_transient('as-worker');

                    if(get_transient('as-worker-hard-stop')){
                        break;
                    };
                    
                    if ( $is_active ) {

                        // if working longer than 1 hour - stop 
                        if (time() - $is_active > 60 * 60) {
                            delete_transient('as-worker');
                            return;
                        }
                    } else {
                        set_transient('as-worker', time(), 60);
                    }
                    
                    $jobsNumber = ActionScheduler_QueueRunner::instance()->run();
                    
                    $couters['total'] += $jobsNumber;

                    \WP_CLI::log('Jobs: ' . print_r($couters, true));
                    wc_get_logger()->info('Jobs: ' . print_r($couters, true), ['context' => 'as-worker']);
                    
                    if (empty($jobsNumber)) {
                        wc_get_logger()->info('Jobs - auto stop: ' . print_r($couters, true), ['context' => 'as-worker']);
                        delete_transient('as-worker');
                        break;
                    }
                    
                } catch (\Throwable $th) {
                    wc_get_logger()->error($th->getMessage() . '... ' . print_r($couters, true), ['context' => 'as-worker']);
                    delete_transient('as-worker');
                    break;
                }
            }

            
        });
    }
});