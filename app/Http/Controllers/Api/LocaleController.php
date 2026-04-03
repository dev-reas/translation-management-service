<?php

namespace App\Http\Controllers\Api;

use App\Actions\Locale\CreateLocaleAction;
use App\Actions\Locale\UpdateLocaleAction;
use App\Actions\Locale\DeleteLocaleAction;
use App\Actions\Locale\ListLocalesAction;
use App\Http\Requests\Locale\CreateLocaleRequest;
use App\Http\Requests\Locale\UpdateLocaleRequest;
use App\Http\Resources\LocaleResource;
use App\Services\ResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocaleController
{
    public function index(Request $request, ListLocalesAction $action): JsonResponse
    {
        $perPage = min((int) $request->input('per_page', 20), 100);
        $page = (int) $request->input('page', 1);
        
        $locales = $action->execute($perPage, $page);
        
        return ResponseService::paginated($locales, LocaleResource::class);
    }

    public function store(CreateLocaleRequest $request, CreateLocaleAction $action): JsonResponse
    {
        $data = $request->validated();
        $locale = $action->execute($data);
        
        return ResponseService::created(new LocaleResource($locale));
    }

    public function show(int $id): JsonResponse
    {
        $locale = \App\Models\Locale::find($id);
        
        if (!$locale) {
            return ResponseService::notFound('Locale not found');
        }
        
        return ResponseService::success(new LocaleResource($locale));
    }

    public function update(UpdateLocaleRequest $request, int $id, UpdateLocaleAction $action): JsonResponse
    {
        $data = $request->validated();
        $locale = $action->execute($id, $data);
        
        return ResponseService::updated(new LocaleResource($locale));
    }

    public function destroy(int $id, DeleteLocaleAction $action): JsonResponse
    {
        $deleted = $action->execute($id);
        
        if (!$deleted) {
            return ResponseService::notFound('Locale not found');
        }
        
        return ResponseService::deleted('Locale deleted successfully');
    }
}