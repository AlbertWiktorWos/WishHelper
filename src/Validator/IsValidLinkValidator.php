<?php

namespace App\Validator;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsValidLinkValidator extends ConstraintValidator
{
    /**
     * @param            $value      $the value that should be validated
     * @param Constraint $constraint which is the constraint class linked to this validator (IsValidLink)
     *
     * @return void
     */
    public function validate($value, Constraint $constraint)
    {
        assert($constraint instanceof IsValidLink);

        if (null === $value || '' === $value) {
            return;
        }

        $scheme = parse_url($value, PHP_URL_SCHEME);
        if (!in_array($scheme, ['http', 'https'], true)) {
            $this->context->buildViolation(' Make sure that link contains https:// and is not a link to a disallowed domain.')
                ->atPath('info')
                ->addViolation();
        }
    }
}
