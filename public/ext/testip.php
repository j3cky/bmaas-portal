<?php
                $privateip="172.16.12.0";
                $pubip="103.93.128.193";
                $pubipcount=ip2long($pubip);
                $ipcount=ip2long($privateip);
                for($i=0 ; $i<3; $i++){
                                //$ipcount++;
				//$pubipcount++;
				echo long2ip($ipcount);
		}

//$start_ip = ip2long('172.16.12.0');
//$end_ip = ip2long('172.16.12.10');

//while($start_ip <= $end_ip){
//  echo long2ip($start_ip).'<br>';
//  $start_ip++;
//}
?>
