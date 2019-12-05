<?php
/**
 * Created by NYXLab.
 * User: Rifal Pramadita G
 * Date: 05/12/2019
 * Time: 14.17
 */

namespace Rifalpg\GDriveDirect;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Rifalpg\GDriveDirect\Exceptions\DirectException;

class Direct
{
    protected $original_url;
    protected $client;
    protected $direct_link;

    public function __construct()
    {
        $this->client = new Client(['cookies' => true]);
    }

    public function download($dir, $url = null, $limit = null)
    {
        $direct_link = $url ? $this->getDirectLink($url) : $this->direct_link;
        $info = $this->getInfo($direct_link);
        $save_to = $info->file = "$dir/$info->filename";
        $this->client->get($direct_link, [
            'sink' => $save_to,
            'on_headers' => function (ResponseInterface $response) use ($limit) {
                if ($limit) {
                    if ($response->getHeaderLine('Content-Length') > $limit) {
                        throw new DirectException('File is to big!', 69, $response);
                    }
                }
            }
        ]);

        return $info;
    }

    public function getDirectLink($url)
    {
        $this->original_url = $url;
        $id = $this->parseID();
        if ($id !== null) {
            $this->direct_link = $direct_link = "https://drive.google.com/uc?export=download&id=$id";
            $head = $this->client->head($direct_link, ['allow_redirects' => false]);

            //CHECK VIRUS PAGE
            if ($head->getHeader('content-type')[0] == 'text/html; charset=utf-8') {
                $html = $this->client->get($direct_link)->getBody()->getContents();
                preg_match('/confirm=(.*)&amp;id=(.*)">Download anyway/', $html, $confirm);
                $confirm_link = "https://drive.google.com/uc?export=download&confirm={$confirm[1]}&id=$id";
                $moved = $this->client->head($confirm_link, ['allow_redirects' => false]);
                $this->direct_link = $direct_link = $moved->getHeader('location')[0];
            }
            return $direct_link;
        }

        return null;
    }

    public function getInfo($direct_link = null)
    {
        $direct_link = $direct_link ? $this->direct_link : null;
        try {
            $this->client->get($direct_link, [
                'on_headers' => function (ResponseInterface $response) {
                    if ($response->getHeaderLine('Content-Length') > 0) {
                        throw new DirectException('trick', 69, $response);
                    }
                }
            ]);

            return null;

        } catch
        (\Exception $e) {
            $response = $e->getPrevious()->getResponse();
            preg_match("/filename=\"(.*?)\"/", $response->getHeaderLine('Content-Disposition'), $fn);
            $return = [
                'filename' => $fn[1],
                'size' => $response->getHeaderLine('Content-Length'),
                'type' => $response->getHeaderLine('Content-Type'),
            ];

            return (object)$return;
        }
    }

    public function parseID($url = null, $try = 0)
    {
        if ($url != null) $this->original_url = $url;
        $reg = [
            '/\/file\/d\/(.*?)\//',
            "/id=(.*)/"
        ];
        preg_match($reg[$try], $this->original_url, $out);
        if (!count($out)) return $try + 1 < count($reg) ? $this->parseID($this->original_url, $try + 1) : null;

        return $out[1];
    }
}