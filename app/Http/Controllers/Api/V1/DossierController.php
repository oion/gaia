<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Dossier;
use App\Models\DossierDetails;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;
use Carbon\Carbon;


use App\Http\Resources\V1\DossierResource;
use App\Http\Resources\V1\DossierCollection;
use App\Filters\V1\DossiersFilter;
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


    /**
     * Show the dossier with scraped data.
     *
     * @param int $id The ID of the dossier
     * @return \Illuminate\Http\JsonResponse
     */
    public function showWithScrapedData(int $id)
    {
        try {
            // Find the dossier by ID
            $dossier = Dossier::findOrFail($id);
            $dossierDetails = $dossier->dossierDetails ?? new DossierDetails();

            // Scrape and save the data
            $this->scrapeDossierData($dossier, $dossierDetails);

            // Return the dossier with the scraped data as JSON response
            return response()->json($dossier);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Scrape the dossier data and save it to the DossierDetails model.
     *
     * @param \App\Dossier $dossier The dossier model
     * @param \App\DossierDetails $dossierDetails The dossier details model
     */
    private function scrapeDossierData(Dossier $dossier, DossierDetails $dossierDetails)
    {
        $urlParams = [
            'a' => $dossier->name,
            'b' => 83002,
            'y' => 2022,
        ];
        $url = 'https://www.ancpi.ro/aplicatii/urmarireCerereRGI/apptrack.php?' . http_build_query($urlParams);

        // Fetch the HTML content
        $html = Http::get($url)->body();
        $crawler = new Crawler($html);

        // Scrape the application details
        $this->scrapeAppDetails($crawler, $dossierDetails);

        // Scrape the application history
        $this->scrapeAppHistory($crawler, $dossierDetails);

        // Scrape the application notes
        $this->scrapeAppNotes($crawler, $dossierDetails);

        // Save the scraped data
        $dossier->dossierDetails()->save($dossierDetails);
    }

    /**
     * Scrape the application details from the HTML.
     *
     * @param \Symfony\Component\DomCrawler\Crawler $crawler The HTML crawler
     * @param \App\DossierDetails $dossierDetails The dossier details model
     */
    private function scrapeAppDetails(Crawler $crawler, DossierDetails $dossierDetails)
    {
        $tableRowsAppDetail = $crawler->filter('#AppDetail tr');

        // Registration date
        $dossierDetails->received_date = Carbon::createFromFormat('d.m.Y', $tableRowsAppDetail->eq(1)->filter('td:nth-child(2)')->text())->toDateString();

        // Completion date
        $dossierDetails->completion_date = Carbon::createFromFormat('d.m.Y', $tableRowsAppDetail->eq(2)->filter('td:nth-child(2)')->text())->toDateString();

        // Request subject
        $dossierDetails->request_type = $tableRowsAppDetail->eq(3)->filter('td:nth-child(2)')->text();

        // Status
        $dossierDetails->status = $tableRowsAppDetail->eq(4)->filter('td:nth-child(2)')->text();
    }

    /**
     * Scrape the application history from the HTML.
     *
     * @param \Symfony\Component\DomCrawler\Crawler $crawler The HTML crawler
     * @param \App\DossierDetails $dossierDetails The dossier details model
     */
    private function scrapeAppHistory(Crawler $crawler, DossierDetails $dossierDetails)
    {
        $tableRowsAppHist = $crawler->filter('#AppHist tr:not(.tabel_categorii)');

        if ($tableRowsAppHist->count() > 0) {
            $history = [];

            foreach ($tableRowsAppHist as $row) {
                $rowElem = new Crawler($row);
                $historyItem = [
                    'date' => Carbon::createFromFormat('d.m.Y', $rowElem->filter('td:nth-child(1)')->text())->toDateString(),
                    'action' => $rowElem->filter('td:nth-child(2)')->text(),
                    'status' => $rowElem->filter('td:nth-child(3)')->text(),
                    'actor' => $rowElem->filter('td:nth-child(4)')->text(),
                ];
                $history[] = $historyItem;
            }

            $dossierDetails->history = json_encode($history);
        }
    }

    /**
     * Scrape the application notes from the HTML.
     *
     * @param \Symfony\Component\DomCrawler\Crawler $crawler The HTML crawler
     * @param \App\DossierDetails $dossierDetails The dossier details model
     */
    private function scrapeAppNotes(Crawler $crawler, DossierDetails $dossierDetails)
    {
        $tableRowsAppNotes = $crawler->filter('#AppNote tr:not(.tabel_categorii)');

        if ($tableRowsAppNotes->count() > 0) {
            $notes = [];

            foreach ($tableRowsAppNotes as $row) {
                $rowElem = new Crawler($row);
                $noteItem = [
                    'date' => Carbon::createFromFormat('d.m.Y', $rowElem->filter('td:nth-child(1)')->text())->toDateString(),
                    'observations' => $rowElem->filter('td:nth-child(2)')->text(),
                    'compartment' => $rowElem->filter('td:nth-child(3)')->text(),
                ];

                $notes[] = $noteItem;
            }

            $dossierDetails->notes = json_encode($notes);
        }
    }
}
