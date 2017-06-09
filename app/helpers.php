<?php

if (!function_exists('checkInterval')) {

    function checkInterval($dueDate) {
        $today = date('Y-m-d');
        $due = date('Y-m-d', strtotime($dueDate));

        $date1 = date_create($today);
        $date2 = date_create($due);
        $interval = date_diff($date1, $date2);
        
        return $interval->format('%a');
    }

}