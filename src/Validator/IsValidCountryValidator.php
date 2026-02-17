<?php

namespace App\Validator;

use App\Entity\Country;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsValidCountryValidator extends ConstraintValidator
{
    public function __construct(private readonly EntityManagerInterface $em) {}

    /**
     * @param            $value      $the value that should be validated
     * @param Constraint $constraint which is the constraint class linked to this validator (IsValidCountry)
     *
     * @return void
     */
    public function validate($value, Constraint $constraint)
    {
        assert($constraint instanceof IsValidCountry);

        if (null === $value || '' === $value) {
            return;
        }

        if (!$this->em->getRepository(Country::class)->findOneBy(['id' => $value])) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }

    }
}
