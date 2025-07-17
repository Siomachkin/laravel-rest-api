<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserEmail;
use App\Http\Requests\Api\V1\StoreUserEmailRequest;
use App\Http\Requests\Api\V1\UpdateUserEmailRequest;
use App\Http\Resources\Api\V1\UserEmailResource;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;

/**
 * @group User Email Management
 *
 * APIs for managing user email addresses
 */
class UserEmailController extends Controller
{
    public function __construct(private UserService $userService) {}
    /**
     * Get user's email addresses
     *
     * Retrieves all email addresses for a specific user, ordered by primary status.
     *
     *
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "email": "john@example.com",
     *       "is_primary": true,
     *       "verified_at": null,
     *       "created_at": "2025-07-09T12:00:00.000000Z",
     *       "updated_at": "2025-07-09T12:00:00.000000Z"
     *     },
     *     {
     *       "id": 2,
     *       "email": "john.work@example.com",
     *       "is_primary": false,
     *       "verified_at": null,
     *       "created_at": "2025-07-09T12:00:00.000000Z",
     *       "updated_at": "2025-07-09T12:00:00.000000Z"
     *     }
     *   ]
     * }
     *
     * @response 404 {
     *   "message": "No query results for model [App\\Models\\User] 1"
     * }
     */
    public function index(User $user): JsonResponse
    {
        $emails = $this->userService->getUserEmails($user);

        return response()->json([
            'success' => true,
            'data' => UserEmailResource::collection($emails)
        ]);
    }

    /**
     * Add new email address
     *
     * Adds a new email address to a user. If set as primary, it will replace
     * the current primary email.
     *
     * @bodyParam email string required The email address. Example: john.new@example.com
     * @bodyParam is_primary boolean Whether this email should be set as primary. Example: true
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Email address added successfully",
     *   "data": {
     *     "id": 3,
     *     "email": "john.new@example.com",
     *     "is_primary": true,
     *     "verified_at": null,
     *     "created_at": "2025-07-09T12:00:00.000000Z",
     *     "updated_at": "2025-07-09T12:00:00.000000Z"
     *   }
     * }
     *
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "email": [
     *       "The email field is required."
     *     ]
     *   }
     * }
     *
     * @response 404 {
     *   "message": "No query results for model [App\\Models\\User] 1"
     * }
     */
    public function store(StoreUserEmailRequest $request, User $user): JsonResponse
    {
        $email = $this->userService->addEmailToUser(
            $user,
            $request->email,
            $request->is_primary ?? false
        );

        return response()->json([
            'success' => true,
            'message' => 'Email address added successfully',
            'data' => new UserEmailResource($email)
        ], 201);
    }

    /**
     * Update email address
     *
     * Updates an existing email address. Can change the email address itself
     * or set it as primary.
     *
     * @bodyParam email string The new email address. Example: john.updated@example.com
     * @bodyParam is_primary boolean Whether this email should be set as primary. Example: true
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Email address updated successfully",
     *   "data": {
     *     "id": 1,
     *     "email": "john.updated@example.com",
     *     "is_primary": true,
     *     "verified_at": null,
     *     "created_at": "2025-07-09T12:00:00.000000Z",
     *     "updated_at": "2025-07-09T12:00:00.000000Z"
     *   }
     * }
     *
     * @response 404 {
     *   "success": false,
     *   "message": "Email address not found for this user"
     * }
     */
    public function update(UpdateUserEmailRequest $request, User $user, UserEmail $email): JsonResponse
    {
        if ($email->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Email address not found for this user'
            ], 404);
        }

        $email = $this->userService->updateUserEmail($user, $email, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Email address updated successfully',
            'data' => new UserEmailResource($email)
        ]);
    }

    /**
     * Delete email address
     *
     * Removes an email address from a user. Cannot delete the primary email
     * if it's the only email address.
     *
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Email address deleted successfully"
     * }
     *
     * @response 400 {
     *   "success": false,
     *   "message": "Cannot delete primary email address. Set another email as primary first."
     * }
     *
     * @response 404 {
     *   "success": false,
     *   "message": "Email address not found for this user"
     * }
     */
    public function destroy(User $user, UserEmail $email): JsonResponse
    {
        if ($email->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Email address not found for this user'
            ], 404);
        }

        try {
            $this->userService->deleteUserEmail($user, $email);
            
            return response()->json([
                'success' => true,
                'message' => 'Email address deleted successfully'
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Set primary email
     *
     * Sets the specified email address as the primary email for the user.
     * The previous primary email will be set as non-primary.
     *
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Primary email address updated successfully",
     *   "data": {
     *     "id": 1,
     *     "email": "john@example.com",
     *     "is_primary": true,
     *     "verified_at": null,
     *     "created_at": "2025-07-09T12:00:00.000000Z",
     *     "updated_at": "2025-07-09T12:00:00.000000Z"
     *   }
     * }
     *
     * @response 404 {
     *   "success": false,
     *   "message": "Email address not found for this user"
     * }
     */
    public function setPrimary(User $user, UserEmail $email): JsonResponse
    {
        if ($email->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Email address not found for this user'
            ], 404);
        }

        $email = $this->userService->setPrimaryEmail($user, $email);

        return response()->json([
            'success' => true,
            'message' => 'Primary email address updated successfully',
            'data' => new UserEmailResource($email)
        ]);
    }
}
