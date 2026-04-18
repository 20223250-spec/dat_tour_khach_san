<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận đặt tour</title>
    <style>
        body {
            margin: 0;
            font-family: "Segoe UI", sans-serif;
            background: #f4f7ff;
            color: #1b2e4b;
        }

        .wrapper {
            max-width: 620px;
            margin: 0 auto;
            padding: 20px 14px;
        }

        .card {
            background: #fff;
            border-radius: 18px;
            overflow: hidden;
            border: 1px solid #dce5ff;
        }

        .head {
            padding: 22px;
            color: #fff;
            background: linear-gradient(140deg, #06122b 0%, #0f4ad6 65%, #14a4c7 100%);
        }

        .content {
            padding: 22px;
        }

        .info {
            border-radius: 14px;
            border: 1px solid #dce5ff;
            padding: 14px;
            margin: 14px 0;
            background: #f9fbff;
        }

        .label {
            color: #5f6f89;
            font-size: 13px;
        }

        .value {
            font-weight: 600;
            margin: 2px 0 10px;
        }

        .foot {
            text-align: center;
            font-size: 12px;
            color: #6d7d95;
            margin-top: 14px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <div class="head">
                <h1 style="margin: 0 0 6px; font-size: 22px;">TourBooking</h1>
                <p style="margin: 0;">Đặt tour thành công - Mã đơn #{{ $booking->id }}</p>
            </div>

            <div class="content">
                <p>Xin chào <strong>{{ $booking->customer_name }}</strong>,</p>
                <p>Cảm ơn bạn đã đặt tour. Đơn đặt của bạn đã được ghi nhận với thông tin sau:</p>

                <div class="info">
                    <div class="label">Tour</div>
                    <div class="value">{{ $booking->tour->name }}</div>

                    <div class="label">Điểm đến</div>
                    <div class="value">{{ $booking->tour->destination }}</div>

                    <div class="label">Ngày khởi hành</div>
                    <div class="value">{{ \Carbon\Carbon::parse($booking->tour->start_date)->format('d/m/Y') }}</div>

                    <div class="label">Số người</div>
                    <div class="value">{{ $booking->number_of_people }}</div>

                    <div class="label">Tổng tiền</div>
                    <div class="value">{{ number_format($booking->total_price, 0, ',', '.') }} VND</div>
                </div>

                <p>Chúng tôi sẽ liên hệ qua số <strong>{{ $booking->customer_phone }}</strong> nếu cần bổ sung thông tin.</p>
                <p>Chúc bạn có một chuyến đi thật tuyệt vời.</p>
                <p>Trân trọng,<br>TourBooking Team</p>
            </div>
        </div>

        <div class="foot">
            Email được gửi tự động. Vui lòng không trả lời trực tiếp email này.
        </div>
    </div>
</body>
</html>


