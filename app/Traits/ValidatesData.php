<?php

namespace App\Traits;

use Illuminate\Contracts\Validation\Factory;
use Illuminate\Validation\ValidationException;

/**
 * @mixin \App\Http\Controllers\Controller
 */
trait ValidatesData
{
    /**
     * Run the validation routine against the given validator.
     *
     * @param  \Illuminate\Contracts\Validation\Validator|array  $validator
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validateDataWith($validator, array $data): array
    {
        if (is_array($validator)) {
            $validator = $this->getValidationDataFactory()->make($data, $validator);
        }

        return $validator->validate();
    }

    /**
     * Validate the given request with the given rules.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validateData(
        array $data,
        array $rules,
        array $messages = [],
        array $customAttributes = []
    ): array {
        return $this->getValidationDataFactory()->make(
            $data,
            $rules,
            $messages,
            $customAttributes
        )->validate();
    }

    /**
     * Validate the given request with the given rules.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validateDataWithBag(
        string $errorBag,
        array $data,
        array $rules,
        array $messages = [],
        array $customAttributes = []
    ): array {
        try {
            return $this->validateData($data, $rules, $messages, $customAttributes);
        } catch (ValidationException $e) {
            $e->errorBag = $errorBag;

            throw $e;
        }
    }

    /**
     * Get a validation factory instance.
     *
     * @return \Illuminate\Contracts\Validation\Factory
     */
    protected function getValidationDataFactory()
    {
        return app(Factory::class);
    }
}
