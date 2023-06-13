<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Dossier;
use App\Models\DossierDetails;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;


use App\Http\Resources\V1\DossierResource;
use App\Http\Resources\V1\DossierCollection;
use App\Filters\V1\DossiersFilter;
use illuminate\Support\Arr;
use App\Http\Requests\V1\BulkStoreDossierRequest;
use PhpParser\Node\Expr\Cast\Object_;

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
        $includeDetails = request()->query('includeDetails');

        if ($includeDetails) {
            return new DossierResource($dossier->loadMissing('dossierDetails'));
        }
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


    public function showWithScrapedData($id)
    {
        // Find the dossier by ID
        $dossier = Dossier::findOrFail($id);

        $dossierName = $dossier->name;
        $year = 2022;
        $cadastral_service = 83002;

        $urlParams = [
            'a' => $dossierName,
            'b' => $cadastral_service,
            'y' => $year
        ];

        // Fetch the URL and dossier ID
        // $url = 'https://www.ancpi.ro/aplicatii/urmarireCerereRGI/apptrack.php?b=83002&y=2022&a=145538';
        $url = 'https://www.ancpi.ro/aplicatii/urmarireCerereRGI/apptrack.php?' . http_build_query($urlParams);

        // Fetch the HTML content
        $response = Http::get($url);
        $html = $response->body();

        // Parse the HTML using Symfony's DomCrawler
        $crawler = new Crawler($html);



        // Define the mapping array for field mapping
        $fieldMappingDetail = [
            'Data înregistrare:' => 'received_date',
            'Termen soluționare:' => 'completion_date',
            'Obiectul cererii:' => 'request_type',
            'Stare curentă:' => 'status',
        ];
        $fieldMappingHistory = [
            'Data' => 'received_date',
            'Actiune' => 'completion_date',
            'Stare' => 'request_type',
            'Actor' => 'status',
        ];

        // Find the table with class "tabelv"
        // AppDetails
        $tableRowsAppDetail = $crawler->filter('#AppDetail tr');

        // Create a new instance of DossierDetails model
        // TODO - if response is ok, create the details

        // else return
        $dossierDetails = new DossierDetails();



        // Loop through the rows and extract the cell data
        $tableRowsAppDetail->each(function ($row) use ($dossierDetails, $fieldMappingDetail) {

            $label = trim($row->filter('td:nth-of-type(1)')->text());
            $value = trim($row->filter('td:nth-of-type(2)')->text());
            // Map the crawled label to the field name
            $fieldName = $fieldMappingDetail[$label] ?? null;

            // If a matching field name is found, set it on the model instance
            if ($fieldName) {
                $dossierDetails->{$fieldName} = $value;
            }
        });


        // History Crawler

        $tableRowsAppHist = $crawler->filter('#AppHist tr:not(.tabel_categorii)');

        if ($tableRowsAppHist->count() > 0) {
            $history = array();
            foreach ($tableRowsAppHist as $row) {
                $rowElem = new Crawler($row);
                $historyItem = array();

                foreach ($rowElem as $cell) {
                    $cellElem = new Crawler($cell);
                    $historyItem['date'] = $cellElem->filter('td:nth-child(1)')->text();
                    $historyItem['action'] = $cellElem->filter('td:nth-child(2)')->text();
                    $historyItem['status'] = $cellElem->filter('td:nth-child(3)')->text();
                    $historyItem['actor'] = $cellElem->filter('td:nth-child(4)')->text();
                };

                array_push($history, $historyItem);
            };
            $dossierDetails->history = json_encode($history);
        }




        // Notes Crawler
        if ($crawler->matches('#AppNote tbody')) {
            $tableRowsAppNotes = $crawler->filter('#AppNote tbody')->children('tr');

            $tableRowsAppNotes->each(function (Crawler $row) use ($dossierDetails) {
                $notes = [];
                $cells = $row->filter('td')->each(function ($cell) {
                    $notes[] = $cell->text();
                });
                $dossierDetails->notes = json_encode($notes);
            });
        }



        // Associate the dossier details with the dossier
        $dossier->dossierDetails()->save($dossierDetails);

        // Save the scraped data
        $dossierDetails->save();

        // Return the dossier with the scraped data as JSON response
        return response()->json($dossier);
    }
}
