<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShortenLinkRequest;
use App\Http\Requests\StoreLinkRequest;
use App\Http\Requests\UpdateLinkRequest;
use App\Models\Link;
use Exception;
use Illuminate\Http\Request;

class LinkController extends Controller
{
    /**
     * Display a listing of links (all public).
     */
    public function index(Request $request)
    {
        $links = Link::getFilteredLinks(
            $request->get('search'),
            $request->get('status')
        );

        $newopened = $request->get('newopened', null);

        return view('links', compact('links', 'newopened'));
    }

    /**
     * Shorten a link for guest users from public form.
     */
    public function shorten(ShortenLinkRequest $request)
    {
        try {
            $validated = $request->validated();
            $link = Link::createShortenedLink(
                $validated['target_url'],
                $validated['custom_alias'] ?? null
            );

            return redirect()->route('links')
                ->with('success', 'URL berhasil diperpendek!')
                ->with('short_url', url('/'.$link->new_link));
        } catch (Exception $e) {
            return redirect()->route('links')
                ->withErrors(['form' => 'Terjadi kesalahan: '.$e->getMessage().'. Silakan coba lagi.'])
                ->withInput();
        }
    }

    /**
     * Store a newly created link from dashboard modal.
     */
    public function shortenwaccount(StoreLinkRequest $request)
    {
        try {
            $validated = $request->validated();
            $link = Link::createShortenedLink(
                $validated['target_url'],
                $validated['custom_alias'] ?? null,
                $validated['is_active'] ?? false,
                $validated['name']
            );

            $shortUrl = url('/'.$link->new_link);

            if (back()->getTargetUrl() === route('links', ['newopened' => '1'])) {
                return redirect()->route('links')
                    ->with('success', 'URL berhasil diperpendek!')
                    ->with('short_url', $shortUrl);
            }

            return back()
                ->with('success', 'URL berhasil diperpendek!')
                ->with('short_url', $shortUrl);
        } catch (Exception $e) {
            return back()
                ->withErrors(['form' => 'Terjadi kesalahan: '.$e->getMessage().'. Silakan coba lagi.'], 'new')
                ->withInput();
        }
    }

    /**
     * Backward-compatible static helper for storing shortened link.
     */
    public static function storeshorten($true_link, $new_link = null, $is_active = true, $name = null)
    {
        $link = Link::createShortenedLink($true_link, $new_link, $is_active, $name);

        return [
            'status' => 'success',
            'short_url' => url('/'.$link->new_link),
        ];
    }

    /**
     * Update the specified link in storage.
     */
    public function update(UpdateLinkRequest $request, $id)
    {
        try {
            $link = Link::where('id_link', $id)->firstOrFail();
            $link->updateLink($request->validated());

            return back()->with('success', 'Link berhasil diperbarui!');
        } catch (Exception $e) {
            return back()
                ->with('id', $id)
                ->withErrors(['form' => 'Terjadi kesalahan: '.$e->getMessage().'. Silakan coba lagi.'], 'edit');
        }
    }

    /**
     * Remove the specified link from storage.
     */
    public function destroy($id)
    {
        try {
            $link = Link::where('id_link', $id)->firstOrFail();
            $link->delete();

            return back()->with('success', 'Link berhasil dihapus!');
        } catch (Exception $e) {
            return back()->withErrors(['delete' => 'Terjadi kesalahan: '.$e->getMessage().'. Silakan coba lagi.']);
        }
    }

    /**
     * Get all links.
     */
    public static function getAllLinks()
    {
        return Link::all();
    }

    /**
     * Get active links count.
     */
    public static function getActiveLinksCount()
    {
        return Link::getActiveLinksCount();
    }

    /**
     * Redirect shortened link to the target URL.
     */
    public function redirect(Request $request, $new_link)
    {
        try {
            $link = Link::where('new_link', $new_link)
                ->where('is_active', true)
                ->firstOrFail();

            return redirect()->away($link->true_link);
        } catch (Exception $e) {
            return redirect()->route('links')->withErrors(['form' => 'Link tidak ditemukan atau tidak aktif.']);
        }
    }
}
