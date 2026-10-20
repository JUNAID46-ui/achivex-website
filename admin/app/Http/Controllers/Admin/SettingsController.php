<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AppSetting;
use App\Services\SupabaseStorage;
use App\Models\Content\SiteSetting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        return view('admin.settings.index', [
            'general' => SiteSetting::group('general'),
            'social' => SiteSetting::group('social'),
            'mail' => AppSetting::group('mail'),
            'storageReady' => SupabaseStorage::enabled(),
            'storageKeyHint' => ($k = SupabaseStorage::key()) ? substr($k, 0, 12).'...'.substr($k, -4) : null,
        ]);
    }

    // Image storage key, checked against Supabase before it is saved.
    public function saveStorage(Request $request)
    {
        $data = $request->validate(['secretKey' => 'required|string|max:2000']);
        $key = trim($data['secretKey']);
        if ($error = SupabaseStorage::check($key)) {
            return back()->withErrors(['secretKey' => $error])->withFragment('storage');
        }
        AppSetting::put('storage', ['secretKey' => $key]);
        ActivityLog::record('updated', 'Settings', 'Image storage');

        return redirect()->to(route('admin.settings').'#storage')->with('status', 'Image storage connected. New uploads now go to Supabase and show on the live website.');
    }

    // Email server. Empty password keeps the saved one.
    public function saveMail(Request $request)
    {
        $data = $request->validate([
            // A local address cannot work on Vercel, which has no mail server of its own.
            'host' => 'required|max:200|not_in:127.0.0.1,localhost,0.0.0.0',
            'port' => 'required|integer|min:1|max:65535',
            'username' => 'required|max:200',
            'password' => 'nullable|max:200',
            'from_address' => 'nullable|email|max:200|not_regex:/[\r\n]/',
            'from_name' => 'required|max:120',
            'notify_to' => ['nullable', 'max:500', function ($attribute, $value, $fail) {
                foreach (array_filter(array_map('trim', explode(',', (string) $value))) as $email) {
                    if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $fail("{$email} is not a valid email address.");
                    }
                }
            }],
        ], ['host.not_in' => 'Use your email provider\'s SMTP server. For Gmail that is smtp.gmail.com.']);
        $data['password'] = str_replace(' ', '', (string) ($data['password'] ?? '')) ?: (AppSetting::group('mail')['password'] ?? '');
        $data['from_address'] = ($data['from_address'] ?? null) ?: $data['username'];
        AppSetting::put('mail', $data);
        ActivityLog::record('updated', 'Settings', 'Email server');

        return redirect()->to(route('admin.settings').'#email')->with('status', 'Email settings saved. Use "Send test email" to check them.');
    }

    public function save(Request $request)
    {
        $data = $request->validate([
            'general.siteName' => 'required|max:120',
            'general.tagline' => 'nullable|max:200',
            'general.phone' => 'nullable|max:50',
            'general.email' => 'nullable|email|max:200|not_regex:/[\r\n]/',
            'general.location' => 'nullable|max:120',
            'general.googleProfile' => 'nullable|url|max:300',
            'social.facebook' => 'nullable|url|max:300',
            'social.x' => 'nullable|url|max:300',
            'social.linkedin' => 'nullable|url|max:300',
            'social.instagram' => 'nullable|url|max:300',
        ], [], [
            'general.siteName' => 'site name', 'general.email' => 'email', 'general.googleProfile' => 'Google Business Profile link',
        ]);
        SiteSetting::put('general', $data['general']);
        SiteSetting::put('social', $data['social'] ?? []);
        ActivityLog::record('updated', 'Settings', 'General settings');

        return back()->with('status', 'Settings saved. The website shows the new details on its next load.');
    }
}
