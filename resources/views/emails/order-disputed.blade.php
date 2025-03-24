<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dispute for Order #{{ $order->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f8f9fa;
            text-align: center;
            padding: 20px;
            border-bottom: 1px solid #e9ecef;
        }
        .logo {
            max-width: 200px;
            margin-bottom: 10px;
        }
        .content {
            padding: 20px;
        }
        .dispute-details {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
            color: #721c24;
        }
        .order-details {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .button {
            display: inline-block;
            background-color: #dc3545;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #6c757d;
            border-top: 1px solid #e9ecef;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('images/logo.png') }}" alt="TechnicalWriters Logo" class="logo">
            <h2>Order Dispute</h2>
        </div>
        
        <div class="content">
            <p>Hello {{ $order->writer->name }},</p>
            
            <p>Unfortunately, Order #{{ $order->id }} has been marked as disputed. Please review the details below and respond accordingly.</p>
            
            <div class="dispute-details">
                <h3>Dispute Reason:</h3>
                <p>{{ $reason }}</p>
            </div>
            
            <div class="order-details">
                <p><strong>Order ID:</strong> #{{ $order->id }}</p>
                <p><strong>Title:</strong> {{ $order->title }}</p>
                <p><strong>Type of Service:</strong> {{ $order->type_of_service }}</p>
                <p><strong>Deadline:</strong> {{ $order->deadline->format('F j, Y, g:i a') }}</p>
            </div>
            
            <p>Please review the dispute details and address the concerns as soon as possible. Contact support if you need any clarification.</p>
            
            <p>We need to resolve this matter promptly to maintain our high-quality standards.</p>
            
            <a href="{{ route('writer.orders.show', $order->id) }}" class="button">View Order & Respond to Dispute</a>
        </div>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} TechnicalWriters. All rights reserved.</p>
            <p>Replies to this e-mail address will not be read or responded to. If you wish to respond to this dispute, please do so through your account.</p>
        </div>
    </div>
</body>
</html>