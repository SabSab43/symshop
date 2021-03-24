<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ContainNumberValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        /* @var $constraint \App\Validator\ContainNumber */

        if (null === $value || '' === $value) 
        {
            return;
        }
        
        if (!is_string($value)) 
        {
            throw new UnexpectedValueException($value, 'string');
        }

        if (!preg_match("#[0-9]+#", $value)) 
        {
            $this->context->buildViolation($constraint->message)
                ->addViolation();     
        }
    }
}
