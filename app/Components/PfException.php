<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/6
 * Time: 3:33 PM
 */

namespace App\Components;


use Throwable;

class PfException extends \Exception
{
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
