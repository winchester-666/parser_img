#!/usr/bin/php
<?php
$search_link = $argv[1];

if($argv[1]){
	if(strpos($search_link,"http") !== false){

		getCSV($search_link);
	}else{
		$get_headers = get_headers('http://'.$search_link);

		if(strpos($get_headers[0],"200 OK") !== false){

			getCSV('http://'.$search_link);
		}else{

			getCSV('https://'.$search_link);
		}
	}
	
}else{
	print "Enter link";
}

// Open scv file
function getCSV($url){
	$files_dir = "./files";
	$url_info = parse_url($url);
	$file_name = $url_info['host']; 

	if (($handle = fopen($files_dir."/".$file_name.'.csv', "r")) !== FALSE) {

		while (($data = fgetcsv($handle, 0, ";")) !== FALSE) {

			echo "Источник : ".$data[0]."\n";
			echo "Изображение : ".$data[1]."\n\n";

		}
		fclose($handle);
	}
}
