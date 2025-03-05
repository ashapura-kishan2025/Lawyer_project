<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome Email</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f7fc;">

    <!-- Email Container -->
    <div style="width: 100%; background-color: #f4f7fc; padding: 30px; box-sizing: border-box;">
        <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);">

            <!-- Logo Row -->
            <!-- <div style="text-align: center; margin-bottom: 30px;">
                <img src="{{ asset('assets/img/logo/logo.svg') }}" alt="Company Logo" style="max-width: 180px;">
            </div> -->

            <!-- Card Body -->
            <div style="text-align: center; padding: 20px; background-color: #f9f9f9; border-radius: 8px;">
                <h1 style="font-size: 24px; color: #333333;">Hello, {{ $userName }}!</h1>
                <p style="font-size: 16px; color: #555555;">Welcome to our platform. We're excited to have you join us!</p>
            </div>

            <!-- Account Details -->
            <div style="padding: 20px; background-color: #f9f9f9; border-radius: 8px; margin-top: 20px;">
                <h3 style="font-size: 18px; color: #333333; text-align: center; margin-bottom: 15px;">Your Account Details</h3>
                <table style="width: 100%; border-spacing: 10px;">
                    <tr>
                        <td style="padding: 8px; font-size: 16px; font-weight: bold; color: #333333;">Email:</td>
                        <td style="padding: 8px; font-size: 16px; color: #555555;">{{ $userEmail }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px; font-size: 16px; font-weight: bold; color: #333333;">Password:</td>
                        <td style="padding: 8px; font-size: 16px; color: #555555;">{{ $userPassword }}</td>
                    </tr>
                </table>
            </div>
            <!-- Login Button -->
            <div style="text-align: center; margin-top: 20px;">
                <a href="{{ route('auth-login-basic') }}" style="padding: 12px 30px; background-color: #007bff; color: #ffffff; font-weight: bold; text-decoration: none; border-radius: 50px; font-size: 16px;">Login to Your Account</a>
            </div>

            <!-- Footer -->
            <div style="text-align: center; margin-top: 30px; font-size: 14px; color: #777777;">
                <p>Best regards,</p>
                <p><strong>Lawyer Dashboard</strong></p>
            </div>
        </div>
    </div>

</body>
</html>
