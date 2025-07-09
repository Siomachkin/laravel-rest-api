<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Welcome</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .content {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            color: #6c757d;
            font-size: 14px;
        }
    </style>
</head>
<body>
<div class="header">
    <h1>Welcome to Our Application!</h1>
</div>

<div class="content">
    <p>Hello {{ $userName }},</p>

    <p>Thank you for joining our application. We're excited to have you on board!</p>

    <p>Your account details:</p>
    <ul>
        <li><strong>Name:</strong> {{ $user->full_name }}</li>
        <li><strong>Phone:</strong> {{ $user->phone ?? 'Not provided' }}</li>
        <li><strong>Primary Email:</strong> {{ $user->primary_email }}</li>
    </ul>

    <p>If you have any questions, please don't hesitate to contact us.</p>

    <p>Best regards,<br>The Team</p>
</div>

<div class="footer">
    <p>&copy; {{ date('Y') }} Our Application. All rights reserved.</p>
</div>
</body>
</html>
