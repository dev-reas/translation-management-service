<?php

namespace App\Http\Controllers\Api;

use App\Actions\Tag\CreateTagAction;
use App\Actions\Tag\UpdateTagAction;
use App\Actions\Tag\DeleteTagAction;
use App\Actions\Tag\ListTagsAction;
use App\Http\Requests\Tag\CreateTagRequest;
use App\Http\Requests\Tag\UpdateTagRequest;
use App\Http\Resources\TagResource;
use App\Services\ResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Tag;

class TagController
{
    public function index(Request $request, ListTagsAction $action): JsonResponse
    {
        $perPage = min((int) $request->input('per_page', 20), 100);
        $page = (int) $request->input('page', 1);
        
        $tags = $action->execute($perPage, $page);
        
        return ResponseService::paginated($tags, TagResource::class);
    }

    public function store(CreateTagRequest $request, CreateTagAction $action): JsonResponse
    {
        $data = $request->validated();
        $tag = $action->execute($data);
        
        return ResponseService::created(new TagResource($tag));
    }

    public function show(int $id): JsonResponse
    {
        $tag = Tag::find($id);
        
        if (!$tag) {
            return ResponseService::notFound('Tag not found');
        }
        
        return ResponseService::success(new TagResource($tag));
    }

    public function update(UpdateTagRequest $request, int $id, UpdateTagAction $action): JsonResponse
    {
        $data = $request->validated();
        $tag = $action->execute($id, $data);
        
        return ResponseService::updated(new TagResource($tag));
    }

    public function destroy(int $id, DeleteTagAction $action): JsonResponse
    {
        $deleted = $action->execute($id);
        
        if (!$deleted) {
            return ResponseService::notFound('Tag not found');
        }
        
        return ResponseService::deleted('Tag deleted successfully');
    }
}