<?php


class SwitchHandler
{

    private static function connect()
    {
        $connection = ssh2_connect('192.168.69.18', 22);

        ssh2_auth_none($connection, 'rys2016');

        $stdio_stream = ssh2_shell($connection);

        fwrite($stdio_stream, 'rys2016' . PHP_EOL);
        sleep(1);

        fwrite($stdio_stream, '2016RyS' . PHP_EOL);
        sleep(1);


        return $stdio_stream;

    }

    public static function addMac($mac, $user = null)
    {
        $shell = self::connect();
        fwrite($shell, 'config macfilter add '.$mac.' 4 wl-labs '.$user . PHP_EOL);
        sleep(1);
    }

    public static function deleteMac($mac)
    {
        $shell = self::connect();
        fwrite($shell, 'config macfilter delete '.$mac . PHP_EOL);
        sleep(1);
    }
}

