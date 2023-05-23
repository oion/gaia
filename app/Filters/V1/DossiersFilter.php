<?php

namespace App\Filters\V1;

use Illuminate\Http\Request;
use App\Filters\ApiFilter;


class DossiersFilter extends ApiFilter
{
   protected $safeParams = [
      'bcpi_id' => ['eq'],
      'customer_id' => ['eq'],
      'name' => ['eq'],
      'status' => ['eq', 'ne']
   ];


   protected $columnMap = [
      'customer_id' => 'customer_id',
      'bcpiId' => 'bcpi_id'
   ];

   protected $operatorMap = [
      'eq' => '=',
      'lt' => '<',
      'lte' => '<=',
      'gt' => '>',
      'gte' => '>=',
      'ne' => '!=',
   ];
}
