<?php namespace Anano\Security\Validation;

trait ValidatorTrait
{
    protected function validate($subject, $rules = null, callable $callback = null)
    {
        // Allow skipping first parameter
        if ( is_array($subject) && $callback === null && ($rules === null || is_callable($rules)) )
        {
            $callback = $rules;
            $rules = $subject;
            $subject = $_REQUEST;
        }

        // Do the validation
        $v = Validator::make($subject, $rules);

        // If validation fails and a callback is defined, run it.
        // NB: Anything returned from this callback will be printed and the process ended.
        if ($callback && $v->fails())
        {
            $rv = $callback($v->errors());

            if ($rv !== null)
            {
                global $app;
                $app->end($rv);
            }
            return $rv;
        }

        return $v;
    }
}
