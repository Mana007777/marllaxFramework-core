<?php


namespace Core\Exceptions;


class NotFoundException extends \Exception 
{
    protected $message = "page not found";
    protected $code = 404;
}