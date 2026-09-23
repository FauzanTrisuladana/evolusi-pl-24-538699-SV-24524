<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ShortenLinkRequest;
use App\Http\Requests\Api\StoreLinkRequest;
use App\Http\Requests\Api\UpdateLinkRequest;
use App\Http\Resources\ApiResourceCollection;
use App\Http\Resources\LinkResource;
use App\Models\Link;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LinkController extends Controller
{
    /**
     * Display a listing of links (all public or filtered).
     */
    public function index(Request $request): ApiResourceCollection
    {
        $links = Link::getFilteredLinks(
            $request->get('search'),
            $request->get('status')
        );

        return LinkResource::collection($links)
            ->message('Daftar link berhasil diambil.');
    }

    /**
     * Store a newly created link.
     */
    public function store(StoreLinkRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $link = Link::createShortenedLink(
            $validated['target_url'],
            $validated['custom_alias'] ?? null,
            $validated['is_active'] ?? true,
            $validated['name']
        );

        return (new LinkResource($link))
            ->message('URL berhasil diperpendek!')
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified link.
     */
    public function show(string|int $id): LinkResource
    {
        $link = Link::where('id_link', $id)->firstOrFail();

        return (new LinkResource($link))
            ->message('Detail link berhasil diambil.');
    }

    /**
     * Update the specified link in storage.
     */
    public function update(UpdateLinkRequest $request, string|int $id): LinkResource
    {
        $link = Link::where('id_link', $id)->firstOrFail();
        $link->updateLink($request->validated());

        return (new LinkResource($link->fresh()))
            ->message('Link berhasil diperbarui!');
    }

    /**
     * Remove the specified link from storage.
     */
    public function destroy(string|int $id): JsonResponse
    {
        $link = Link::where('id_link', $id)->firstOrFail();
        $link->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Link berhasil dihapus!',
        ]);
    }

    /**
     * Shorten a link for guest users / quick shorten.
     */
    public function shorten(ShortenLinkRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $link = Link::createShortenedLink(
            $validated['target_url'],
            $validated['custom_alias'] ?? null
        );

        return (new LinkResource($link))
            ->message('URL berhasil diperpendek!')
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Get statistics of links.
     */
    public function stats(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Statistik link berhasil diambil.',
            'data' => [
                'active_links_count' => Link::getActiveLinksCount(),
                'total_links_count' => Link::count(),
            ],
        ]);
    }
}
