<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ContainUppercaseCharacterValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        /* @var $constraint \App\Validator\ContainUppercaseCaracter */

        if (null === $value || '' === $value) 
        {
            return;
        }
        
        if (!is_string($value)) 
        {
            throw new UnexpectedValueException($value, 'string');
        }

        if (!preg_match("#[A-Z]+#", $value)) 
        {
            $this->context->buildViolation($constraint->message)
                ->addViolation();     
        }
    }
}
