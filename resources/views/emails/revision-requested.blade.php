<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revision Requested for Order #{{ $order->id }}</title>
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
        .revision-details {
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .button {
            display: inline-block;
            background-color: #ffc107;
            color: #212529;
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
            <h2>Revision Requested</h2>
        </div>
        
        <div class="content">
            <p>Hello {{ $order->writer->name }},</p>
            
            <p>The client has requested a revision for Order #{{ $order->id }}. Please review the revision comments and make the necessary adjustments as soon as possible.</p>
            
            <div class="revision-details">
                <h3>Revision Comments:</h3>
                <p>{{ $comments }}</p>
            </div>
            
            <div class="order-details">
                <p><strong>Order ID:</strong> #{{ $order->id }}</p>
                <p><strong>Title:</strong> {{ $order->title }}</p>
                <p><strong>Type of Service:</strong> {{ $order->type_of_service }}</p>
                <p><strong>Deadline for Revision:</strong> {{ now()->addDays(1)->format('F j, Y, g:i a') }}</p>
            </div>
            
            <p>Please prioritize this revision and complete it within the next 24 hours.</p>
            
            <a href="{{ route('writer.orders.show', $order->id) }}" class="button">View Order & Revision Details</a>
        </div>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} TechnicalWriters. All rights reserved.</p>
            <p>Replies to this e-mail address will not be read or responded to. If you wish to reply, please check the order in your account.</p>
        </div>
    </div>
</body>
</html>