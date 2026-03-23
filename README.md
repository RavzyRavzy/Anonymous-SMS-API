📩 Anonymous.php – Advanced Messaging API

«⚠️ Disclaimer: This project is intended strictly for educational purposes and legitimate messaging use cases. Any misuse, including spam, harassment, or unauthorized messaging, is strictly prohibited.»

---

🚀 Overview

Anonymous.php is a PHP-based messaging API designed to demonstrate advanced concepts such as:

- Multi-endpoint request handling
- Proxy rotation & testing
- Concurrent request processing
- Logging & statistics tracking
- Bulk messaging workflows

The project showcases how to build a scalable and flexible messaging system architecture using pure PHP and cURL.

---

✨ Features

- 🔄 Multiple SMS API Integrations
- 🌐 Proxy Rotation System
- ⚡ Concurrent Request Handling (Multi-cURL)
- 📊 Detailed Logging & Statistics
- 📦 Bulk Messaging Support
- 🧠 Smart Proxy Scoring (success/fail tracking)
- 📱 Phone Number Formatting & Normalization
- 🔍 Proxy Testing Endpoint

---

📁 File Structure

Anonymous.php     # Main API file
sms_logs.json     # Log storage (auto-generated)
proxies.txt       # Proxy list (optional external source)

---

⚙️ Installation

1. Clone the repository:

git clone https://github.com/yourusername/anonymous-php-api.git
cd anonymous-php-api

2. Place the file on your server:

- Apache / Nginx + PHP 7.4+
- cURL extension enabled

3. Run via browser or API client:

http://localhost/Anonymous.php

---

📡 API Endpoints

🔹 Send SMS

GET /Anonymous.php?action=send&phone=XXXXXXXXXX&message=Hello&count=1

Parameters:

Parameter| Description
phone| Target phone number
message| Message content
count| Number of attempts
country| Country code (default: 90)

---

🔹 Bulk SMS

POST /Anonymous.php?action=bulk

Body:

numbers=1234567890,9876543210
message=Hello World

---

🔹 Statistics

GET /Anonymous.php?action=stats

Returns:

- Total sent
- Success rate
- Proxy stats
- Recent logs

---

🔹 Proxy List

GET /Anonymous.php?action=proxies

---

🔹 Test Proxy

GET /Anonymous.php?action=test_proxy&proxy=IP:PORT

---

🧠 How It Works

🔄 Proxy System

- Proxies are automatically tested
- Success/fail ratio determines usability
- Dynamic selection for each request

📊 Logging System

Stored in:

sms_logs.json

Tracks:

- Message attempts
- Success/failure counts
- Timestamps

⚡ Request Engine

- Uses "cURL" & "curl_multi"
- Randomized endpoints & user agents
- Delay system to reduce rate limits

---

🔐 Security Notice

This project does NOT include:

- Authentication system
- API key validation
- Rate limiting (per user/IP)

👉 You MUST implement these before production use.

---

🚫 Abuse Policy

The following activities are strictly prohibited:

- Spam messaging
- Harassment or abuse
- Bypassing service provider limitations
- Unauthorized OTP triggering

Any misuse is the sole responsibility of the user.

---

🛠 Recommended Improvements

To make this production-ready:

- ✅ Add API key authentication
- ✅ Implement rate limiting
- ✅ Use a database instead of JSON logs
- ✅ Add request validation & sanitization
- ✅ Integrate queue system (Redis / RabbitMQ)
- ✅ Build a dashboard UI

---

📌 Example Response

{
  "status": "completed",
  "phone": "905XXXXXXXXX",
  "requested": 1,
  "success": 1,
  "fail": 0
}

---

📜 License

This project is licensed under the Apache 2.0 License.

---

👨‍💻 Author

Developed as an experimental project to explore:

- Networking concepts
- Proxy systems
- API design in PHP

---

⭐ Final Note

This project is a technical demonstration, not a ready-to-use commercial service.

Use responsibly.
