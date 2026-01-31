# Flashcard API Documentation

## Authentication

All routes are protected by Sanctum authentication. You must provide a valid API token in the `Authorization` header with the `Bearer` scheme.
**Important:** Users only have access to their own flashcards.

### Obtaining API Token

*   **Route:** `POST /tokens/create`
*   **Purpose:** Obtain a new API token.
*   **Arguments:**
    *   `name` (required, string): The name of the user.
    *   `password` (required, string): The password of the user.
*   **Response:**
    *   Success: Returns a JSON object containing the API token.
    *   Error: Returns an error message with an appropriate HTTP status code if authentication fails.

## Flashcards

### 1. List Flashcards

*   **Route:** `GET /api/flashcards`
*   **Purpose:** Retrieve a list of all flashcards for the authenticated user.
*   **Arguments:** None
*   **Response:**
    *   Success: Returns a JSON array of flashcard objects with `id`, `question`, `answer`, `status`, `created_at`, and `updated_at` fields.
    *   Error: Returns an error message with an appropriate HTTP status code.

### 2. Create Flashcard

*   **Route:** `POST /api/flashcards`
*   **Purpose:** Create a new flashcard for the authenticated user.
*   **Arguments:**
    *   `question` (required, string): The question for the flashcard.
    *   `answer` (required, string): The answer for the flashcard.
*   **Response:**
    *   Success: Returns a JSON object of the created flashcard with `id`, `question`, `answer`, `status`, `created_at`, and `updated_at` fields.
    *   Error: Returns an error message with an appropriate HTTP status code if validation fails.

### 3. Get Flashcard

*   **Route:** `GET /api/flashcards/{id}`
*   **Purpose:** Retrieve a specific flashcard by its ID.
*   **Arguments:**
    *   `id` (required, integer): The ID of the flashcard to retrieve.
*   **Response:**
    *   Success: Returns a JSON object of the flashcard with `id`, `question`, `answer`, `status`, `created_at`, and `updated_at` fields.
    *   Error: Returns an error message with a 404 HTTP status code if the flashcard is not found or does not belong to the user.

### 4. Update Flashcard

*   **Route:** `PATCH /api/flashcards/{id}`
*   **Purpose:** Update a specific flashcard by its ID.
*   **Arguments:**
    *   `id` (required, integer): The ID of the flashcard to update.
    *   `question` (required, string): The new question for the flashcard.
    *   `answer` (required, string): The new answer for the flashcard.
*   **Response:**
    *   Success: Returns a JSON object of the updated flashcard with `id`, `question`, `answer`, `status`, `created_at`, and `updated_at` fields.
    *   Error: Returns an error message with an appropriate HTTP status code if validation fails or the flashcard is not found or does not belong to the user.

### 5. Delete Flashcard

*   **Route:** `DELETE /api/flashcards/{id}`
*   **Purpose:** Delete a specific flashcard by its ID.
*   **Arguments:**
    *   `id` (required, integer): The ID of the flashcard to delete.
*   **Response:**
    *   Success: Returns a JSON object with a message indicating successful deletion.
    *   Error: Returns an error message with a 404 HTTP status code if the flashcard is not found or does not belong to the user.

### 6. Restore Flashcard

*   **Route:** `POST /api/flashcards/{id}/restore`
*   **Purpose:** Restore a soft-deleted flashcard by its ID.
*   **Arguments:**
    *   `id` (required, integer): The ID of the flashcard to restore.
*   **Response:**
    *   Success: Returns a JSON object with a message indicating successful restoration.
    *   Error:
        *   Returns an error message with a 404 HTTP status code if the flashcard is not found or does not belong to the user.
        *   Returns an error message with a 422 HTTP status code if the flashcard is not deleted.

### 7. Get Statistics

*   **Route:** `GET /api/flashcards/statistics`
*   **Purpose:** Retrieve statistics about the user's flashcards.
*   **Arguments:** None
*   **Response:**
    *   Success: Returns a JSON object with `total_questions`, `percentage_answered`, and `percentage_correct` fields.
    *   Error: Returns an error message with an appropriate HTTP status code.

### 8. Reset Progress

*   **Route:** `POST /api/flashcards/reset`
*   **Purpose:** Reset the progress of all flashcards for the authenticated user.
*   **Arguments:** None
*   **Response:**
    *   Success: Returns a JSON object with a message indicating successful reset.
    *   Error: Returns an error message with an appropriate HTTP status code.

### 9. Get History

*   **Route:** `GET /api/flashcards/{id}/history`
*   **Purpose:** Retrieve the history of changes for a specific flashcard.
*   **Arguments:**
    *   `id` (required, integer): The ID of the flashcard to retrieve history for.
*   **Response:**
    *   Success: Returns a JSON array of audit objects with `audit_id`, `question`, `answer`, `status`, and `deleted` fields.
    *   Error: Returns an error message with a 404 HTTP status code if the flashcard is not found or does not belong to the user.

### 10. Revert Flashcard

*   **Route:** `POST /api/flashcards/{id}/history`
*   **Purpose:** Revert a flashcard to a specific historical state.
*   **Arguments:**
    *   `id` (required, integer): The ID of the flashcard to revert.
    *   `audit_id` (required, integer): The ID of the audit record to revert to.
*   **Response:**
    *   Success: Returns a JSON object with a message indicating successful reversion.
    *   Error:
        *   Returns an error message with a 404 HTTP status code if the flashcard or audit record is not found.
        *   Returns an error message with an appropriate HTTP status code if validation fails.

OpenAPI Docs: /docs/api
