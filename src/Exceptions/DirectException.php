<?php
/**
 * Created by NYXLab.
 * User: Rifal Pramadita G
 * Date: 05/12/2019
 * Time: 19.17
 */

namespace Rifalpg\GDriveDirect\Exceptions;

use Psr\Http\Message\ResponseInterface;

class DirectException extends \Exception
{
    private $response;
    public function __construct($message = '', $code = 0, ResponseInterface $response, \Exception $previous = null)
    {
        $this->response = $response;
        parent::__construct($message, $code, $previous);
    }

    public function getResponse()
    {
        return $this->response;
    }
}