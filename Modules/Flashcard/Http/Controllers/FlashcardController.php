<?php

namespace Modules\Flashcard\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\Flashcard\Http\Requests\CreateFlashcardRequest;
use Modules\Flashcard\Http\Requests\FindFlashcardRequest;
use Modules\Flashcard\Http\Requests\RevertFlashcardRequest;
use Modules\Flashcard\Http\Requests\UpdateFlashcardRequest;
use Modules\Flashcard\Http\Resources\FlashcardResource;
use Modules\Flashcard\Models\Flashcard;
use Modules\Flashcard\Services\FlashcardService;
use Nwidart\Modules\Routing\Controller;

class FlashcardController extends Controller
{
    private FlashcardService $flashcardService;

    public function __construct(FlashcardService $flashcardService)
    {
        $this->flashcardService = $flashcardService;
        $this->flashcardService->setUser(auth('api')->user());
    }

    public function index(): AnonymousResourceCollection
    {
        $flashcards = $this->flashcardService->listFlashcards();

        return FlashcardResource::collection($flashcards);
    }

    public function store(CreateFlashcardRequest $request): FlashcardResource
    {
        $data = $request->validated();

        $flashcard = $this->flashcardService->createFlashcard(
            $data['question'], $data['answer']
        );

        return new FlashcardResource($flashcard);
    }

    public function show(FindFlashcardRequest $findRequest): FlashcardResource
    {
        $flashcard = $this->getFlashcardOrAbort($findRequest->validated()['id']);

        return new FlashcardResource($flashcard);
    }

    private function getFlashcardOrAbort(int $id, bool $withDeleted = false): Flashcard
    {
        $flashcard = $this->flashcardService->findFlashcard($id, $withDeleted);

        if (! $flashcard) {
            abort(404, 'Flashcard not found');
        }

        return $flashcard;
    }

    public function destroy(FindFlashcardRequest $findRequest): JsonResponse
    {
        $flashcard = $this->getFlashcardOrAbort($findRequest->validated()['id'], withDeleted: true);

        if ($flashcard->trashed()) {
            $flashcard->forceDelete();

            return response()->json(['message' => 'Flashcard deleted permanently']);
        }

        $this->flashcardService->deleteFlashcard($flashcard);

        return response()->json(['message' => 'Flashcard deleted successfully']);
    }

    public function restore(FindFlashcardRequest $findRequest): JsonResponse
    {
        $flashcard = $this->getFlashcardOrAbort($findRequest->validated()['id'], withDeleted: true);

        if (! $flashcard->trashed()) {
            return response()->json(['message' => 'Flashcard is not deleted'], 422);
        }

        $this->flashcardService->restoreFlashcard($flashcard);

        return response()->json(['message' => 'Flashcard restored successfully']);
    }

    public function getStatistics(): JsonResponse
    {
        $statistics = $this->flashcardService->getStatistics();

        return response()->json($statistics);
    }

    public function resetProgress(): JsonResponse
    {
        $this->flashcardService->resetProgress();

        return response()->json(['message' => 'Flashcard progress reset successfully']);
    }

    public function getHistory(FindFlashcardRequest $findRequest): JsonResponse
    {
        $flashcard = $this->getFlashcardOrAbort($findRequest->validated()['id']);
        $history = $this->flashcardService->getFlashcardHistory($flashcard);

        return response()->json($history);
    }

    public function revert(RevertFlashcardRequest $request, FindFlashcardRequest $findRequest): JsonResponse
    {
        $flashcard = $this->getFlashcardOrAbort($findRequest->validated()['id']);
        $auditId = $request->validated()['audit_id'];

        $this->flashcardService->revertFlashcardToAudit($flashcard, $auditId);

        return response()->json(['message' => "Flashcard #$flashcard->id reverted to state #$auditId successfully."]);
    }

    public function update(UpdateFlashcardRequest $request, FindFlashcardRequest $findRequest): FlashcardResource
    {
        $flashcard = $this->getFlashcardOrAbort($findRequest->validated()['id']);

        $data = $request->validated();

        $this->flashcardService->editFlashcard(
            $flashcard,
            $data['question'], $data['answer']
        );

        return new FlashcardResource($flashcard);
    }
}
