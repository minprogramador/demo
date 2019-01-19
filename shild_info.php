<?php

function formatBytes($bytes) {
        if ($bytes >= 1073741824) {
                $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        }elseif ($bytes >= 1048576) {
                $bytes = number_format($bytes / 1048576, 2) . ' MB';
        }elseif ($bytes >= 1024) {
                $bytes = number_format($bytes / 1024, 2) . ' KB';
        }elseif ($bytes > 1) {
                $bytes = $bytes . ' bytes';
        }elseif ($bytes == 1) {
                $bytes = $bytes . ' byte';
        }else {
               	$bytes = '0 bytes';
        }

	return $bytes;
}

function query_comm($input) {
        exec("ps " . $input . " | tail -1 | cut -b 28-", $comm);
        return $comm[0];
}
function listStringSplit($sList) {
        $aList = preg_split("/[\s,]+/", $sList, -1, PREG_SPLIT_NO_EMPTY);
        return $aList;
}
function brackets_remove($string) {
        $left = str_replace("[", "", $string);
        $right = str_replace("]", "", $left);
        return $right;
}

function cpu_info() {
        $aCpu_info = array();
        exec("date +'%Y-%m-%d\t%H:%M'", $time);
        $date_time = listStringSplit(trim($time[0]));
        $aCpu_info["date"] = trim($date_time[0]) . ' ' .trim($date_time[1]);
        exec("cat /proc/loadavg", $load_avg);
        $loadavg = listStringSplit(trim($load_avg[0]));
        $aCpu_info["load_oneMin"] = $loadavg[0];
        $aCpu_info["load_fiveMin"] = $loadavg[1];
        $aCpu_info["load_fifteenMin"] = $loadavg[2];
        exec("top -b -n 1 c", $cpuinfo);
        $cpu_tasks = listStringSplit(trim($cpuinfo[1]));
        $aCpu_info["totalTasks"] = $cpu_tasks[1];
        $aCpu_info["runTasks"] = $cpu_tasks[3];

        $cpu_percent = listStringSplit(trim($cpuinfo[2]));
        $aCpu_info["cpuPercent"] = 100 - trim(str_replace("%id", "", $cpu_percent[4]));
        $top_three[] = listStringSplit(trim($cpuinfo[7]));
        $top_three[] = listStringSplit(trim($cpuinfo[8]));
        $top_three[] = listStringSplit(trim($cpuinfo[9]));
        
        exec("free -h", $meninfo);

        $men_percent = listStringSplit(trim($meninfo[1]));
        $aCpu_info['men_total'] = ($men_percent[1]);
        $aCpu_info['men_usado'] = ($men_percent[2]);
        $aCpu_info['men_livre'] = ($men_percent[3]);

        $aCpu_info["topOnePid"] = $top_three[0][0];
        $aCpu_info["topTwoPid"] = $top_three[1][0];
        $aCpu_info["topThreePid"] = $top_three[2][0];
        $aCpu_info["topOneComm"] = query_comm($top_three[0][0]);
        $aCpu_info["topTwoComm"] = query_comm($top_three[1][0]);
        $aCpu_info["topThreeComm"] = query_comm($top_three[2][0]);
        return $aCpu_info;
}

$info = cpu_info();
echo json_encode($info);




