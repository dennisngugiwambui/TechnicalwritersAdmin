<!-- resources/views/emails/order-assigned.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #{{ $order->id }}: Please confirm that you are working</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid #eaeaea;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 10px;
        }
        .order-number {
            text-align: right;
            font-size: 16px;
            color: #666;
            margin-top: 10px;
        }
        .content {
            padding: 20px;
        }
        .confirmation-box {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            border-top: 1px solid #e0e0e0;
            border-bottom: 1px solid #e0e0e0;
        }
        .confirmation-title {
            font-size: 18px;
            color: #0056b3;
            margin-bottom: 15px;
        }
        .order-details {
            background-color: #f9f9f9;
            border: 1px solid #eaeaea;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
        }
        .order-details p {
            margin: 5px 0;
        }
        .button {
            display: inline-block;
            background-color: #f0ad4e;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 4px;
            font-weight: bold;
            margin: 15px 0;
        }
        .button:hover {
            background-color: #ec971f;
        }
        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #777;
            border-top: 1px solid #eaeaea;
        }
        .contact-links {
            margin-top: 15px;
        }
        .contact-links a {
            color: #0056b3;
            text-decoration: none;
            margin: 0 10px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <img src="{{ asset('images/technicalwriters.jpg') }}" alt="TechnicalWriters Logo" class="logo">
            <div class="order-number">Order #{{ $order->id }}</div>
        </div>
        
        <div class="content">
            <div class="confirmation-box">
                <h2 class="confirmation-title">Order #{{ $order->id }}: Please confirm that you are working</h2>
                
                <p>Please check this assigneded order #{{ $order->id }} and confirm that you are working on it.</p>
                
                <p>If you cannot complete the order, or if it's missing crucial information, click the 'Reject Assignment' button and indicate the corresponding reason. You may also send a message to the customer if the order contains contradictory information.</p>
                
                <a href="{{ route('home') }}" class="button">Check Order</a>
            </div>
            
            <div class="order-details">
                <p><strong>Order ID:</strong> #{{ $order->id }}</p>
                <p><strong>Title:</strong> {{ $order->title }}</p>
            </div>
        </div>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} TechnicalWriters. All rights reserved.</p>
            
            <div class="contact-links">
                <a href="{{ route('home') }}">TechnicalWriters.com</a>
                <a href="{{ route('home') }}">Contact Us</a>
            </div>
        </div>
    </div>
</body>
</html>