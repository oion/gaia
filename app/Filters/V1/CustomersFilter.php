<?php

namespace App\Filters\V1;

use Illuminate\Http\Request;
use App\Filters\ApiFilter;


class CustomersFilter extends ApiFilter
{
   protected $safeParams = [
      'type' => ['eq'],
      'county' => ['eq'],
      'postalCode' => ['eq', 'gt', 'lt'],
      'county' => ['eq']

   ];

   protected $columnMap = [
      // 'postalCode' => 'postal_code'
   ];

   protected $operatorMap = [
      'eq' => '=',
      'lt' => '<',
      'lte' => '<=',
      'gt' => '>',
      'gte' => '>=',
   ];
}
