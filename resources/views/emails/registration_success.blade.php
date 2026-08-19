<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful - Digital India Yug</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333333;
        }
        .container {
            max-width: 600px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }
        .header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: #ffffff;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .header p {
            margin: 5px 0 0 0;
            font-size: 14px;
            opacity: 0.9;
        }
        .content {
            padding: 30px 25px;
        }
        .greeting {
            font-size: 18px;
            font-weight: 600;
            color: #1e3c72;
            margin-bottom: 15px;
        }
        .message {
            font-size: 15px;
            line-height: 1.6;
            color: #555555;
            margin-bottom: 25px;
        }
        .details-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
        }
        .details-card h3 {
            margin-top: 0;
            margin-bottom: 15px;
            color: #2d3748;
            font-size: 16px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 8px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dashed #e2e8f0;
            font-size: 14px;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 600;
            color: #4a5568;
        }
        .detail-value {
            color: #1a202c;
            font-weight: 500;
        }
        .badge {
            display: inline-block;
            background-color: #319795;
            color: white;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .footer {
            background-color: #edf2f7;
            padding: 20px;
            text-align: center;
            font-size: 13px;
            color: #718096;
            border-top: 1px solid #e2e8f0;
        }
        .footer a {
            color: #2a5298;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Digital India Yug</h1>
            <p>Empowering Citizens Across India</p>
        </div>

        <div class="content">
            <div class="greeting">Hello {{ $user->name }},</div>
            <div class="message">
                Thank you for registering with <strong>Digital India Yug</strong>! Your registration has been completed successfully. Below are your registration details for your reference:
            </div>

            <div class="details-card">
                <h3>Registration Details</h3>
                @if($user->is_partner)
                <div class="detail-row">
                    <span class="detail-label">Partner ID:</span>
                    <span class="detail-value"><strong>{{ $user->custom_id ?? ('PTR-'.str_pad($user->id, 5, '0', STR_PAD_LEFT)) }}</strong></span>
                </div>
                @if(!empty($user->partner_code))
                <div class="detail-row">
                    <span class="detail-label">Partner Code:</span>
                    <span class="detail-value"><strong>{{ $user->partner_code }}</strong></span>
                </div>
                @endif
                @else
                <div class="detail-row">
                    <span class="detail-label">Registration ID:</span>
                    <span class="detail-value"><strong>{{ $user->custom_id ?? ('#'.str_pad($user->id, 5, '0', STR_PAD_LEFT)) }}</strong></span>
                </div>
                @endif
                <div class="detail-row">
                    <span class="detail-label">Full Name:</span>
                    <span class="detail-value">{{ $user->name }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Email Address:</span>
                    <span class="detail-value">{{ $user->email }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Mobile Number:</span>
                    <span class="detail-value">{{ $user->mobile }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Category:</span>
                    <span class="detail-value"><span class="badge">{{ ucfirst($user->category) }}</span></span>
                </div>
                @if(!empty($user->city) || !empty($user->state))
                <div class="detail-row">
                    <span class="detail-label">Location:</span>
                    <span class="detail-value">{{ implode(', ', array_filter([$user->city, $user->state])) }}</span>
                </div>
                @endif
                @if(!empty($user->referred_partner_code))
                <div class="detail-row">
                    <span class="detail-label">Referred Partner Code:</span>
                    <span class="detail-value"><strong>{{ $user->referred_partner_code }}</strong></span>
                </div>
                @endif
                <div class="detail-row">
                    <span class="detail-label">Registration Date:</span>
                    <span class="detail-value">{{ $user->created_at ? $user->created_at->setTimezone('Asia/Kolkata')->format('d M Y, h:i A') : date('d M Y') }}</span>
                </div>
            </div>

            <div style="margin-bottom: 15px; padding: 14px 18px; background-color: #f0f7ff; border-left: 4px solid #2196F3; border-radius: 6px; font-size: 13px; color: #1e3c72; line-height: 1.5;">
                📌 <strong>Terms &amp; Conditions Acceptance Record:</strong><br>
                By completing your registration, you have formally reviewed, agreed to, and accepted all <strong>Terms &amp; Conditions</strong>, <strong>Privacy Policy</strong>, and <strong>Service Guidelines</strong> applicable to Digital India Yug.
            </div>

            <div style="margin-bottom: 20px; padding: 14px 18px; background-color: #e8f5e9; border-left: 4px solid #4CAF50; border-radius: 6px; font-size: 13px; color: #1b5e20; line-height: 1.5;">
                📎 <strong>Attached Document:</strong> A PDF copy of the <strong>{{ (str_contains(strtolower($user->category ?? ''), 'student') || str_contains(strtolower($user->category ?? ''), 'job seeker')) ? 'Employment Assistance' : 'Business Assistance' }} Terms &amp; Conditions</strong> accepted by you has been attached to this email for your official records.
            </div>

            <div class="message">
                If you have any questions or require assistance, please feel free to reach out to our support team through the app.
            </div>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} Digital India Yug. All rights reserved.<br>
            This is an automated confirmation email.
        </div>
    </div>
</body>
</html>
