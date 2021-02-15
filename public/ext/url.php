<?php
                $curl = curl_init();
                echo "yrdy";
                curl_setopt_array($curl, array(
                        CURLOPT_URL => "https://172.16.1.4/api/ipam/prefixes/2/",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "GET",
                        CURLOPT_SSL_VERIFYHOST => 0,
                        CURLOPT_SSL_VERIFYPEER => 0,
                        CURLOPT_HTTPHEADER => array(
                                "Authorization: Token 0554acc7c422e39ce6e5abde46df10edddc1e377",
                                "Content-Type: application/json",
                                "Postman-Token: 90b06eb5-c2f0-4a80-b3d2-d50dababaa9d",
                                "cache-control: no-cache"
                        ),
                ));

                $response = curl_exec($curl);
                echo $response;
                
                $err = curl_error($curl);

                curl_close($curl);
                if ($err) {
                        echo "cURL Error #:" . $err;
                } else {
                        $val = json_decode($response);
                        print_r($val);
                        
                        $prefix['id']=$val->id;
                        $prefix['prefix']=$val->prefix;
                        $prefix['vlan']=$val->vlan->vid;
                        return $prefix;

                        //return $val->id;
                }

?>
