<?php

namespace App\Services;

use App\Models\Order;
use App\Models\File;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Nesk\Puphpeteer\Puppeteer;
use Nesk\Rialto\Data\JsFunction;
use Carbon\Carbon;

class OrderScrapingService
{
    protected $baseUrl = 'https://www.uvocorp.com';
    protected $loginUrl = 'https://www.uvocorp.com/login.html';
    protected $ordersUrl = 'https://www.uvocorp.com/orders/current.html';

    /**
     * Scrape orders from UvoCorp
     */
    public function scrapeOrders()
    {
        try {
            // Initialize Puppeteer
            $puppeteer = new Puppeteer([
                'read_timeout' => 60,
                'write_timeout' => 60,
            ]);
            
            $browser = $puppeteer->launch([
                'headless' => config('scraping.puppeteer.headless', true),
                'args' => ['--no-sandbox', '--disable-setuid-sandbox'],
                'ignoreHTTPSErrors' => true,
            ]);
            
            $page = $browser->newPage();
            
            // Set viewport and user agent
            $page->setViewport(['width' => 1280, 'height' => 800]);
            $page->setUserAgent(config('scraping.puppeteer.user_agent'));
            
            // Handle reCAPTCHA
            $page->setRequestInterception(true);
            $page->on('request', JsFunction::createWithParameters(['request'], "
                if (request.url().includes('recaptcha')) {
                    request.respond({
                        status: 200,
                        contentType: 'application/javascript',
                        body: 'console.log(\"Intercepted reCAPTCHA\");'
                    });
                } else {
                    request.continue();
                }
            "));
            
            // Login to UvoCorp
            $loginSuccess = $this->login($page, config('scraping.uvocorp.username'), config('scraping.uvocorp.password'));
            
            if (!$loginSuccess) {
                Log::error('Failed to login to UvoCorp');
                $browser->close();
                return [
                    'success' => false,
                    'message' => 'Login failed',
                    'orders' => []
                ];
            }
            
            // Navigate to orders page
            Log::info('Navigating to orders page: ' . $this->ordersUrl);
            $response = $page->goto($this->ordersUrl, ['waitUntil' => 'networkidle0']);
            
            if (!$response->ok()) {
                Log::error('Failed to load orders page');
                $browser->close();
                return [
                    'success' => false,
                    'message' => 'Failed to load orders page',
                    'orders' => []
                ];
            }
            
            // Wait for orders to load
            $page->waitForSelector('.order-link');
            
            // Extract orders data
            $orders = $this->extractOrders($page);
            
            if (empty($orders)) {
                Log::warning('No orders found');
                $browser->close();
                return [
                    'success' => true,
                    'message' => 'No orders found',
                    'orders' => []
                ];
            }
            
            Log::info('Found ' . count($orders) . ' orders');
            
            $processedOrders = [];
            
            // Process each order
            foreach ($orders as $orderData) {
                $order = $this->processOrder($page, $orderData);
                if ($order) {
                    $processedOrders[] = $order;
                }
            }
            
            $browser->close();
            
            return [
                'success' => true,
                'message' => 'Orders scraped successfully',
                'count' => count($processedOrders),
                'orders' => $processedOrders
            ];
            
        } catch (\Exception $e) {
            Log::error('Scraping error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            if (isset($browser)) {
                $browser->close();
            }
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'orders' => []
            ];
        }
    }
    
    /**
     * Login to UvoCorp
     */
    protected function login($page, $username, $password)
    {
        try {
            Log::info('Navigating to login page: ' . $this->loginUrl);
            $response = $page->goto($this->loginUrl, ['waitUntil' => 'networkidle0']);
            
            if (!$response->ok()) {
                Log::error('Failed to load login page');
                return false;
            }
            
            // Wait for login form to be available
            $page->waitForSelector('#loginForm');
            
            // Fill login form
            $page->type('input[name="loginEmail"]', $username);
            $page->type('input[name="loginPassword"]', $password);
            
            // Click login button
            $page->click('#loginSubmit');
            
            // Wait for navigation
            try {
                $page->waitForNavigation(['timeout' => 10000]);
            } catch (\Exception $e) {
                Log::warning('Navigation timeout after login, checking if login was successful');
            }
            
            // Check if login was successful by looking for error message or orders page
            $loginError = $page->evaluate('document.querySelector("#loginError") && document.querySelector("#loginError").textContent.trim()');
            
            if ($loginError) {
                Log::error('Login error: ' . $loginError);
                return false;
            }
            
            // Check if we need to refresh the page (sometimes reCAPTCHA causes issues)
            $currentUrl = $page->evaluate('window.location.href');
            if (strpos($currentUrl, 'login') !== false) {
                Log::warning('Still on login page, attempting to resubmit...');
                
                // Clear fields and try again
                $page->evaluate('document.querySelector("input[name=\'loginEmail\']").value = ""');
                $page->evaluate('document.querySelector("input[name=\'loginPassword\']").value = ""');
                
                $page->type('input[name="loginEmail"]', $username);
                $page->type('input[name="loginPassword"]', $password);
                
                $page->click('#loginSubmit');
                
                try {
                    $page->waitForNavigation(['timeout' => 10000]);
                } catch (\Exception $e) {
                    Log::warning('Navigation timeout after second login attempt');
                }
                
                $currentUrl = $page->evaluate('window.location.href');
                if (strpos($currentUrl, 'login') !== false) {
                    Log::error('Login failed after retry');
                    return false;
                }
            }
            
            Log::info('Login successful');
            return true;
            
        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Extract orders from the current page
     */
    protected function extractOrders($page)
    {
        return $page->evaluate(JsFunction::createWithBody('
            const orderLinks = document.querySelectorAll(".order-link");
            return Array.from(orderLinks).map(link => {
                const row = link.querySelector(".row");
                const orderId = row.dataset.order_id;
                const orderUrl = link.getAttribute("href");
                const status = row.querySelector(".status_type")?.textContent.trim() || "";
                const titleElement = row.querySelector(".title-order-description");
                const title = titleElement ? titleElement.textContent.trim() : "";
                
                const disciplineElement = row.querySelector(".discipline-order");
                const discipline = disciplineElement ? disciplineElement.getAttribute("data-title")?.trim() || "" : "";
                
                const academicLevelElement = row.querySelector(".academic-level-order");
                const academicLevel = academicLevelElement ? academicLevelElement.textContent.trim() : "";
                
                // Get deadline text
                const deadlineElement = row.querySelector(".time-order span");
                const deadlineText = deadlineElement ? deadlineElement.getAttribute("data-title")?.trim() || "" : "";
                
                // Get cost
                const costElement = row.querySelector(".pages-cost-order");
                const costText = costElement ? costElement.textContent.trim() : "";
                const costMatch = costText.match(/\$[\d.]+/);
                const cost = costMatch ? costMatch[0] : "$0.00";
                
                // Check if there are files
                const filesElement = row.querySelector(".title-files-order");
                const filesCount = filesElement ? filesElement.textContent.trim() : "0 files";
                
                // Get instructions if available
                const tooltipElement = row.querySelector(".title-order");
                const tooltipInstructions = tooltipElement ? tooltipElement.getAttribute("data-html") : "";
                let instructions = "";
                if (tooltipInstructions) {
                    const match = tooltipInstructions.match(/<div class=\'tooltip-instruction-order\'>([^<]+)<\/div>/);
                    if (match && match[1]) {
                        instructions = match[1].trim();
                    }
                }
                
                // Get size (pages)
                const pagesElement = row.querySelector(".pages-cost-order span");
                const pagesText = pagesElement ? pagesElement.textContent.trim() : "0";
                const pages = parseInt(pagesText, 10) || 0;
                
                return {
                    orderId,
                    orderUrl,
                    status,
                    title,
                    discipline,
                    academicLevel,
                    deadline: deadlineText,
                    cost,
                    pages,
                    filesCount,
                    instructions,
                    hasFiles: filesCount !== "0 files"
                };
            });
        '));
    }
    
    /**
     * Process a single order
     */
    protected function processOrder($page, $orderData)
    {
        Log::info('Processing order: ' . $orderData['orderId']);
        
        // Check if order already exists
        $existingOrder = Order::where('id', $orderData['orderId'])->first();
        
        if ($existingOrder) {
            Log::info('Order already exists: ' . $orderData['orderId']);
            return $existingOrder;
        }
        
        // Parse deadline
        $deadline = $this->parseDeadline($orderData['deadline']);
        
        // Extract price from cost string
        $price = floatval(str_replace(['$', ','], '', $orderData['cost']));
        
        // Create new order
        $order = new Order();
        $order->id = $orderData['orderId']; // Use the external ID as our primary ID
        $order->title = $orderData['title'];
        $order->instructions = $orderData['instructions'];
        $order->price = $price;
        $order->deadline = $deadline;
        $order->task_size = $orderData['pages'];
        $order->discipline = $orderData['discipline'];
        $order->type_of_service = $orderData['academicLevel'];
        $order->status = Order::STATUS_AVAILABLE; // Default to available
        $order->save();
        
        // If the order has files, download them
        if ($orderData['hasFiles']) {
            $this->downloadOrderFiles($page, $order, $orderData['orderUrl']);
        }
        
        Log::info('Order saved: ' . $orderData['orderId']);
        return $order;
    }
    
    /**
     * Download files for an order
     */
    protected function downloadOrderFiles($page, $order, $orderUrl)
    {
        try {
            // Navigate to order details page
            $fullUrl = $this->baseUrl . $orderUrl;
            Log::info('Navigating to order page: ' . $fullUrl);
            
            $response = $page->goto($fullUrl, ['waitUntil' => 'networkidle0']);
            
            if (!$response->ok()) {
                Log::error('Failed to load order page');
                return;
            }
            
            // Wait for files section to load
            $page->waitForSelector('.order-files-block');
            
            // Extract file information
            $files = $page->evaluate(JsFunction::createWithBody('
                const fileLinks = document.querySelectorAll(".order-files-block .download-file-link");
                return Array.from(fileLinks).map(link => {
                    return {
                        name: link.textContent.trim(),
                        url: link.getAttribute("href")
                    };
                });
            '));
            
            if (empty($files)) {
                Log::info('No files found for order: ' . $order->id);
                return;
            }
            
            Log::info('Found ' . count($files) . ' files for order: ' . $order->id);
            
            // Find or create an admin user for file uploads
            $adminUser = User::where('usertype', User::ROLE_ADMIN)->first();
            
            if (!$adminUser) {
                Log::warning('No admin user found for file attribution');
                return;
            }
            
            // Download each file
            foreach ($files as $fileData) {
                $fileUrl = $this->baseUrl . $fileData['url'];
                Log::info('Downloading file: ' . $fileData['name'] . ' from ' . $fileUrl);
                
                // Navigate to file URL to trigger download
                $fileResponse = $page->goto($fileUrl, ['waitUntil' => 'networkidle0']);
                
                if (!$fileResponse->ok()) {
                    Log::error('Failed to download file: ' . $fileData['name']);
                    continue;
                }
                
                // Get file content
                $content = $page->content();
                
                // Create safe filename
                $filename = $order->id . '_' . \Illuminate\Support\Str::slug(pathinfo($fileData['name'], PATHINFO_FILENAME)) . '.' . pathinfo($fileData['name'], PATHINFO_EXTENSION);
                
                // Save file to storage
                $path = 'order_files/' . $filename;
                Storage::put($path, $content);
                
                // Get file size
                $size = Storage::size($path);
                
                // Create file record using the existing File model
                $file = new File();
                $file->name = $fileData['name'];
                $file->path = $path;
                $file->size = $size;
                $file->fileable_id = $order->id;
                $file->fileable_type = get_class($order);
                $file->uploaded_by = $adminUser->id;
                $file->save();
                
                Log::info('File saved: ' . $fileData['name']);
            }
            
        } catch (\Exception $e) {
            Log::error('Error downloading files: ' . $e->getMessage());
        }
    }
    
    /**
     * Parse deadline string to datetime
     */
    protected function parseDeadline($deadlineString)
    {
        try {
            // Format typically: "19 Mar 2025 - 12:22 AM"
            if (preg_match('/(\d+)\s+(\w+)\s+(\d+)\s+-\s+(\d+):(\d+)\s+(\w+)/', $deadlineString, $matches)) {
                $day = $matches[1];
                $month = $matches[2];
                $year = $matches[3];
                $hour = $matches[4];
                $minute = $matches[5];
                $ampm = $matches[6];
                
                return Carbon::createFromFormat('d M Y h:i A', "$day $month $year $hour:$minute $ampm");
            }
            
            // Format for relative time: "7d 19h 8m" or "33h 26m"
            if (preg_match('/(\d+)d\s+(\d+)h\s+(\d+)m/', $deadlineString, $matches)) {
                return Carbon::now()->addDays($matches[1])->addHours($matches[2])->addMinutes($matches[3]);
            }
            
            if (preg_match('/(\d+)h\s+(\d+)m/', $deadlineString, $matches)) {
                return Carbon::now()->addHours($matches[1])->addMinutes($matches[2]);
            }
            
            // Default to 3 days from now if parsing fails
            Log::warning('Could not parse deadline: ' . $deadlineString . '. Using default.');
            return Carbon::now()->addDays(3);
        } catch (\Exception $e) {
            Log::error('Error parsing deadline: ' . $e->getMessage());
            return Carbon::now()->addDays(3);
        }
    }
}