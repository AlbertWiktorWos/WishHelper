<?php

namespace App\Dto;

use App\Validator\IsValidCountry;
use Symfony\Component\Validator\Constraints as Assert;

class RegisterUserDto
{
    #[Assert\NotBlank(message: 'Email cannot be empty')]
    #[Assert\Email(message: 'Invalid email address')]
    public string $email;

    #[Assert\NotBlank(message: 'Password cannot be empty')]
    #[Assert\Length(min: 8, minMessage: 'Password must be at least {{ limit }} characters long')]
    public string $password;

    #[Assert\NotBlank(message: 'Nick nie może być pusty')]
    #[Assert\Length(max: 50, maxMessage: 'The nickname can have a maximum of {{ limit }} characters')]
    public string $nickName;

    #[IsValidCountry]
    #[Assert\NotBlank(message: 'The country must be chosen')]
    public string $country; // This will be the country ID

    public function __construct(string $email, string $password, string $nickName, string $country)
    {
        $this->email = $email;
        $this->password = $password;
        $this->nickName = $nickName;
        $this->country = preg_replace('#^/api/countries/#', '', $country);
    }
}
