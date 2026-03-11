<?php

namespace App\Validator;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsValidCategoryNameValidator extends ConstraintValidator
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    /**
     * @param            $value      $the value that should be validated
     * @param Constraint $constraint which is the constraint class linked to this validator (IsValidCategory)
     *
     * @return void
     */
    public function validate($value, Constraint $constraint)
    {
        assert($constraint instanceof IsValidCategoryName);

        if (null === $value || '' === $value) {
            return;
        }

        if (!$this->em->getRepository(Category::class)->findOneBy(['name' => ucfirst($value)])) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
