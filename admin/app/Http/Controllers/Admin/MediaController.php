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
    // SVG is left out because an SVG file can carry script.
    private const TYPES = 'jpg,jpeg,png,gif,webp,avif,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,zip,mp4,webm';

    // File types the media bucket accepts, by extension.
    private const MIMES = [
        'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif', 'webp' => 'image/webp',
        'avif' => 'image/avif', 'pdf' => 'application/pdf', 'mp4' => 'video/mp4', 'webm' => 'video/webm',
        'doc' => 'application/msword', 'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel', 'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt' => 'application/vnd.ms-powerpoint', 'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'txt' => 'text/plain', 'csv' => 'text/csv', 'zip' => 'application/zip',
    ];

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
            'maxUpload' => SupabaseStorage::enabled() ? $this->directMaxMb() : $this->maxUploadMb(),
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
            // mimes checks the file contents, extensions checks the name it is saved under.
            'files.*' => 'file|max:'.($this->maxUploadMb() * 1024).'|mimes:'.self::TYPES.'|extensions:'.self::TYPES,
        ], ['files.*.mimes' => 'This file type is not allowed.', 'files.*.extensions' => 'This file type is not allowed.', 'files.*.max' => 'Each file must be under '.$this->maxUploadMb().' MB.']);

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

    // Step 1 of a direct upload: check the file and hand the browser a one time upload link.
    public function sign(Request $request)
    {
        abort_unless(SupabaseStorage::enabled(), 422, 'Connect image storage in Settings first.');
        $data = $request->validate([
            'filename' => 'required|string|max:255',
            'size' => 'required|integer|min:1|max:'.($this->directMaxMb() * 1048576),
        ], ['size.max' => 'Each file must be under '.$this->directMaxMb().' MB.']);
        $ext = strtolower(pathinfo($data['filename'], PATHINFO_EXTENSION));
        abort_unless(isset(self::MIMES[$ext]), 422, 'This file type is not allowed.');

        $name = Str::slug(pathinfo($data['filename'], PATHINFO_FILENAME)) ?: 'file';
        $path = now()->format('Y/m').'/'.Str::limit($name, 80, '').'-'.Str::lower(Str::random(6)).'.'.$ext;
        try {
            $url = SupabaseStorage::signUpload($path);
        } catch (\Throwable $e) {
            report($e);
            abort(502, $e->getMessage());
        }
        // Only paths handed out here can be registered in step 2.
        $request->session()->put('media_uploads.'.md5($path), $path);

        return response()->json(['url' => $url, 'path' => $path, 'mime' => self::MIMES[$ext]]);
    }

    // Step 2: once the browser has sent the file, record it in the library.
    public function complete(Request $request)
    {
        $data = $request->validate([
            'path' => 'required|string|max:300',
            'filename' => 'required|string|max:255',
            'width' => 'nullable|integer|min:1|max:100000',
            'height' => 'nullable|integer|min:1|max:100000',
        ]);
        abort_unless($request->session()->pull('media_uploads.'.md5($data['path'])) === $data['path'], 422, 'This upload was not started here.');
        $file = SupabaseStorage::head($data['path']);
        abort_unless($file, 422, 'The file did not reach storage. Please try again.');
        $ext = strtolower(pathinfo($data['path'], PATHINFO_EXTENSION));
        if ($file['mime'] !== self::MIMES[$ext]) {
            SupabaseStorage::delete($data['path']);
            abort(422, 'The file type does not match its name.');
        }

        $media = Media::create([
            'disk' => 'supabase', 'path' => $data['path'], 'public_url' => SupabaseStorage::publicUrl($data['path']),
            'filename' => $data['filename'], 'mime' => $file['mime'], 'size' => $file['size'],
            'width' => $data['width'] ?? null, 'height' => $data['height'] ?? null, 'uploaded_by' => auth()->id(),
        ]);
        ActivityLog::record('uploaded', 'Media', '1 file(s)', $media->filename);

        return response()->json($media);
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

    // Largest file for direct uploads. Supabase can lower it: its Free plan stops at 50 MB per file.
    private function directMaxMb(): int
    {
        return max(1, (int) config('admin.max_upload_mb', 2048));
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
