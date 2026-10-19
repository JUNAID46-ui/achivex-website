<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Media;
use App\Services\SupabaseStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $media = Media::query()
            ->when($request->input('q'), fn ($q, $term) => $q->where('filename', 'ilike', "%{$term}%"))
            ->when($request->input('type') === 'image', fn ($q) => $q->where('mime', 'like', 'image/%'))
            ->when($request->input('type') === 'document', fn ($q) => $q->where('mime', 'not like', 'image/%'))
            ->latest()->paginate(40)->withQueryString();

        return view('admin.media.index', [
            'media' => $media,
            'totalSize' => Media::sum('size'),
            'maxUpload' => $this->maxUploadMb(),
            'cloud' => SupabaseStorage::enabled(),
        ]);
    }

    // JSON list for the media picker
    public function list(Request $request)
    {
        return Media::query()
            ->when($request->input('q'), fn ($q, $term) => $q->where('filename', 'ilike', "%{$term}%"))
            ->latest()->limit(60)->get(['id', 'disk', 'path', 'public_url', 'filename', 'mime', 'size', 'alt', 'width', 'height']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'files' => 'required|array|max:20',
            'files.*' => 'file|max:'.($this->maxUploadMb() * 1024).'|mimes:jpg,jpeg,png,gif,webp,svg,avif,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,zip,mp4,webm',
        ], ['files.*.mimes' => 'This file type is not allowed.', 'files.*.max' => 'Each file must be under '.$this->maxUploadMb().' MB.']);

        // With a storage key, files go to Supabase so the live website can show them.
        // Without one they stay on this computer, which only works while testing locally.
        $cloud = SupabaseStorage::enabled();
        $saved = [];
        foreach ($request->file('files') as $file) {
            $name = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'file';
            $ext = strtolower($file->getClientOriginalExtension());
            $filename = $name.'-'.Str::lower(Str::random(6)).'.'.$ext;
            $dims = str_starts_with((string) $file->getMimeType(), 'image/') && $ext !== 'svg' ? @getimagesize($file->getRealPath()) : null;
            if ($cloud) {
                $path = now()->format('Y/m').'/'.$filename;
                try {
                    $url = SupabaseStorage::put($path, file_get_contents($file->getRealPath()), (string) $file->getMimeType());
                } catch (\Throwable $e) {
                    report($e);
                    $message = $e->getMessage();

                    return $request->wantsJson() ? response()->json(['message' => $message], 422) : back()->withErrors(['files' => $message]);
                }
            } else {
                $path = $file->storeAs('media/'.now()->format('Y/m'), $filename, 'public');
                $url = null;
            }
            $saved[] = Media::create([
                'disk' => $cloud ? 'supabase' : 'public', 'path' => $path, 'public_url' => $url, 'filename' => $file->getClientOriginalName(),
                'mime' => $file->getMimeType(), 'size' => $file->getSize(),
                'width' => $dims[0] ?? null, 'height' => $dims[1] ?? null, 'uploaded_by' => auth()->id(),
            ]);
        }
        ActivityLog::record('uploaded', 'Media', count($saved).' file(s)', collect($saved)->pluck('filename')->implode(', '));

        if ($request->wantsJson()) {
            return response()->json($saved);
        }

        return back()->with('status', count($saved).' file(s) uploaded.');
    }

    public function update(Request $request, Media $media)
    {
        $media->update($request->validate(['alt' => 'nullable|max:250']));

        return $request->wantsJson() ? response()->json($media) : back()->with('status', 'Details saved.');
    }

    public function destroy(Media $media)
    {
        if ($media->disk === 'supabase') {
            try {
                SupabaseStorage::delete($media->path);
            } catch (\Throwable $e) {
                report($e);
            }
        } else {
            Storage::disk($media->disk)->delete($media->path);
        }
        $media->delete();
        ActivityLog::record('deleted', 'Media', $media->filename);

        return request()->wantsJson() ? response()->json(['ok' => true]) : back()->with('status', 'File deleted.');
    }

    private function maxUploadMb(): int
    {
        $toMb = function ($v) {
            $v = trim((string) $v);
            $n = (int) $v;

            return match (strtolower(substr($v, -1))) { 'g' => $n * 1024, 'k' => max(1, intdiv($n, 1024)), default => str_ends_with(strtolower($v), 'm') ? $n : max(1, intdiv($n, 1048576)) };
        };

        return max(1, min($toMb(ini_get('upload_max_filesize')), $toMb(ini_get('post_max_size')), 20));
    }
}
