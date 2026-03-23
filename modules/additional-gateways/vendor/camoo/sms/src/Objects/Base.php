<?php

declare(strict_types=1);

namespace Camoo\Sms\Objects;

use Camoo\Sms\Entity\Recipient;
use Camoo\Sms\Exception\CamooSmsException;
use Camoo\Sms\Lib\Utils;
use Valitron\Validator;

/**
 * Class Objects\Base
 */
class Base implements ObjectEntityInterface
{
    private const CM_CARRIERS = ['MTN', 'ORANGE'];

    public static function create(): Base
    {
        return new self();
    }

    public function set(string $property, mixed $value, ?object $class = null): void
    {
        if ($class === null) {
            return;
        }
        if (!property_exists($class, $property)) {
            throw new CamooSmsException([$property => 'is not allowed!']);
        }
        if ($property === 'from') {
            $value = Utils::clearSender($value);
        }
        if ($property === 'to') {
            $value = Utils::makeNumberE164Format($value);
        }

        $class->$property = $value;
    }

    public function get(object $oClass, string $validator = 'default'): array
    {
        $hPayload = get_object_vars($oClass);
        if (method_exists($oClass, 'validator' . ucfirst($validator))) {
            $sValidator = 'validator' . ucfirst($validator);
            $oValidator = $oClass->$sValidator(new Validator($hPayload));
            if ($oValidator->validate() === false) {
                throw new CamooSmsException($oValidator->errors());
            }
        }
        if (array_key_exists('route', $hPayload) && $hPayload['route'] === 'classic' &&
            $oClass instanceof Message && array_key_exists('to', $hPayload)) {
            $asTo = $hPayload['to'];
            foreach ($asTo as $xTo) {
                if (!Utils::isValidPhoneNumber($xTo, 'CM', true)) {
                    throw new CamooSmsException([
                        json_encode($xTo) => 'does not seems to be a cameroonian phone number!',
                    ]);
                }
            }
        }

        return array_filter($hPayload);
    }

    public function isMTNCameroon(Validator $oValidator, string $sParam): void
    {
        $oValidator
            ->rule(function (string $field, mixed $value) {
                if (empty($value) || empty($field)) {
                    return false;
                }

                return Utils::isCmMTN($value);
            }, $sParam)->message('{field} is not carried by MTN Cameroon');
    }

    public function isValidUTF8Encoded(Validator $oValidator, string $sParam): void
    {
        $oValidator
            ->rule(function (string $field, mixed $value) {
                return mb_check_encoding($value, 'UTF-8');
            }, $sParam)->message('{field} needs to be a valid UTF-8 encoded string');
    }

    public function notEmptyRule(Validator $oValidator, string $sParam): void
    {
        $oValidator
            ->rule(function (string $field, mixed $value) {
                return !empty($value);
            }, $sParam)->message('{field} can not be blank/empty...');
    }

    // @codeCoverageIgnoreEnd

    public function isPossibleNumber(Validator $oValidator, string $sParam): void
    {
        $oValidator
            ->rule(function (string $field, mixed $value) {
                if (empty($value) || empty($field)) {
                    return false;
                }

                foreach ($value as $xTo) {
                    $sTo = $this->findPhoneNumber($xTo);
                    $xTel = preg_replace('/[^\dxX]/', '', $sTo);
                    $xTel = ltrim($xTel, '0');
                    if (!is_numeric($xTel) || mb_strlen($xTel) <= 10 || mb_strlen($xTel) > 15) {
                        return false;
                    }
                }

                return true;
            }, $sParam)->message('{field} no (correct) phone number found!');
    }

    public function has(string $sName): bool
    {
        return property_exists($this, $sName);
    }

    public function canTopUpCM(Validator $validator, string $parameter): void
    {
        $validator
            ->rule(function (string $field, mixed $value) {
                if (empty($value)) {
                    return false;
                }
                if ($field !== 'phonenumber') {
                    return false;
                }

                return in_array(Utils::getPhoneCarrier($value), self::CM_CARRIERS, true);
            }, $parameter)->message('{field} is not carried by MTN or Orange Cameroon');
    }

     /**
      * @codeCoverageIgnore
      */
     public function validatorDefault(Validator $validator): Validator
     {
         return $validator;
     }

    private function findPhoneNumber(mixed $recipient): string
    {
        if (is_string($recipient)) {
            return trim($recipient);
        }
        if ($recipient instanceof Recipient) {
            return $recipient->phoneNumber;
        }

        return is_array($recipient) && !empty($recipient['mobile']) ? $recipient['mobile'] : $recipient;
    }
}
