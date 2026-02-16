<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class LoginUserDto
{
    #[Assert\NotBlank(message: 'Email cannot be empty')]
    #[Assert\Email(message: 'Invalid email address')]
    public string $email;

    #[Assert\NotBlank(message: 'Password cannot be empty')]
    public string $password;

    public function __construct(string $email, string $password)
    {
        $this->email = $email;
        $this->password = $password;
    }
}
