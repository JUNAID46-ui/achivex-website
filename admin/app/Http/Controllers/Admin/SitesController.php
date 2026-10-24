<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Site;
use App\Support\Sites;
use Illuminate\Http\Request;

// Settings, Sites: connect more websites built on the same stack, each with its own database
// and hosting, and switch the whole admin between them.
class SitesController extends Controller
{
    public function index()
    {
        return view('admin.sites.index', ['sites' => Site::orderBy('name')->get()]);
    }

    public function create()
    {
        return view('admin.sites.form', ['site' => new Site(['db_port' => 5432, 'db_database' => 'postgres', 'db_sslmode' => 'require'])]);
    }

    public function store(Request $request)
    {
        $site = Site::create($this->validated($request, true));
        $this->runCheck($site);
        ActivityLog::record('created', 'Site', $site->name, $site->site_url, ['module' => 'Sites', 'id' => $site->id]);

        return redirect()->route('admin.sites.index')->with('status', "{$site->name} added. ".$site->status_message);
    }

    public function edit(Site $site)
    {
        return view('admin.sites.form', ['site' => $site]);
    }

    public function update(Request $request, Site $site)
    {
        $data = $this->validated($request, false);
        if (($data['db_password'] ?? '') === '') {
            unset($data['db_password']);
        }
        $site->update($data);
        $this->runCheck($site);
        ActivityLog::record('updated', 'Site', $site->name, $site->site_url, ['module' => 'Sites', 'id' => $site->id]);

        return redirect()->route('admin.sites.index')->with('status', "{$site->name} saved. ".$site->status_message);
    }

    public function destroy(Request $request, Site $site)
    {
        if ((int) $request->session()->get('site_id') === $site->id) {
            $request->session()->forget('site_id');
        }
        $site->delete();
        ActivityLog::record('deleted', 'Site', $site->name, 'Removed from the admin. Its database and hosting were not touched.', ['module' => 'Sites']);

        return redirect()->route('admin.sites.index')->with('status', "{$site->name} removed from the admin. Its database and website were not changed.");
    }

    public function check(Site $site)
    {
        $this->runCheck($site);

        return back()->with($site->status === 'error' ? 'error' : 'status', $site->status_message);
    }

    public function prepare(Site $site)
    {
        $result = Sites::prepare($site);
        $this->runCheck($site);
        ActivityLog::record('updated', 'Site', $site->name, 'Prepared database', ['module' => 'Sites', 'id' => $site->id, 'status' => $result['ok'] ? 'success' : 'failed']);

        return back()->with($result['ok'] ? 'status' : 'error', $result['ok'] ? 'Database prepared. '.$site->status_message : 'The database could not be prepared: '.$result['output']);
    }

    public function switch(Request $request, Site $site)
    {
        $this->runCheck($site);
        if ($site->status !== 'ready') {
            return back()->with('error', "{$site->name} is not ready yet. ".$site->status_message);
        }
        $request->session()->put('site_id', $site->id);
        $request->session()->forget('site_user_'.$site->id);

        return redirect()->route('admin.dashboard')->with('status', "You are now managing {$site->name}.");
    }

    public function main(Request $request)
    {
        $request->session()->forget('site_id');

        return redirect()->route('admin.dashboard')->with('status', 'You are back on the main website.');
    }

    private function runCheck(Site $site): void
    {
        $r = Sites::check($site);
        $site->forceFill(['status' => $r['status'], 'status_message' => $r['message'], 'checked_at' => now()])->save();
    }

    private function validated(Request $request, bool $creating): array
    {
        return $request->validate([
            'name' => 'required|max:120',
            'site_url' => 'required|url|max:500',
            'public_url' => 'nullable|url|max:500',
            'db_host' => ['required', 'max:255', 'regex:/^[A-Za-z0-9.\-]+$/'],
            'db_port' => 'required|integer|between:1,65535',
            'db_database' => ['required', 'max:120', 'regex:/^[A-Za-z0-9_\-]+$/'],
            'db_username' => 'required|max:255',
            'db_password' => ($creating ? 'required' : 'nullable').'|max:500',
            'db_sslmode' => 'required|in:require,prefer,disable,verify-full',
            'notes' => 'nullable|max:2000',
        ], ['db_host.regex' => 'Use only the host name, for example aws-0-us-west-1.pooler.supabase.com.']);
    }
}
