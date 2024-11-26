<?php

namespace App\Http\Controllers;

use App\Models\Protocol;
use App\Models\RiskSituation;
use App\Models\Institution;
use Illuminate\Http\Request;
use App\Http\Requests\StoreProtocolRequest;
use App\Http\Requests\UpdateProtocolRequest;

class ProtocolController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Institution $institution, RiskSituation $riskSituation)
    {
        $protocols = $riskSituation->protocols();
        $query = $request->query();

        if (isset($query['name'])) {
            $protocols->where('name', 'like', '%' . $query['name'] . '%');
        }

        $perPage = isset($query['per_page'])  && $query['per_page'] > 0 ? $query['per_page'] : $protocols->count();
        $protocols = $protocols->orderBy('updated_at', 'desc')->paginate($perPage)->withQueryString();

        return response()->json([
            'data' => $protocols->items(),
            'pagination' => [
                'total' => $protocols->total(),
                'per_page' => $protocols->perPage(),
                'current_page' => $protocols->currentPage(),
                'total_pages' => $protocols->lastPage(),
                'last_page' => $protocols->lastPage(),
                'next_page_url' => $protocols->nextPageUrl(),
                'prev_page_url' => $protocols->previousPageUrl(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProtocolRequest $request, Institution $institution, RiskSituation $riskSituation)
    {
        $request['risk_situation_id'] = $riskSituation->id;
        $protocol = Protocol::create($request->all());

        return response()->json(['data' => $protocol, 'message' =>  __('messages.created', ['Model' => __('protocol')])], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Institution $institution, RiskSituation $riskSituation, Protocol $protocol)
    {
        return response()->json(['data' => $protocol]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProtocolRequest $request, Institution $institution, RiskSituation $riskSituation, Protocol $protocol)
    {
        if ($protocol->risk_situation_id !== $riskSituation->id) {
            return response()->json(['message' => __('messages.not_found_in_risk_situation', ['Model' => __('protocol')])], 404);
        }

        $protocol->update($request->all());

        return response()->json(['data' => $protocol, 'message' => __('messages.updated', ['Model' => __('protocol')])]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Institution $institution, RiskSituation $riskSituation, Protocol $protocol)
    {
        $protocol->delete();

        return response()->json(['message' => __('messages.deleted', ['Model' => __('protocol')])]);
    }
}
