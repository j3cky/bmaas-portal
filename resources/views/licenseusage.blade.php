<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>License Usage</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Nunito', sans-serif;
                font-weight: 200;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 84px;
            }

            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 13px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }
table {
  border-collapse: collapse;
  width: 100%;
}

th, td {
  text-align: left;
  padding: 8px;
}

tr:nth-child(even){background-color: #f2f2f2}

th {
  background-color: #4CAF50;
  color: white;
}
        </style>
    </head>
    <body>
	<table style="width:100%" border="1">
	<tr>
    		<th style="vertical-align: top;">Organization Name</th>
    		<th style="vertical-align: top;">Win 2012 STD</th>
    		<th style="vertical-align: top;">Win 2012 DC</th>
    		<th style="vertical-align: top;">Win 2016 STD</th>
    		<th style="vertical-align: top;">Win 2016 DC</th>
    		<th style="vertical-align: top;">Win 2019 STD</th>
    		<th style="vertical-align: top;">Win 2019 DC</th>
		<th style="vertical-align: top;">RedHat 8</th>
		<th style="vertical-align: top;">Redhat 7</th>
		<th style="vertical-align: top;">Redhat 6</th>
	</tr>
	<?php  $win12std = 0 ; $win12dc = 0 ; $win16std = 0 ; $win16dc = 0 ; $win19std = 0 ; $win19dc = 0 ; $redhat8 = 0 ; $redhat7 = 0 ; $redhat6 = 0 ; ?>
	@foreach($orglicense as $orglicenseval)
	<tr>
		<?php
		 $win12std = $win12std + $orglicenseval['Windows2012STD'] ;
		 $win12dc = $win12dc + $orglicenseval['Windows2012DC'] ;
		 $win16std = $win16std + $orglicenseval['Windows2016STD'] ;
		 $win16dc = $win16dc + $orglicenseval['Windows2016DC'] ;
		 $win19std = $win19std + $orglicenseval['Windows2019STD'] ;
		 $win19dc = $win19dc + $orglicenseval['Windows2019DC'] ;
		 $redhat8 = $redhat8 + $orglicenseval['Redhat8'] ;
		 $redhat7 = $redhat7 + $orglicenseval['Redhat7'] ;
		 $redhat6 = $redhat6 + $orglicenseval['Redhat6'] ;
		?>
		<td>{{$orglicenseval['name']}}</td>
		<td>{{$orglicenseval['Windows2012STD']}}</td>
		<td>{{$orglicenseval['Windows2012DC']}} </td>
		<td>{{$orglicenseval['Windows2016STD']}}</td>
		<td>{{$orglicenseval['Windows2016DC']}}</td>
		<td>{{$orglicenseval['Windows2019STD']}}</td>
		<td>{{$orglicenseval['Windows2019DC']}}</td>
		<td>{{$orglicenseval['Redhat8']}}</td>
		<td>{{$orglicenseval['Redhat7']}}</td>
		<td>{{$orglicenseval['Redhat6']}}</td>
	</tr>
	@endforeach
	<tr>
		<td style="font-weight:bold">Total</td>
		<td style="font-weight:bold">{{ $win12std}}</td>
		<td style="font-weight:bold">{{$win12dc}}</td>
		<td style="font-weight:bold">{{$win16std}}</td>
		<td style="font-weight:bold">{{$win16dc}}</td>
		<td style="font-weight:bold">{{$win19std}}</td>
		<td style="font-weight:bold">{{$win19dc}}</td>
		<td style="font-weight:bold">{{$redhat8}}</td>
		<td style="font-weight:bold">{{$redhat7}}</td>
		<td style="font-weight:bold">{{$redhat6}}</td>		
	</tr>
	</table>
	
    </body>
</html>
