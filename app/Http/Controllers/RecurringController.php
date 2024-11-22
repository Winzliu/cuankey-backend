<?php

namespace App\Http\Controllers;

use App\Http\Requests\RecurringRequest;
use App\Http\Resources\RecurringResource;
use App\Models\Recurring;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RecurringController extends Controller
{
    public function getRecurring(Request $request)
    {
        $recurring = Recurring::whereIn('user_id', [$request->user()->id])->get();

        return response()->json([
            'status'  => 'success',
            'code'    => 200,
            'message' => 'Get Recurring success',
            'data'    => RecurringResource::collection($recurring)
        ], 200);
    }

    public function getRecurringById($id)
    {
        $recurring = Recurring::find($id);

        if (!$recurring) {
            return response()->json([
                'status'  => 'not found',
                'code'    => 404,
                'message' => 'Recurring not found'
            ], 404);
        }

        if (Gate::denies('private', $recurring)) {
            return response()->json([
                'status'  => 'error',
                'code'    => 403,
                'message' => 'You can only see your own Recurring.'
            ], 403);
        }

        return response()->json([
            'status'  => 'success',
            'code'    => 200,
            'message' => 'Get Recurring success',
            'data'    => new RecurringResource($recurring)
        ], 200);
    }

    public function createRecurring(RecurringRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;

        $existingRecurring = Recurring::where('user_id', $data['user_id'])
            ->where('wallet_id', $data['wallet_id'])
            ->where('category_id', $data['category_id'])
            ->where('description', $data['description'])
            ->first();

        if ($existingRecurring) {
            return response()->json([
                'status'  => 'bad request',
                'code'    => 400,
                'message' => 'Duplicate recurring transaction found for this user',
                'errors'  => [
                    'recurring' => 'A recurring transaction with these details already exists for this user.'
                ]
            ], 400);
        }

        $recurring = Recurring::create($data);

        return response()->json([
            'status'  => 'success',
            'code'    => 200,
            'message' => 'Create Recurring success',
            'data'    => new RecurringResource($recurring)
        ]);
    }

    public function updateRecurring(RecurringRequest $request, $id)
    {
        if (Gate::denies('private', Recurring::find($id))) {
            return response()->json([
                'status'  => 'error',
                'code'    => 403,
                'message' => 'You can only see and modify your own Recurring Transaction.'
            ], 403);
        }

        $data = $request->validated();

        $recurring = Recurring::find($id);
        if ($recurring) {
            $recurring->update($data);
            return response()->json([
                'status'  => 'success',
                'code'    => 200,
                'message' => 'Update Recurring Transaction success',
                'data'    => new RecurringResource($recurring)
            ]);
        }

        return response([
            'status'  => 'not found',
            'code'    => 404,
            'message' => 'Transaction not found'
        ], 404);
    }

    public function deleteRecurring($id)
    {
        if (Gate::denies('private', Recurring::find($id))) {
            return response()->json([
                'status'  => 'error',
                'code'    => 403,
                'message' => 'You can only see and modify your own Recurring Transaction.'
            ], 403);
        }

        $Recurring = Recurring::find($id);
        if ($Recurring) {
            $Recurring->delete();
            return response()->json([
                'status'  => 'success',
                'code'    => 200,
                'message' => 'Delete Recurring Transaction success'
            ]);
        }

        return response([
            'status'  => 'not found',
            'code'    => 404,
            'message' => 'Recurring not found'
        ], 404);
    }
}
