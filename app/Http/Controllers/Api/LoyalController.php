<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\MultipleDestroyRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLoyalRequest;
use App\Models\Loyal;


class LoyalController extends Controller
{
    public function index()
    {
        $loyal = Loyal::paginate();

        return response()->json($loyal);
    }


    public function store(StoreLoyalRequest $request)
    {
        $loyals = Loyal::all();

        foreach ($loyals as $loyal) {
            if ($request->spending_min <= $loyal->spending_min) {
                return response()->json(['error' => 'Spending min is already exist in the range of ' . $loyal->name])->setStatusCode(400);
            }
        }
        $loyal = Loyal::create($request->validated());

        return response()->json($loyal)->setStatusCode(201);
    }

    public function update(StoreLoyalRequest $request, Loyal $loyal)
    {
        $loyal->update($request->all());

        return response()->json($loyal)->setStatusCode(201);
    }
    public function show(Loyal $loyal)
    {
        return response()->json($loyal)->setStatusCode(200);
    }

    public function destroy(Loyal $loyal)
    {
        $loyal->delete();

        return response('Deleted successfully', 204);
    }

    public function destroyMultiple(MultipleDestroyRequest $request)
    {
        Loyal::destroy($request->ids);

        return response('Deleted successfully', 204);
    }
}
