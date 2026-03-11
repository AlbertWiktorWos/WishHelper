<?php

namespace App\Validator;

use App\Entity\Currency;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsValidCurrencyCodeValidator extends ConstraintValidator
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    /**
     * @param            $value      $the value that should be validated
     * @param Constraint $constraint which is the constraint class linked to this validator (IsValidCurrencyCode)
     *
     * @return void
     */
    public function validate($value, Constraint $constraint)
    {
        assert($constraint instanceof IsValidCurrencyCode);

        if (null === $value || '' === $value) {
            return;
        }

        if (!$this->em->getRepository(Currency::class)->findOneBy(['code' => $value])) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
