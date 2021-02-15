<?php
/*$response='{
    "count": 1,
    "next": null,
    "previous": null,
    "results": [
        {
            "id": 1,
            "url": "https://172.16.1.4/api/ipam/prefixes/1/",
            "family": {
                "value": 4,
                "label": "IPv4"
            },
            "prefix": "172.16.30.0/24",
            "site": null,
            "vrf": null,
            "tenant": {
                "id": 1,
                "url": "https://172.16.1.4/api/tenancy/tenants/1/",
                "name": "AA99997",
                "slug": "AA99997"
            },
            "vlan": {
                "id": 1,
                "url": "https://172.16.1.4/api/ipam/vlans/1/",
                "vid": 30,
                "name": "vlan30",
                "display_name": "vlan30 (30)"
            },
            "status": {
                "value": "active",
                "label": "Active"
            },
            "role": {
                "id": 2,
                "url": "https://172.16.1.4/api/ipam/roles/2/",
                "name": "Internal",
                "slug": "Internal"
            },
            "is_pool": false,
            "description": "",
            "tags": [],
            "custom_fields": {},
            "created": "2020-11-26",
            "last_updated": "2020-11-26T07:38:54.129074Z"
        }
    ]
}';*/
$response='{
    "count": 12,
    "next": "https://172.16.1.4/api/ipam/ip-addresses/?limit=1&offset=1&status=active&tag=Public",
    "previous": null,
    "results": [
        {
            "id": 270,
            "url": "https://172.16.1.4/api/ipam/ip-addresses/270/",
            "family": {
                "value": 4,
                "label": "IPv4"
            },
            "address": "103.93.128.194/28",
            "vrf": null,
            "tenant": null,
            "status": {
                "value": "active",
                "label": "Active"
            },
            "role": null,
            "assigned_object_type": null,
            "assigned_object_id": null,
            "assigned_object": null,
            "nat_inside": null,
            "nat_outside": null,
            "dns_name": "",
            "description": "",
            "tags": [
                {
                    "id": 1,
                    "url": "https://172.16.1.4/api/extras/tags/1/",
                    "name": "Public",
                    "slug": "Public",
                    "color": "9e9e9e"
                }
            ],
            "custom_fields": {},
            "created": "2020-11-27",
            "last_updated": "2020-11-27T05:03:47.131695Z"
        }
    ]
}';
$val = json_decode($response);
echo $val->results[0]->id;


			//$prefix['id']=$val->results[0]->id;
			//$prefix['prefix']=$val->results[0]->prefix;
			//$prefix['vlan']=$val->results[0]->vlan->vid;
			//if(array_key_exists ( "tenant" , $val )){
			//	echo "exist";
			//}else{
			//	echo "not exsist";
			//}
			//print_r($val->tenant[0]);
$ip="103.93.128.193";
$range="/28";
	if ( strpos( $range, '/' ) == false ) {
		$range .= '/32';
	}
	// $range is in IP/CIDR format eg 127.0.0.1/24
	list( $range, $netmask ) = explode( '/', $range, 2 );
	$range_decimal = ip2long( $range );
	$ip_decimal = ip2long( $ip );
	$wildcard_decimal = pow( 2, ( 32 - $netmask ) ) - 1;
	$netmask_decimal = ~ $wildcard_decimal;
	echo ( ( $ip_decimal & $netmask_decimal ) == ( $range_decimal & $netmask_decimal ) );
?>
