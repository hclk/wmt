<?php
	$folder = "enterprise-20150210-102842";

	chdir($folder);
	//$savepath = $folder . "-" . date("Ymd-His") . "-concat";
	$files = glob("*.csv");
	
	foreach($files as $file){
		preg_match("#TOP_QUERIES\-(.*?)\-.*#", $file, $sitea);
		if(isset($sitea[1]) && strlen($sitea[1])>2){
			$sitesa[] = $sitea[1];
		}		
		//print($file . "\r\n");
	}
	$sites = array_unique($sitesa);
	foreach($sites as $site){
		print("\r\nProcessing " . $site . "\r\n");
		$tnl = 0;
		$output = "Site: " . $site . PHP_EOL . PHP_EOL;
		foreach($files as $file){
			$file = trim($file);
			if(strpos($file, $site) !== FALSE){
				preg_match("#.*-(.*)#", $file, $datea);
				$date = $datea[1];
				$date = substr($date, 0,4)."-".substr($date, 4,2)."-".substr($date, 6,2);
				$file = fopen($file, "r");
				if($file){
					$nl = 0;
					while(!feof($file)){
						$line = fgets($file);
						$c = explode(",", $line);
						
						$nl++;
						$tnl++;

						printf("\rProcessed %s lines", $tnl);

						if($tnl === 1){
							if(strpos($line, "Change") !== FALSE){
								$extraHeadings = TRUE;
							} else {
								$extraHeadings = FALSE;
							}
							$output .= "Date,Query,Avg.position,Impressions,Clicks" . PHP_EOL;
						}
						/*if($extraHeadings === TRUE){
							//print("Extra headings\r\n");
						}else{
							print("No extra headings\r\n");
							print_r($c);
						}*/
						
						if($nl > 1){
							if($extraHeadings === TRUE && isset($c[0],$c[7],$c[1],$c[3])){
								$output .= $date . "," . trim($c[0]) . "," . trim($c[7]) . "," . trim($c[1]) . "," . trim($c[3]) . PHP_EOL;
							} elseif (isset($c[0],$c[4],$c[1],$c[2])){
								$output .= $date . "," . trim($c[0]) . "," . trim($c[4]) . "," . trim($c[1]) . "," . trim($c[2]) . PHP_EOL;
							}
						}		
					}
				}
			}
			//print($file . "\r\n");
		}
		file_put_contents($site . "-" . date("Ymd-His") . "-concat.csv", $output);
		$output = "";
	}
?>