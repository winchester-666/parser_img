#!/usr/bin/php
<?php
$array_links = array();
$array_img_src = array();
$search_link = $argv[1];

if($argv[1]){
	if(strpos($search_link,"http") !== false){
		array_push($array_links, $argv[1]);
		parseHref($search_link);
	}else{
		$get_headers = get_headers('http://'.$search_link);

		if(strpos($get_headers[0],"200 OK") !== false){
			array_push($array_links, 'http://'.$argv[1]);
			parseHref('http://'.$search_link);
		}else{
			array_push($array_links, 'https://'.$argv[1]);
			parseHref('https://'.$search_link);
		}
	}
}else{
	print "Enter link";
}

// Parser href
function parseHref($url, $count_url = 0){
	global $array_links;
	global $array_img_src;
	global $search_link;
	$files_dir = "./files";
	$url_info = parse_url($url);

	$html = file_get_contents($url);

	preg_match_all("/<a .+>/", $html, $insert_links);

	foreach ($insert_links[0] as $key => $insert_link) {
		if(strpos($insert_link,"href") !== false){

			if(strpos($insert_link,"href='") !== false){
				preg_match('@href=\'([^"]+?)\'@', $insert_link, $href);
			}else{
				preg_match('@href="([^"]+?)"@', $insert_link, $href);
			}
			if(strpos($href[1], '//') === 0){
				$search_href = $url_info['scheme'].":".$href[1];
			}elseif(strpos($href[1], '/') === 0){
				$search_href = $url_info['scheme']."://".$url_info['host'].$href[1];
			}elseif(strpos($href[1], 'http') === 0){
				$search_href = $href[1];
			}elseif(strpos($href[1], $url_info['host']) === 0){
				$search_href = $url_info['scheme']."://".$href[1];
			}else{
				$search_href = $url_info['scheme']."://".$url_info['host'].'/'.$href[1];
			}

			if(strpos($search_href, $url_info['host'])){
				if(strpos($search_href,"#") !== false){
					array_push($array_links, strstr($search_href,"#",true));
				}else if(strpos($search_href,"@") !== false){
					continue;
				}
				else{
					array_push($array_links, $search_href);
				}
			}
		}
	}
	$array_links = array_values(array_unique($array_links));

	saveCSV($url, parseImg($array_links[$count_url]));

	if(count($array_links) > $count_url){
		echo "PARSE #{$count_url} --> {$array_links[$count_url]} \n";
		parseHref($array_links[$count_url], ++$count_url);
	}else{
		echo "\nCompleted!\n";
		echo "The path to the file: {$files_dir}/{$url_info['host']}.csv \n";
		echo "To open the file, type: report {$url_info['host']}\n\n\n";
	}
}


// Parser img
function parseImg($url){
	global $array_links;
	array_push($array_links, $url);

	$url_info = parse_url($url);

	$html = file_get_contents($url);

	preg_match_all("/<img.+\>/", $html, $images);

	$array_src = array();
	foreach ($images[0] as $key => $img) {
		if(strpos($img,"src='") !== false){
			preg_match('@src=\'([^"]+?)\'@', $img, $src);
		}else{
			preg_match('@src="([^"]+?)"@', $img, $src);
		}

		if(strlen($src[1]) != 0){

			if(strpos($src[1], '//') === 0){
				$formated_src = $url_info['scheme'].":".$src[1];
			}elseif(strpos($src[1], '/') === 0){
				$formated_src = $url_info['scheme']."://".$url_info['host'].$src[1];
			}elseif(strpos($src[1], 'http') === 0){
				$formated_src = $src[1];
			}elseif(strpos($src[1], $url_info['host']) === 0){
				$formated_src = $url_info['scheme']."://".$src[1];
			}else{
				$formated_src = $url_info['scheme']."://".$url_info['host'].'/'.$src[1];
			}

		}

		$array_src[$key] = array(
			"link" => $url,
			"src" => $formated_src
		);
	}
	return $array_src;
}


// Save href and src
function saveCSV($url ,$array_src){
	$files_dir = "./files";
	$url_info = parse_url($url);
	if(!is_dir($files_dir)){
		mkdir($files_dir, 0777);
	}

	$file_name = $url_info['host']; 

	$file_csv = fopen($files_dir."/".$file_name.'.csv', 'a');

	foreach ($array_src as $arrays) {
		fputcsv($file_csv, $arrays,';');
	}

	fclose($file_csv);
}