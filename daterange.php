<?php
	include 'gwtdata.php';
	try {
		$email = "email@example.com";
		$passwd = "*************";
		$sitestring = "example";


		$folder = $sitestring . "-" . date("Ymd-His");
		$savepath = getcwd() . "/" . $folder;
		
		$ns = 0;
		for($i = 1; $i <= 90; $i++){
			$dates[] = array(date('o-m-d', strtotime('-' . strval($i) . ' days')),date('o-m-d', strtotime('-' . strval($i-1) . ' days')));
		}

		print("Working...");
		
		$gdata = new GWTdata();
		if($gdata->LogIn($email, $passwd) === true){
			$sites = $gdata->GetSites();
			foreach($sites as $site){
				if(strpos($site, $sitestring) !== FALSE){
					print("\r\nProcessing " . $site . "\r\n");
					$ns ++;
					if($ns === 1){
						mkdir($savepath);
					}
					$nd=0;
					foreach($dates as $date){
						$nd++;
						printf("\rProcessing day %s", $nd);
						$gdata->SetDaterange($date);
						$gdata->DownloadCSV($site, $savepath);
						sleep(1.5);
					}
					print("\r\nSleeping for 10 seconds...");
					sleep(15);
				}
				
			}				
		
		}

############################################
		## Start parsing csvs ##
############################################	

	chdir($folder);
	$files = glob("*.csv");
	
	foreach($files as $file){
		preg_match("#TOP_QUERIES\-(.*?)\-.*#", $file, $sitea);
		if(isset($sitea[1]) && strlen($sitea[1])>2){
			$sitesa[] = $sitea[1];
		}
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
		}
		file_put_contents($site . "-" . date("Ymd-His") . "-concat.csv", $output);
		$output = "";
	}

	} catch (Exception $e) {
		die($e->getMessage());
	}
?>