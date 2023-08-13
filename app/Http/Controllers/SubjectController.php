<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\SubjectResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SubjectController extends Controller
{
    public function findAllSubjects()
    {
        $subjects = Subject::all();

        return SubjectResource::collection($subjects)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function addSubject(Request $request)
    {
        try {
            
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'code' => 'required|string|max:5|unique:subjects',
                'description' => 'required|string|max:255',
                'units' => 'required|integer|min:0',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $subject = Subject::create($validator->validated());

            DB::commit();

            return (new SubjectResource($subject))
                ->response()
                ->setStatusCode(Response::HTTP_CREATED);


        } catch (\Throwable $e) {
            
            DB::rollBack();

            if(isset($e->validator))

                return response()->json([
                        'errors' => $validator->errors()
                    ], 
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );

            else
                return response()->json([
                        'message' => 'An internal server error occurred.'
                    ], 
                    Response::HTTP_INTERNAL_SERVER_ERROR
                );
        }           
    }
}
