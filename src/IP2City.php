<?php
/**
 * Created by PhpStorm.
 * User: lukin
 * Date: 15/3/26
 * Time: 15:56
 */

namespace Mylukin\Ip2city;

class IP2City
{

    private static $ip = NULL;
    private static $fp = NULL;
    private static $offset = NULL;
    private static $index = NULL;
    private static $cached = array();
    private static $datFile = '17monipdb.dat';

    // IP17Mon instance
    private static $instance;

    /**
     * Returns IP17Mon instance.
     *
     * @static
     * @param string $dat_file
     * @return IP17Mon
     */
    public static function &instance($dat_file)
    {
        if (!self::$instance) {
            self::$instance = new IP17Mon($dat_file);
        }
        return self::$instance;
    }

    public function __construct($dat_file)
    {
        self::$datFile = $dat_file;
    }


    public static function ip2addr($ip)
    {
        if (empty($ip) === TRUE) {
            return 'N/A';
        }

        $nip = gethostbyname($ip);
        $ipdot = explode('.', $nip);

        if ($ipdot[0] < 0 || $ipdot[0] > 255 || count($ipdot) !== 4) {
            return 'N/A';
        }

        if (isset(self::$cached[$nip]) === TRUE) {
            return self::$cached[$nip];
        }

        if (self::$fp === NULL) {
            self::init();
        }

        $nip2 = pack('N', ip2long($nip));

        $tmp_offset = (int)$ipdot[0] * 4;
        $start = unpack('Vlen', self::$index[$tmp_offset] . self::$index[$tmp_offset + 1] . self::$index[$tmp_offset + 2] . self::$index[$tmp_offset + 3]);

        $index_offset = $index_length = NULL;
        $max_comp_len = self::$offset['len'] - 1024 - 4;
        for ($start = $start['len'] * 8 + 1024; $start < $max_comp_len; $start += 8) {
            if (self::$index{$start} . self::$index{$start + 1} . self::$index{$start + 2} . self::$index{$start + 3} >= $nip2) {
                $index_offset = unpack('Vlen', self::$index{$start + 4} . self::$index{$start + 5} . self::$index{$start + 6} . "\x0");
                $index_length = unpack('Clen', self::$index{$start + 7});

                break;
            }
        }

        if ($index_offset === NULL) {
            return 'N/A';
        }

        fseek(self::$fp, self::$offset['len'] + $index_offset['len'] - 1024);

        self::$cached[$nip] = explode("\t", fread(self::$fp, $index_length['len']));

        return self::$cached[$nip];
    }

    private static function init()
    {
        if (self::$fp === NULL) {
            self::$ip = new self();

            self::$fp = fopen(self::$datFile, 'rb');
            if (self::$fp === FALSE) {
                throw new Exception('Invalid 17monipdb.dat file!');
            }

            self::$offset = unpack('Nlen', fread(self::$fp, 4));
            if (self::$offset['len'] < 4) {
                throw new Exception('Invalid 17monipdb.dat file!');
            }

            self::$index = fread(self::$fp, self::$offset['len'] - 4);
        }
    }

    public function __destruct()
    {
        if (self::$fp !== NULL) {
            fclose(self::$fp);
        }
    }
}
