<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\MpesaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    /**
     * Display the settings page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $exchangeRate = config('mpesa.exchange_rate', 120);
        $adminUsers = User::where('usertype', User::ROLE_ADMIN)->get();
        
        // System statistics
        $stats = [
            'total_users' => User::count(),
            'total_admins' => User::where('usertype', User::ROLE_ADMIN)->count(),
            'total_writers' => User::where('usertype', User::ROLE_WRITER)->count(),
            'total_orders' => \App\Models\Order::count(),
        ];

        // Get email templates
        $emailTemplates = $this->getEmailTemplates();
        
        // Get recent backups
        $backups = $this->getRecentBackups();

        // Check writer portal status
        $writerPortalMaintenance = file_exists(storage_path('framework/down_writers'));
        
        // Check writer portal debug status
        $writerPortalDebug = false;
        $writerEnvFile = '/path/to/your/writers/portal/.env';
        if (file_exists($writerEnvFile)) {
            $writerEnv = file_get_contents($writerEnvFile);
            $writerPortalDebug = (bool) preg_match('/APP_DEBUG=true/i', $writerEnv);
        }
        
        return view('admin.settings', compact(
            'exchangeRate', 
            'adminUsers', 
            'stats', 
            'emailTemplates', 
            'backups',
            'writerPortalMaintenance',
            'writerPortalDebug'
        ));
        
        //return view('admin.settings', compact('exchangeRate', 'adminUsers', 'stats', 'emailTemplates', 'backups'));
    }
    
    /**
     * Update general settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $request->validate([
            'site_name' => 'nullable|string|max:255',
            'site_url' => 'nullable|url|max:255',
            'site_email' => 'nullable|email|max:255',
            'support_email' => 'nullable|email|max:255',
            'support_phone' => 'nullable|string|max:20',
            'notification_email' => 'nullable|email|max:255',
            'company_address' => 'nullable|string|max:500',
        ]);
        
        // Update .env file for APP_NAME
        if ($request->has('site_name') && !empty($request->site_name)) {
            $this->updateEnvFile('APP_NAME', $request->site_name);
        }
        
        // Update .env file for APP_URL
        if ($request->has('site_url') && !empty($request->site_url)) {
            $this->updateEnvFile('APP_URL', $request->site_url);
        }
        
        // Update settings in cache
        $this->updateSettings($request->except(['_token', '_method']));
        
        return redirect()->route('admin.settings')->with('success', 'Settings updated successfully');
    }
    
    /**
     * Show the exchange rate settings.
     *
     * @return \Illuminate\View\View
     */
    public function exchangeRate()
    {
        $exchangeRate = config('mpesa.exchange_rate', 120);
        
        return view('admin.settings.exchange-rate', compact('exchangeRate'));
    }
    
    /**
     * Update the exchange rate.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateExchangeRate(Request $request)
    {
        $request->validate([
            'exchange_rate' => 'required|numeric|min:1',
        ]);
        
        // Update exchange rate in .env file
        $envFile = app()->environmentFilePath();
        $str = file_get_contents($envFile);
        $str = $this->updateEnvValue($str, 'MPESA_EXCHANGE_RATE', $request->exchange_rate);
        file_put_contents($envFile, $str);
        
        // Clear config cache
        Artisan::call('config:clear');
        
        return redirect()->route('admin.settings', ['#payment'])->with('success', 'Exchange rate updated successfully');
    }
    
    /**
     * Update M-Pesa settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateMpesa(Request $request)
    {
        $request->validate([
            'mpesa_environment' => 'required|string|in:sandbox,production',
            'mpesa_shortcode' => 'required|string',
            'mpesa_consumer_key' => 'required|string',
            'mpesa_consumer_secret' => 'required|string',
            'mpesa_initiator' => 'required|string',
            'mpesa_security_credential' => 'required|string',
            'mpesa_callback_url' => 'required|url',
            'mpesa_timeout_url' => 'required|url',
            'mpesa_result_url' => 'required|url',
        ]);
        
        // Update M-Pesa settings in .env file
        $envFile = app()->environmentFilePath();
        $str = file_get_contents($envFile);
        
        // Update or create environment variables
        $str = $this->updateEnvValue($str, 'MPESA_ENVIRONMENT', $request->mpesa_environment);
        $str = $this->updateEnvValue($str, 'MPESA_SHORTCODE', $request->mpesa_shortcode);
        $str = $this->updateEnvValue($str, 'MPESA_CONSUMER_KEY', $request->mpesa_consumer_key);
        $str = $this->updateEnvValue($str, 'MPESA_CONSUMER_SECRET', $request->mpesa_consumer_secret);
        $str = $this->updateEnvValue($str, 'MPESA_INITIATOR', $request->mpesa_initiator);
        $str = $this->updateEnvValue($str, 'MPESA_SECURITY_CREDENTIAL', $request->mpesa_security_credential);
        $str = $this->updateEnvValue($str, 'MPESA_CALLBACK_URL', $request->mpesa_callback_url);
        $str = $this->updateEnvValue($str, 'MPESA_TIMEOUT_URL', $request->mpesa_timeout_url);
        $str = $this->updateEnvValue($str, 'MPESA_RESULT_URL', $request->mpesa_result_url);
        
        // Save changes to .env file
        file_put_contents($envFile, $str);
        
        // Clear config cache
        Artisan::call('config:clear');
        
        return redirect()->route('admin.settings', ['#payment'])->with('success', 'M-Pesa settings updated successfully');
    }

    /**
     * Update an email template.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateEmailTemplate(Request $request)
    {
        $request->validate([
            'template_key' => 'required|string',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);
        
        // Store email template in cache
        $template = [
            'subject' => $request->subject,
            'body' => $request->body,
        ];
        
        Cache::forever('email_template_' . $request->template_key, $template);
        
        return redirect()->route('admin.settings', ['#email'])->with('success', 'Email template updated successfully');
    }

    /**
     * Toggle maintenance mode.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleMaintenance()
    {
        try {
            if (app()->isDownForMaintenance()) {
                // Bring the application up
                Artisan::call('up');
                $message = 'Application is now live';
            } else {
                // Put the application into maintenance mode
                Artisan::call('down', [
                    '--message' => 'The site is currently down for maintenance. Please check back shortly.',
                    '--retry' => 60
                ]);
                $message = 'Application is now in maintenance mode';
            }
            
            return redirect()->route('admin.settings', ['#system'])->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Failed to toggle maintenance mode: ' . $e->getMessage());
            return redirect()->route('admin.settings', ['#system'])->with('error', 'Failed to toggle maintenance mode');
        }
    }

    /**
     * Create a database backup.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function createBackup()
    {
        try {
            // Define backup filename
            $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $path = storage_path('app/backups');
            
            // Create backup directory if it doesn't exist
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }
            
            // Get database configuration
            $host = config('database.connections.mysql.host');
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            
            // Create backup command
            $command = "mysqldump --user={$username} --password={$password} --host={$host} {$database} > {$path}/{$filename}";
            
            // Execute backup command
            exec($command, $output, $returnVar);
            
            if ($returnVar !== 0) {
                throw new \Exception('Database backup failed');
            }
            
            return redirect()->route('admin.settings', ['#system'])->with('success', 'Database backup created successfully');
        } catch (\Exception $e) {
            Log::error('Failed to create backup: ' . $e->getMessage());
            return redirect()->route('admin.settings', ['#system'])->with('error', 'Failed to create database backup: ' . $e->getMessage());
        }
    }

    /**
     * Download a backup file.
     *
     * @param  string  $filename
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadBackup($filename)
    {
        $path = storage_path('app/backups/' . $filename);
        
        if (file_exists($path)) {
            return response()->download($path);
        }
        
        return redirect()->route('admin.settings', ['#system'])->with('error', 'Backup file not found');
    }

    /**
     * Clear application cache.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clearCache()
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            
            return redirect()->route('admin.settings', ['#system'])->with('success', 'Cache cleared successfully');
        } catch (\Exception $e) {
            Log::error('Failed to clear cache: ' . $e->getMessage());
            return redirect()->route('admin.settings', ['#system'])->with('error', 'Failed to clear cache');
        }
    }

    /**
     * Update an environment variable value in the .env file.
     *
     * @param  string  $envData
     * @param  string  $key
     * @param  string  $value
     * @return string
     */
    private function updateEnvValue($envData, $key, $value)
    {
        if (str_contains($envData, $key . '=')) {
            return preg_replace('/^' . $key . '=.*$/m', $key . '=' . $value, $envData);
        }
        
        return $envData . "\n" . $key . '=' . $value . "\n";
    }
    
    /**
     * Update settings in cache.
     *
     * @param  array  $settings
     * @return void
     */
    protected function updateSettings($settings)
    {
        foreach ($settings as $key => $value) {
            if (!empty($value)) {
                Cache::forever('setting_' . $key, $value);
            }
        }
    }

    /**
     * Update a value in the .env file
     *
     * @param string $key
     * @param string $value
     * @return bool
     */
    private function updateEnvFile($key, $value)
    {
        $path = base_path('.env');

        if (file_exists($path)) {
            $escaped = preg_quote('=' . env($key), '/');
            $pattern = "/^{$key}{$escaped}/m";

            if (preg_match($pattern, file_get_contents($path))) {
                file_put_contents(
                    $path, 
                    preg_replace(
                        $pattern, 
                        "{$key}={$value}", 
                        file_get_contents($path)
                    )
                );
            } else {
                file_put_contents($path, file_get_contents($path) . "\n{$key}={$value}\n");
            }

            return true;
        }

        return false;
    }

    /**
     * Get email templates from cache
     *
     * @return array
     */
    private function getEmailTemplates()
    {
        $templates = [
            'order_assignment' => Cache::get('email_template_order_assignment', [
                'subject' => 'New Order Assignment: [Order_ID] - [Order_Title]',
                'body' => "Hello [Writer_Name],\n\nYou have been assigned a new order.\n\nOrder ID: [Order_ID]\nTitle: [Order_Title]\nDeadline: [Order_Deadline]\nPayment: $[Order_Price]\n\nPlease log in to your dashboard to view the full details and accept the assignment.\n\nBest regards,\nTechnical Writers Team"
            ]),
            'revision_request' => Cache::get('email_template_revision_request', [
                'subject' => 'Revision Requested: [Order_ID] - [Order_Title]',
                'body' => "Hello [Writer_Name],\n\nA revision has been requested for your order.\n\nOrder ID: [Order_ID]\nTitle: [Order_Title]\nRevision Comments: [Revision_Comments]\n\nPlease log in to your dashboard to view the full details and submit the revised work by [Revision_Deadline].\n\nBest regards,\nTechnical Writers Team"
            ]),
            'order_completion' => Cache::get('email_template_order_completion', [
                'subject' => 'Order Completed: [Order_ID] - [Order_Title]',
                'body' => "Hello [Writer_Name],\n\nCongratulations! Your work on the following order has been completed and approved.\n\nOrder ID: [Order_ID]\nTitle: [Order_Title]\nPayment Amount: $[Order_Price]\n\nYour payment will be processed according to our payment schedule. You can view your payment details in your dashboard.\n\nThank you for your excellent work!\n\nBest regards,\nTechnical Writers Team"
            ]),
            'payment_notification' => Cache::get('email_template_payment_notification', [
                'subject' => 'Payment Processed: $[Payment_Amount]',
                'body' => "Hello [Writer_Name],\n\nWe're pleased to inform you that your payment has been processed.\n\nPayment Details:\n- Amount: $[Payment_Amount]\n- Transaction ID: [Transaction_ID]\n- Payment Method: [Payment_Method]\n- Date: [Payment_Date]\n\nThis payment covers the following completed orders:\n[Order_List]\n\nIf you have any questions about this payment, please contact our support team.\n\nBest regards,\nTechnical Writers Finance Team"
            ]),
            'account_suspension' => Cache::get('email_template_account_suspension', [
                'subject' => 'Account Temporarily Suspended',
                'body' => "Hello [Writer_Name],\n\nWe regret to inform you that your account has been temporarily suspended.\n\nReason for suspension: [Suspension_Reason]\n\nDuring this suspension period, you will not be able to access your account or receive new orders. Any pending orders will be reassigned to other writers.\n\nIf you believe this suspension was made in error or you would like to discuss this further, please contact our support team at [Support_Email].\n\nBest regards,\nTechnical Writers Team"
            ])
        ];

        return $templates;
    }

    /**
     * Get recent database backups
     *
     * @return array
     */
    private function getRecentBackups()
    {
        $backups = [];
        $backupPath = storage_path('app/backups');
        
        if (file_exists($backupPath)) {
            $files = glob($backupPath . '/*.sql');
            
            foreach ($files as $file) {
                $filename = basename($file);
                $size = $this->formatSize(filesize($file));
                $date = date('F j, Y, g:i a', filemtime($file));
                
                $backups[] = [
                    'filename' => $filename,
                    'size' => $size,
                    'date' => $date
                ];
            }
            
            // Sort by most recent first
            usort($backups, function ($a, $b) {
                return strtotime(str_replace('backup_', '', $b['filename'])) - strtotime(str_replace('backup_', '', $a['filename']));
            });
            
            // Limit to most recent 5
            $backups = array_slice($backups, 0, 5);
        }
        
        return $backups;
    }

    /**
     * Format file size
     *
     * @param int $bytes
     * @return string
     */
    private function formatSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Store a new admin user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|string|in:admin,super_admin',
        ]);
        
        $user = new User();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = bcrypt($validated['password']);
        $user->phone = $validated['phone'] ?? null;
        $user->usertype = User::ROLE_ADMIN;
        $user->status = 'active';
        $user->role = $validated['role'];
        $user->save();
        
        return redirect()->route('admin.settings', ['#users'])->with('success', 'Admin user created successfully');
    }

    /**
     * Update an admin user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|string|in:admin,super_admin',
            'status' => 'required|string|in:active,inactive,suspended',
        ]);
        
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        if ($validated['password']) {
            $user->password = bcrypt($validated['password']);
        }
        $user->phone = $validated['phone'] ?? null;
        $user->role = $validated['role'];
        $user->status = $validated['status'];
        $user->save();
        
        return redirect()->route('admin.settings', ['#users'])->with('success', 'Admin user updated successfully');
    }

    /**
     * Delete an admin user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyUser($id)
    {
        // Don't allow deleting own account
        if (auth()->id() == $id) {
            return redirect()->route('admin.settings', ['#users'])->with('error', 'You cannot delete your own account');
        }
        
        $user = User::findOrFail($id);
        $user->delete();
        
        return redirect()->route('admin.settings', ['#users'])->with('success', 'Admin user deleted successfully');
    }

        /**
     * Toggle maintenance mode for the writers portal.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleWriterMaintenance()
    {
        try {
            // Determine the current status
            $maintenanceFile = storage_path('framework/down_writers');
            $isDown = file_exists($maintenanceFile);
            
            if ($isDown) {
                // Bring the writer portal up
                if (file_exists($maintenanceFile)) {
                    unlink($maintenanceFile);
                }
                $message = 'Writer portal is now live';
            } else {
                // Put the writer portal into maintenance mode
                file_put_contents($maintenanceFile, json_encode([
                    'time' => time(),
                    'message' => 'The writers portal is currently down for maintenance. Please check back shortly.',
                    'retry' => 60
                ]));
                $message = 'Writer portal is now in maintenance mode';
            }
            
            return redirect()->route('admin.settings', ['#system'])->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Failed to toggle writer portal maintenance mode: ' . $e->getMessage());
            return redirect()->route('admin.settings', ['#system'])->with('error', 'Failed to toggle writer portal maintenance mode');
        }
    }

    /**
     * Toggle debug mode for the writers portal.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleWriterDebug()
    {
        try {
            // Path to the writers portal .env file
            $envFile = '/path/to/your/writers/portal/.env';
            
            if (!file_exists($envFile)) {
                return redirect()->route('admin.settings', ['#system'])
                    ->with('error', 'Writers portal .env file not found');
            }
            
            // Read the current .env file
            $env = file_get_contents($envFile);
            
            // Check if debug is enabled
            if (preg_match('/APP_DEBUG=true/i', $env)) {
                // Disable debug mode
                $env = preg_replace('/APP_DEBUG=true/i', 'APP_DEBUG=false', $env);
                $message = 'Debug mode has been disabled for the writers portal';
            } else {
                // Enable debug mode
                $env = preg_replace('/APP_DEBUG=false/i', 'APP_DEBUG=true', $env);
                $message = 'Debug mode has been enabled for the writers portal';
            }
            
            // Write the updated .env file
            file_put_contents($envFile, $env);
            
            // Clear config cache on the writers portal
            // This is a simplistic approach - in production you might want to use
            // SSH or an API to trigger cache clearing on the remote server
            if (function_exists('shell_exec')) {
                shell_exec('cd /path/to/your/writers/portal && php artisan config:clear');
            }
            
            return redirect()->route('admin.settings', ['#system'])->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Failed to toggle writer portal debug mode: ' . $e->getMessage());
            return redirect()->route('admin.settings', ['#system'])->with('error', 'Failed to toggle writer portal debug mode: ' . $e->getMessage());
        }
    }
}