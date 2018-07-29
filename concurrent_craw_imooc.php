<?php

//进程数
$work_number=20;
$worker=[];
$ch = curl_init();
function getRange($pid){
 	$i = $pid % 20;
 	$min = 2000000 + 245000*$i;
 	return range($min,$min+245000);
}
function fetchContent(swoole_process $worker) {
   $range = getRange($worker->pid);
   $filename = "imooc_user_learning_info/success/imooc_user_learning_info_".$worker->pid;
   $failname = "imooc_user_learning_info/fail/imooc_user_learning_info_".$worker->pid;
   foreach ($range as $key => $uid) {
	   	$url = 'https://www.imooc.com/u/'.$uid;
	   	$string = curlData($url);
	   	if(!$string) {
			file_put_contents($failname, $url."\n",FILE_APPEND);
			usleep(500000);
			continue;
	   	}
		preg_match_all("/<em>([^<]+)<\/em>/", $string, $matches);
	  	$str = $uid.','.implode(',', $matches[1]).','.$url."\n";
	  	file_put_contents($filename, $str,FILE_APPEND);
	  	usleep(500000);
    }
}

//创建进程
for ($i=0; $i < $work_number; $i++) { 
	//创建多线程
	$pro=new swoole_process("fetchContent");
	$pro_id=$pro->start();

}

function curlData($url){
	global $ch;
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($ch, CURLOPT_POSTFIELDS,$posts);
	$icerik = curl_exec($ch);
	return $icerik;
} 

//进程回收
swoole_process::wait();

