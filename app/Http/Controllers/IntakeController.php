<?php

namespace App\Http\Controllers;

use App\Models\Intake;
use App\Models\IntakeResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class IntakeController extends Controller
{
    public function index()
    {
        Log::info('Fetching all intakes with relationships');
        $intakes = Intake::with(['user', 'client', 'response'])->get();
        return response()->json($intakes);
    }

    public function store(Request $request)
    {
        try {
            Log::info('Creating new intake', ['request_data' => $request->all()]);

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
                'client_id' => 'nullable|exists:clients,id',
                'expiration_date' => 'required|date',
                'status' => 'required|in:pending,completed,cancelled',
                'link' => 'required|string|unique:intakes',
                'full_name' => 'nullable|string|max:255',
                'company_name' => 'nullable|string|max:255',
                'email' => 'nullable|email',
            ]);

            if ($validator->fails()) {
                Log::warning('Intake validation failed', ['errors' => $validator->errors()->toArray()]);
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Create client if full_name, company_name, and email are provided
            $clientId = null;
            if ($request->filled(['full_name', 'company_name', 'email'])) {
                // Split full name into first and last name
                $nameParts = explode(' ', $request->full_name, 2);
                $firstName = $nameParts[0];
                $lastName = $nameParts[1] ?? '';

                // Create new client
                $client = \App\Models\Client::create([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $request->email,
                    'company_name' => $request->company_name,
                ]);

                $clientId = $client->id;
                Log::info('Client created for intake', [
                    'client_id' => $clientId,
                    'client_data' => [
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'email' => $request->email,
                        'company_name' => $request->company_name,
                    ]
                ]);
            }

            // Create intake with client_id if available
            $intakeData = $request->all();
            if ($clientId) {
                $intakeData['client_id'] = $clientId;
            }

            $intake = Intake::create($intakeData);
            Log::info('Intake created successfully', ['intake_id' => $intake->id]);
            return response()->json($intake, 201);
        } catch (\Exception $e) {
            Log::error('Failed to create intake', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => 'Failed to create intake', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            Log::info('Fetching intake details', ['intake_id' => $id]);
            $intake = Intake::with(['user', 'client', 'response'])->findOrFail($id);

            // Check if intake has expired
            if ($intake->expiration_date < now()) {
                Log::warning('Intake has expired', ['intake_id' => $id]);
                return response()->json(['message' => 'This intake form has expired'], 410);
            }

            // Check if intake has already received a response
            if ($intake->response) {
                Log::warning('Intake already has a response', ['intake_id' => $id]);
                return response()->json(['message' => 'This intake form has already been submitted'], 422);
            }

            return response()->json($intake);
        } catch (ModelNotFoundException $e) {
            Log::warning('Intake not found', ['intake_id' => $id]);
            return response()->json(['message' => 'Intake not found'], 404);
        } catch (\Exception $e) {
            Log::error('Error fetching intake', [
                'intake_id' => $id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['message' => 'Error fetching intake'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            Log::info('Updating intake', ['intake_id' => $id, 'request_data' => $request->all()]);
            $intake = Intake::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'user_id' => 'sometimes|required|exists:users,id',
                'client_id' => 'nullable|exists:clients,id',
                'expiration_date' => 'sometimes|required|date',
                'status' => 'sometimes|required|in:pending,completed,cancelled',
                'link' => 'sometimes|required|string|unique:intakes,link,' . $id,
            ]);

            if ($validator->fails()) {
                Log::warning('Intake update validation failed', ['intake_id' => $id, 'errors' => $validator->errors()->toArray()]);
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $intake->update($request->all());
            Log::info('Intake updated successfully', ['intake_id' => $intake->id]);
            return response()->json($intake);
        } catch (ModelNotFoundException $e) {
            Log::warning('Intake not found for update', ['intake_id' => $id]);
            return response()->json(['message' => 'Intake not found'], 404);
        } catch (\Exception $e) {
            Log::error('Error updating intake', [
                'intake_id' => $id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['message' => 'Error updating intake'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            Log::info('Deleting intake', ['intake_id' => $id]);
            $intake = Intake::findOrFail($id);
            $intake->delete();
            Log::info('Intake deleted successfully', ['intake_id' => $id]);
            return response()->json(null, 204);
        } catch (ModelNotFoundException $e) {
            Log::warning('Intake not found for deletion', ['intake_id' => $id]);
            return response()->json(['message' => 'Intake not found'], 404);
        } catch (\Exception $e) {
            Log::error('Error deleting intake', [
                'intake_id' => $id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['message' => 'Error deleting intake'], 500);
        }
    }

    public function storeResponse(Request $request, $id)
    {
        try {
            Log::info('Storing intake response', ['intake_id' => $id, 'request_data' => $request->all()]);
            $intake = Intake::findOrFail($id);

            // Check if intake has already received a response
            if ($intake->response) {
                Log::warning('Intake already has a response', ['intake_id' => $id]);
                return response()->json(['message' => 'This intake form has already been submitted'], 422);
            }

            // Check if intake has expired
            if ($intake->expiration_date < now()) {
                Log::warning('Intake has expired', ['intake_id' => $id]);
                return response()->json(['message' => 'This intake form has expired'], 410);
            }

            $validator = Validator::make($request->all(), [
                'full_name' => 'required|string|max:255',
                'company_name' => 'required|string|max:255',
                'email' => 'required|email',
                'project_description' => 'required|string',
                'budget_range' => 'required|string',
                'deadline' => 'required|date',
                'project_type' => 'required|string',
                'project_examples' => 'nullable|string',
            ], [
                'full_name.required' => 'Please provide your full name',
                'company_name.required' => 'Please provide your company name',
                'email.required' => 'Please provide your email address',
                'email.email' => 'Please provide a valid email address',
                'project_description.required' => 'Please describe your project',
                'budget_range.required' => 'Please select a budget range',
                'deadline.required' => 'Please provide a project deadline',
                'deadline.date' => 'Please provide a valid date for the deadline',
                'project_type.required' => 'Please select a project type',
            ]);

            if ($validator->fails()) {
                Log::warning('Intake response validation failed', [
                    'intake_id' => $id,
                    'errors' => $validator->errors()->toArray(),
                    'request_data' => $request->all()
                ]);
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Create or find client
            $nameParts = explode(' ', $request->full_name, 2);
            $firstName = $nameParts[0];
            $lastName = $nameParts[1] ?? '';

            $client = \App\Models\Client::firstOrCreate(
                ['email' => $request->email],
                [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'company_name' => $request->company_name,
                ]
            );

            // Update intake with client_id if not already set
            if (!$intake->client_id) {
                $intake->update(['client_id' => $client->id]);
                Log::info('Intake updated with client', [
                    'intake_id' => $id,
                    'client_id' => $client->id
                ]);
            }

            // Transform the data to match the database column names
            $responseData = [
                'full_name' => $request->full_name,
                'company_name' => $request->company_name,
                'email' => $request->email,
                'project_description' => $request->project_description,
                'budget_range' => $request->budget_range,
                'deadline' => $request->deadline,
                'project_type' => $request->project_type,
                'project_examples' => $request->project_examples ? explode(',', $request->project_examples) : null,
            ];

            $response = $intake->response()->create($responseData);

            // Update intake status to completed
            $intake->update(['status' => 'completed']);

            Log::info('Intake response stored successfully', [
                'intake_id' => $id,
                'response_id' => $response->id,
                'client_id' => $client->id
            ]);

            return response()->json([
                'message' => 'Intake form submitted successfully',
                'response' => $response
            ], 201);

        } catch (ModelNotFoundException $e) {
            Log::warning('Intake not found for response storage', ['intake_id' => $id]);
            return response()->json(['message' => 'Intake form not found'], 404);
        } catch (\Exception $e) {
            Log::error('Error storing intake response', [
                'intake_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Error submitting intake form',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function storeForm(Request $request, $id)
    {
        try {
            Log::info('Storing intake form', ['intake_id' => $id, 'form_type' => $request->form_type]);
            $intake = Intake::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'form_type' => 'required|string',
                'form_data' => 'required|array',
            ]);

            if ($validator->fails()) {
                Log::warning('Intake form validation failed', ['intake_id' => $id, 'errors' => $validator->errors()->toArray()]);
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $form = $intake->forms()->create($request->all());
            Log::info('Intake form stored successfully', ['intake_id' => $id, 'form_id' => $form->id]);
            return response()->json($form, 201);
        } catch (ModelNotFoundException $e) {
            Log::warning('Intake not found for form storage', ['intake_id' => $id]);
            return response()->json(['message' => 'Intake not found'], 404);
        } catch (\Exception $e) {
            Log::error('Error storing intake form', [
                'intake_id' => $id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['message' => 'Error storing form'], 500);
        }
    }

    public function storeAttachment(Request $request, $id)
    {
        try {
            Log::info('Storing intake attachment', ['intake_id' => $id]);
            $intake = Intake::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'file' => 'required|file|max:10240', // 10MB max
            ]);

            if ($validator->fails()) {
                Log::warning('Intake attachment validation failed', ['intake_id' => $id, 'errors' => $validator->errors()->toArray()]);
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $file = $request->file('file');
            $path = $file->store('intake-attachments');

            $attachment = $intake->attachments()->create([
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $file->getClientMimeType(),
            ]);

            Log::info('Intake attachment stored successfully', [
                'intake_id' => $id,
                'attachment_id' => $attachment->id,
                'file_name' => $file->getClientOriginalName()
            ]);

            return response()->json($attachment, 201);
        } catch (ModelNotFoundException $e) {
            Log::warning('Intake not found for attachment storage', ['intake_id' => $id]);
            return response()->json(['message' => 'Intake not found'], 404);
        } catch (\Exception $e) {
            Log::error('Failed to store intake attachment', [
                'intake_id' => $id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['message' => 'Failed to store attachment'], 500);
        }
    }

    public function deleteAttachment($intakeId, $attachmentId)
    {
        try {
            Log::info('Attempting to delete intake attachment', [
                'intake_id' => $intakeId,
                'attachment_id' => $attachmentId
            ]);

            $intake = Intake::findOrFail($intakeId);
            $attachment = IntakeAttachment::findOrFail($attachmentId);

            if ($attachment->intake_id !== $intake->id) {
                Log::warning('Unauthorized attachment deletion attempt', [
                    'intake_id' => $intakeId,
                    'attachment_id' => $attachmentId
                ]);
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            Storage::delete($attachment->file_path);
            $attachment->delete();

            Log::info('Intake attachment deleted successfully', [
                'intake_id' => $intakeId,
                'attachment_id' => $attachmentId
            ]);

            return response()->json(null, 204);
        } catch (ModelNotFoundException $e) {
            Log::warning('Resource not found for attachment deletion', [
                'intake_id' => $intakeId,
                'attachment_id' => $attachmentId
            ]);
            return response()->json(['message' => 'Resource not found'], 404);
        } catch (\Exception $e) {
            Log::error('Failed to delete intake attachment', [
                'intake_id' => $intakeId,
                'attachment_id' => $attachmentId,
                'error' => $e->getMessage()
            ]);
            return response()->json(['message' => 'Failed to delete attachment'], 500);
        }
    }

    public function findByLink($link)
    {
        try {
            Log::info('Finding intake by link', ['link' => $link]);

            $intake = Intake::where('link', $link)
                ->with(['user', 'client', 'response'])
                ->first();

            if (!$intake) {
                Log::warning('Intake not found by link', ['link' => $link]);
                return response()->json(['message' => 'Intake not found'], 404);
            }

            // Check if the intake has expired
            if ($intake->expiration_date < now()) {
                Log::info('Intake has expired', ['intake_id' => $intake->id, 'expiration_date' => $intake->expiration_date]);
                return response()->json([
                    'message' => 'This intake form has expired',
                    'intake' => $intake
                ], 410); // 410 Gone status code for expired resources
            }

            Log::info('Intake found successfully', ['intake_id' => $intake->id]);
            return response()->json($intake);
        } catch (\Exception $e) {
            Log::error('Error finding intake by link', [
                'link' => $link,
                'error' => $e->getMessage()
            ]);
            return response()->json(['message' => 'Error finding intake'], 500);
        }
    }
}
