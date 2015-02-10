<?php
	include 'gwtdata.php';
	try {
		$email = "example@example.com";
		$passwd = "**************";
		$sitestring = "example.com";


		$savepath = getcwd() . "/" . $sitestring . "-" . date("Ymd-His");
		$ns = 0;
		# Dates must be in valid ISO 8601 format.
		//$dtoday = date('o-M-D');
		for($i = 1; $i <= 90; $i++){
			$dates[] = array(date('o-m-d', strtotime('-' . strval($i) . ' days')),date('o-m-d', strtotime('-' . strval($i-1) . ' days')));
			

			//print(date('o-m-d', strtotime('-' . strval($i) . ' days')) . "-" . date('o-m-d', strtotime('-' . strval($i-1) . ' days')));
			//print(PHP_EOL);
		}

		print("Working...");
		
		$gdata = new GWTdata();
		if($gdata->LogIn($email, $passwd) === true){
			//$site = "enterprise.co.uk";
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
	} catch (Exception $e) {
		die($e->getMessage());
	}
?>