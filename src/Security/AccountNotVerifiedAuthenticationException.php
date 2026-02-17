<?php


namespace App\Security;


use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * Only to check which exception we are throwing
 * Class AccountNotVerifiedAuthenticationException
 * @package App\Security
 */
class AccountNotVerifiedAuthenticationException extends AuthenticationException
{

}