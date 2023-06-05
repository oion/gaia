<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Dossier;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\DossierResource;
use App\Http\Resources\V1\DossierCollection;
use App\Filters\V1\DossiersFilter;
use illuminate\Support\Arr;
use App\Http\Requests\V1\BulkStoreDossierRequest;



class DossierController extends Controller
{
    /**
     * Display a listing of the resource.
     * 
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filter = new DossiersFilter();
        $queryItems = $filter->transform($request); //[['column', 'operator', 'value']]


        if (count($queryItems) == 0) { //if there are no query arguments
            return new DossierCollection(Dossier::paginate());
        } else {
            $dossiers = Dossier::where($queryItems)->paginate();

            return new DossierCollection($dossiers->appends($request->query()));
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    public function bulkStore(BulkStoreDossierRequest $request)
    {
        $bulk = collect($request->all())->map(function ($arr, $key) {
            return Arr::except($arr, ['customerId', 'bcpiId', 'statusDate']);
        });

        Dossier::insert($bulk->toArray());
    }
    /**
     * Display the specified resource.
     */
    public function show(Dossier $dossier)
    {
        return new DossierResource($dossier);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Dossier $dossier)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDossierRequest $request, Dossier $dossier)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Dossier $dossier)
    {
        //
    }
}
