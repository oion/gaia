<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkStoreDossierRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        return $user != NULL && $user->tokenCan('dossier:create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {

        return [
            '*.name' => ['required'],
            '*.bcpiId' => ['required'],
            '*.customerId' => ['required', 'integer'],
            '*.status' => ['required',  Rule::in(['completed', 'ongoing', 'rejected'])],
            '*.statusDate' => ['required', 'date_format:Y-m-d H:i:s']
        ];
    }

    protected function prepareForValidation()
    {

        $data = [];
        foreach ($this->toArray() as $obj) {
            $obj['customer_id'] = $obj['customerId'] ?? null;
            $obj['bcpi_id'] = $obj['bcpiId'] ?? null;
            $obj['status_date'] = $obj['statusDate'] ?? null;

            $data[] = $obj;
        }
        $this->merge($data);
    }
}
