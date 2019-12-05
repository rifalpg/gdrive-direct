<?php
/**
 * Created by NYXLab.
 * User: Rifal Pramadita G
 * Date: 05/12/2019
 * Time: 14.17
 */

namespace Rifalpg\GDriveDirect;

use GuzzleHttp\Client;

class Direct
{
    protected $original_url;
    protected $client;

    public function __construct()
    {
        $this->client = new Client(['cookies' => true]);
    }

    public function get($url)
    {
        $this->original_url = $url;
        $id = $this->parseID();
        if ($id !== null) {
            $direct_url = "https://drive.google.com/uc?export=download&id=$id";
            $head = $this->client->head($direct_url, ['allow_redirects' => false]);

            //CHECK VIRUS PAGE
            if ($head->getHeader('content-type')[0] == 'text/html; charset=utf-8') {
                $html = $this->client->get($direct_url)->getBody()->getContents();
                preg_match('/confirm=(.*)&amp;id=(.*)">Download anyway/', $html, $confirm);
                $confirm_link = $this->info_url = "https://drive.google.com/uc?export=download&confirm={$confirm[1]}&id=$id";
                $moved = $this->client->head($confirm_link, ['allow_redirects' => false]);
                $direct_url = $moved->getHeader('location')[0];
            }
            return $direct_url;
        }

        return null;
    }


    protected function parseID($try = 0)
    {
        $reg = [
            '/\/file\/d\/(.*?)\//',
            "/id=(.*)/"
        ];
        preg_match($reg[$try], $this->original_url, $out);
        if (!count($out)) return $try < count($reg) ? $this->parseID(($try + 1)) : null;
        return $out[1];
    }
}