<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\Api\V1\StoreUserRequest;
use App\Http\Requests\Api\V1\UpdateUserRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Services\EmailService;
use App\Services\UserService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @group User Management
 *
 * APIs for managing users with multiple email addresses
 */
class UserController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private EmailService $emailService,
        private UserService $userService
    ) {}

    /**
     * Get list of users
     *
     * Retrieves a paginated list of users with optional search functionality.
     *
     * @queryParam search string Search by first name, last name, or phone number. Example: John
     * @queryParam per_page integer Number of items per page (default: 15). Example: 10
     * @queryParam page integer Page number (default: 1). Example: 2
     *
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "first_name": "John",
     *       "last_name": "Doe",
     *       "full_name": "John Doe",
     *       "phone": "+1234567890",
     *       "primary_email": "john@example.com",
     *       "emails": [
     *         {
     *           "id": 1,
     *           "email": "john@example.com",
     *           "is_primary": true,
     *           "verified_at": null,
     *           "created_at": "2025-07-09T12:00:00.000000Z",
     *           "updated_at": "2025-07-09T12:00:00.000000Z"
     *         }
     *       ],
     *       "created_at": "2025-07-09T12:00:00.000000Z",
     *       "updated_at": "2025-07-09T12:00:00.000000Z"
     *     }
     *   ],
     *   "pagination": {
     *     "current_page": 1,
     *     "per_page": 15,
     *     "total": 1,
     *     "last_page": 1
     *   }
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search']);
        $perPage = $request->get('per_page', 15);

        $users = $this->userService->getUsersPaginated($filters, $perPage);

        return response()->json([
            'success' => true,
            'data' => UserResource::collection($users),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'last_page' => $users->lastPage(),
            ]
        ]);
    }

    /**
     * Create a new user
     *
     * Creates a new user with at least one email address.
     *
     * @response 201 {
     *   "success": true,
     *   "message": "User created successfully",
     *   "data": {
     *     "id": 1,
     *     "first_name": "John",
     *     "last_name": "Doe",
     *     "full_name": "John Doe",
     *     "phone": "+1234567890",
     *     "primary_email": "john@example.com",
     *     "emails": [
     *       {
     *         "id": 1,
     *         "email": "john@example.com",
     *         "is_primary": true,
     *         "verified_at": null,
     *         "created_at": "2025-07-09T12:00:00.000000Z",
     *         "updated_at": "2025-07-09T12:00:00.000000Z"
     *       },
     *       {
     *         "id": 2,
     *         "email": "john.work@example.com",
     *         "is_primary": false,
     *         "verified_at": null,
     *         "created_at": "2025-07-09T12:00:00.000000Z",
     *         "updated_at": "2025-07-09T12:00:00.000000Z"
     *       }
     *     ],
     *     "created_at": "2025-07-09T12:00:00.000000Z",
     *     "updated_at": "2025-07-09T12:00:00.000000Z"
     *   }
     * }
     *
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "first_name": [
     *       "The first name field is required."
     *     ],
     *     "emails.0.email": [
     *       "The email field is required."
     *     ]
     *   }
     * }
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->userService->createUser($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => new UserResource($user)
        ], 201);
    }

    /**
     * Get user by ID
     *
     * Retrieves a specific user by their ID with all associated email addresses.
     *
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "id": 1,
     *     "first_name": "John",
     *     "last_name": "Doe",
     *     "full_name": "John Doe",
     *     "phone": "+1234567890",
     *     "primary_email": "john@example.com",
     *     "emails": [
     *       {
     *         "id": 1,
     *         "email": "john@example.com",
     *         "is_primary": true,
     *         "verified_at": null,
     *         "created_at": "2025-07-09T12:00:00.000000Z",
     *         "updated_at": "2025-07-09T12:00:00.000000Z"
     *       }
     *     ],
     *     "created_at": "2025-07-09T12:00:00.000000Z",
     *     "updated_at": "2025-07-09T12:00:00.000000Z"
     *   }
     * }
     *
     * @response 404 {
     *   "message": "No query results for model [App\\Models\\User] 1"
     * }
     */
    public function show(User $user): JsonResponse
    {
        $user->load('emails');

        return response()->json([
            'success' => true,
            'data' => new UserResource($user)
        ]);
    }

    /**
     * Update user
     *
     * Updates an existing user's information. If emails are provided,
     * all existing emails will be replaced with the new ones.
     *
     * @response 200 {
     *   "success": true,
     *   "message": "User updated successfully",
     *   "data": {
     *     "id": 1,
     *     "first_name": "John",
     *     "last_name": "Doe",
     *     "full_name": "John Doe",
     *     "phone": "+1234567890",
     *     "primary_email": "john.new@example.com",
     *     "emails": [
     *       {
     *         "id": 2,
     *         "email": "john.new@example.com",
     *         "is_primary": true,
     *         "verified_at": null,
     *         "created_at": "2025-07-09T12:00:00.000000Z",
     *         "updated_at": "2025-07-09T12:00:00.000000Z"
     *       }
     *     ],
     *     "created_at": "2025-07-09T12:00:00.000000Z",
     *     "updated_at": "2025-07-09T12:00:00.000000Z"
     *   }
     * }
     *
     * @response 404 {
     *   "message": "No query results for model [App\\Models\\User] 1"
     * }
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $user = $this->userService->updateUser($user, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => new UserResource($user)
        ]);
    }

    /**
     * Delete user
     *
     * Deletes a user and all associated data including email addresses.
     *
     *
     * @response 200 {
     *   "success": true,
     *   "message": "User deleted successfully"
     * }
     *
     * @response 404 {
     *   "message": "No query results for model [App\\Models\\User] 1"
     * }
     */
    public function destroy(User $user): JsonResponse
    {
        $this->userService->deleteUser($user);

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }

    /**
     * Send welcome email
     *
     * Queues welcome email jobs to be sent to all of the user's email addresses.
     * The actual email sending is handled asynchronously via queues.
     *
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Welcome email job queued for 2 email addresses",
     *   "emails_count": 2,
     *   "emails": [
     *     "john@example.com",
     *     "john.work@example.com"
     *   ]
     * }
     *
     * @response 400 {
     *   "success": false,
     *   "message": "User has no email addresses"
     * }
     *
     * @response 404 {
     *   "message": "No query results for model [App\\Models\\User] 1"
     * }
     */
    public function sendWelcome(User $user): JsonResponse
    {
        $result = $this->emailService->sendWelcomeEmails($user);

        return response()->json($result, $result['success'] ? 200 : 400);
    }
}
