<?php
/*$cmds = array ('configure', 'vlan database','vlan 1001,2001');

$connection = ssh2_connect('172.16.10.30', 22);
ssh2_auth_password($connection, 'admin', 'admin');

foreach ($cmds as $cmd) {
    $stream = ssh2_exec($connection, $cmd);
    //stream_set_blocking($stream, true);
    $stream_out = ssh2_fetch_stream($stream, SSH2_STREAM_STDIO);
    echo stream_get_contents($stream_out);
}

$connection = ssh2_connect('172.16.10.30', 22);
ssh2_auth_password($connection, 'admin', 'admin');

$stream = ssh2_exec($connection, 'configure');*/
shell_exec ('sshpass -p admin ssh -t -t admin@172.16.10.30 < /var/www/html/bmaas/public/ext/ssh.txt');

