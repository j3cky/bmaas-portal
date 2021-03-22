<meta http-equiv="Content-Type" content="text/html; charset=us-ascii"><p>
<br>Thank you for ordering Neo Metal<br>
Your Server has been provisioned.<br>
<b>Access to the environment will be granted as soon as the environment deployment is complete.</b><br>
<br>
Here is an example on how to remote console to your server impi:<br><br>
<pre> ssh -l {{ $username }} -o ProxyCommand="openssl s_client -quiet -connect 103.93.128.205:2222 -servername {{$hostname}} " {{$hostname}} </pre>

Password : {{ $password }}

<pre>
https://{{$hostname}}:444/irc.html?gui=true&lang=en <br>
Username : {{ $username }} <br>
Password : {{ $password }} <br>
</pre>


</pre><br>

<b>Troubleshooting and Access issues:</b><br><br>
If you need help using the SSH client on your computer please consult <a href="https://bmaas.arch.biznetgio.xyz/contact">https://bmaas.arch.biznetgio.xyz/contact</a>.<br>
<br>

</p>

