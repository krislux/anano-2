<?php namespace Anano\Security\Validation;

use ErrorException;

class Validator
{
    private $failed = [];

    /**
     * Static instantiation shorthand
     */
    public static function make($subject, $rules)
    {
        return new self($subject, $rules);
    }

    /**
     * Create and run a validation. The constructor accepts both single inputs and arrays,
     * the latter probably being more useful, so this should be used in general.
     * @param  mixed  $subject  Either string or associative array of values ($_REQUEST goes neatly here).
     * @param  mixed  $rules    Either string or associative array of subject keys => rules.
     */
    public function __construct($subject = null, $rules = null)
    {
        if ( ! $subject || ! $rules)
            return;
        
        if (is_array($subject))
        {
            foreach ($rules as $rule_key => $rule_val)
            {
                $subject_val = isset($subject[$rule_key]) ? $subject[$rule_key] : null;
                $rv = $this->validate($subject_val, $rule_val, true);
                if ($rv !== true)
                {
                    $this->failed[$rule_key] = $rv;
                }
            }
        }
        else
        {
            $rv = $this->validate($subject, $rules, true);
            if ($rv !== true)
            {
                $rule = current(explode('|', $rules));
                $this->failed[$rule] = $rv;
            }
        }
    }

    /**
     * Single validation run. Can be called publicly, but using the constructor is more efficient.
     * @param  string  $subject        String to perform the validation on.
     * @param  string  $rule           String of rules separated by |. E.g. "required|email|min:5"
     * @param  bool    $return_errors  If true, returns array of failed rules instead of simple bool.
     * @return bool or array
     */
    public function validate($subject, $rule, $return_errors = false)
    {
        $rules = $this->parseRules($rule);

        $errors = [];

        foreach ($rules as $rule => $value)
        {
            $method = 'validate' . ucfirst($rule);
            if (method_exists($this, $method))
            {
                if ( ! $this->$method($subject, $value) )
                {
                    $errors[] = $rule;
                }
            }
            else
            {
                throw new ErrorException("Invalid validation rule `$rule`");
            }
        }

        if (empty($errors))
            return true;

        if ($return_errors)
            return $errors;
        return false;
    }

    /**
     * Converts rule string to key/val set. Rules without values have value null.
     * @param   string  $rules
     * @return  array
     */
    private function parseRules($rules)
    {
        $output = [];
        $rules = explode('|', $rules);
        foreach ($rules as $rule)
        {
            $tmp = explode(':', $rule);
            $output[$tmp[0]] = isset($tmp[1]) ? $tmp[1] : null;
        }
        return $output;
    }

    /**
     * Returns a simple bool of whether all validation rules have passed.
     * Also true before any validation is run.
     * @return bool
     */
    public function success()
    {
        return empty($this->failed);
    }

    /**
     * Reverse of success, except if one or more rules have failed,
     * it returns an array of failed fields instead of bool.
     * @return bool or array
     */
    public function fails()
    {
        if (empty($this->failed))
            return false;
        return array_keys($this->failed);
    }

    /**
     * Returns array with failed fields as keys and failed rules as sub-arrays.
     * @return array
     */
    public function errors()
    {
        return $this->failed;
    }


    /**
     * =========================================================================
     * Validation rules
     * =========================================================================
     */


    // String must not be empty.
    public function validateRequired($subject)
    {
        return !empty($subject);
    }

    // String must be shorter than this length.
    public function validateMax($subject, $value)
    {
        return strlen($subject) <= $value;
    }

    // String must be longer than this length.
    public function validateMin($subject, $value)
    {
        return strlen($subject) >= $value;
    }

    // String must be exactly this length.
    public function validateLength($subject, $value)
    {
        return strlen($subject) == $value;
    }

    // Weak email validation. Does not accept whitespace or IP-emails but should accept all others, including IDN.
    public function validateEmail($subject)
    {
        return preg_match('/^[^\s]+\@[^\s]+\.[a-z]{2,}$/i', $subject);
    }

    // String must only contain numbers. No decimal points or separators allowed. Datatype not enforced.
    public function validateInteger($subject)
    {
        return preg_match('/^[0-9]*$/', $subject);
    }

    // String must be numeric value. Accepts comma and period symbols. Datatype not enforced.
    public function validateNumeric($subject)
    {
        return preg_match('/^[0-9\.,]*$/', $subject);
    }

    // String must only contain alphanumeric English characters.
    public function validateAlpha($subject)
    {
        return preg_match('/^[0-9a-z]*$/', $subject);
    }

    // String must only contain hexadecimal characters.
    public function validateHex($subject)
    {
        return preg_match('/^[0-9a-f]*$/', $subject);
    }

    // String must be valid JSON.
    public function validateJson($subject)
    {
        if (empty($subject))
            return true;
        if (is_array($subject))
            return true;
        return json_decode($subject) !== null;
    }

    // Phone numbers, including whitespace, () and + signs accepted. Optional length is for pure digits,
    // excluding above characters, and does not require the field to be filled. Use require for that.
    public function validatePhone($subject, $length = null)
    {
        if ($subject && $length !== null)
        {
            $clean = preg_replace('/[^\d]/', '', $subject);
            if (strlen($clean) != $length)
                return false;
        }
        return preg_match('/^[0-9 \(\)\+]*$/', $subject);
    }
}
