<?php
	try {		
		$folder = "concats-all";
		chdir($folder);
		$date = date("Ymd-His");
		$data = array();
		file_put_contents($date . "-ctrs.csv", $folder . PHP_EOL . PHP_EOL . "Type,Words,Rank,Impressions,Clicks,CTR" . PHP_EOL);
		$files = glob("*concat.csv");
		$countfiles = count($files);
		$nk = $nf = $tnl = 0;
		$fp = fopen($date . "-ctrs.csv", "w");
		foreach($files as $file){
			$nf++;
			$file = trim($file);
			preg_match("/^(.*?)\-\-.*?\-concat.csv$/", $file, $sitea);$site = $sitea[1];unset($sitea);
			$file = fopen($file, "r");
			if($file){
				while(!feof($file)){
					++$tnl;
					$line = fgets($file);
					$brandfound = false;
					if(substr($line, 0, 2) == 20){	//Checks the line starts with a date "20xx" thus is data to be parsed
						$line = str_replace('"', "", $line); // removes inverted commas
						$d = explode(",", $line);
						$c = array_map('trim', $d); unset($d);
						$nonpercentage = explode(".", $c[2]);
						if(isset($nonpercentage[1])){
							$lendec = strlen($nonpercentage[1]);
						}else{
							$lendec = 0;
						}
						if(count($c)>3 && is_numeric($c[2]) && $c[2]>=1 && $lendec<=2 && $c[3]>0 && $c[2]<=100 && $c[3]>$c[4]){ //sanitise data to process
							$nowords = substr_count($c[1], " ") + 1;
							if(strripos($c[1], " ")){ 	//start checking for brand
								$words = explode(" ", $c[1]);
								foreach ($words as $word) {
									if(strripos($site, $word) !== false){
										$brandfound = true;
									}
								}							
							}elseif(strripos($site, $c[1])){
								$brandfound = true;
							}							//end checking for brand
							$key = strval($c[2]) . "-" . ($brandfound ? "Branded" : "Unbranded") . "-" . strval($nowords);
							//$key = $c[2] . "-" . ($brandfound ? "Branded" : "Unbranded");
							if(array_key_exists($key, $data)){
								$newline = array(
									"Type" 			=> ($brandfound ? "Branded" : "Unbranded"),
									"Words" 		=> $nowords,
									"Rank"			=> $c[2],
									"Impressions" 	=> $c[3] + $data[$key]["Impressions"],
									"Clicks"		=> $c[4] + $data[$key]["Clicks"],
									"Instances"		=> $data[$key]["Instances"] + 1
									);
								$data[$key] = $newline;
							}else{
								$newline = array(
									"Type" 			=> ($brandfound ? "Branded" : "Unbranded"),
									"Words" 		=> $nowords,
									"Rank"			=> $c[2],
									"Impressions" 	=> $c[3],
									"Clicks"		=> $c[4],
									"Instances"		=> 1
									);
								$data[$key] = $newline;
								$nk ++;
							}							
							
							printf("\r%s unique keys processed. %s/%s files processed. %s lines processed", $nk, $nf, $countfiles, $tnl);	
						}
						

					}else{
						//printf("Line " . $line . " not processed" . PHP_EOL);
					}
				}
			}
			$output = "";
			foreach($data as $fields){
				foreach ($fields as $value) {
					$output .= $value . ",";
				}
				$output .= PHP_EOL;
			}
			file_put_contents($date . "-ctrs.csv",  $folder . PHP_EOL . PHP_EOL . "Type,Words,Rank,Impressions,Clicks,Instances" . PHP_EOL . $output);
		}		
		fclose($fp);
	} catch (Exception $e) {
		die($e->getMessage());
	}
?>