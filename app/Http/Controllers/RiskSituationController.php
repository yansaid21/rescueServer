<?php

namespace App\Http\Controllers;

use App\Models\RiskSituation;
use App\Models\Institution;
use Illuminate\Http\Request;
use App\Http\Requests\StoreRiskSituationRequest;
use App\Http\Requests\UpdateRiskSituationRequest;

class RiskSituationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Institution $institution)
    {
        $riskSituations = $institution->riskSituations();
        $query = $request->query();

        if (isset($query['name'])) {
            $riskSituations->where('name', 'like', '%' . $query['name'] . '%');
        }

        $perPage = isset($query['per_page'])  && $query['per_page'] > 0 ? $query['per_page'] : $riskSituations->count();
        $riskSituations = $riskSituations->orderBy('updated_at', 'desc')->paginate($perPage)->withQueryString();

        return response()->json([
            'data' => $riskSituations->items(),
            'pagination' => [
                'total' => $riskSituations->total(),
                'per_page' => $riskSituations->perPage(),
                'current_page' => $riskSituations->currentPage(),
                'total_pages' => $riskSituations->lastPage(),
                'last_page' => $riskSituations->lastPage(),
                'next_page_url' => $riskSituations->nextPageUrl(),
                'prev_page_url' => $riskSituations->previousPageUrl(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRiskSituationRequest $request, Institution $institution)
    {
        $request['institution_id'] = $institution->id;
        $riskSituation = RiskSituation::create($request->all());

        return response()->json(['data' => $riskSituation, 'message' =>  __('messages.created', ['Model' => __('risk situation')])], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Institution $institution, RiskSituation $riskSituation)
    {
        if ($riskSituation->institution_id !== $institution->id) {
            return response()->json(['message' => __('messages.not_found_in_institution', ['Model' => __('risk situation')])], 404);
        }

        return response()->json(['data' => $riskSituation]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRiskSituationRequest $request, Institution $institution, RiskSituation $riskSituation)
    {
        if ($riskSituation->institution_id !== $institution->id) {
            return response()->json(['message' => __('messages.not_found_in_institution', ['Model' => __('risk situation')])], 404);
        }

        $riskSituation->update($request->all());

        return response()->json(['data' => $riskSituation, 'message' => __('messages.updated', ['Model' => __('risk situation')])]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Institution $institution, RiskSituation $riskSituation)
    {
        try {
            $riskSituation->delete();
            return response()->json(['message' => __('messages.deleted', ['Model' => __('risk situation')])]);
        } catch (\Exception $e) {

            $resources = [];

            if ($riskSituation->protocols()->count() > 0) {
                $resources[] = __('protocols');
            }

            if ($riskSituation->incidents()->count() > 0) {
                $resources[] = __('incidents');
            }

            $resourceList = implode(', ', $resources);

            return response()->json(['message' => __('messages.cannot_delete', ['Model' => __('risk situation'), 'resources' => $resourceList])], 400);
        }
    }
}
