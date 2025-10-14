
# JWT REST Booking API

A lightweight PHP REST API for hotel booking management featuring **JWT authentication**, **booking validation**, and **PDF invoice generation** using **DomPDF**. Built for mobile app with a clean **Service–Repository–Controller architecture**.

---

## 🚀 Features

- 🔐 JWT Authentication (Register/Login)  
- 🏨 Hotel & Booking Management  
- 💾 Repository Pattern for clean data access  
- 🧠 Service Layer for business logic (total price, duplicate booking prevention)  
- 🧾 PDF Invoice Generation via DomPDF  
- 🧱 Custom Middleware for Bearer token validation  
- 🧩 Modular folder structure  

---

## ⚙️ Setup Instructions

### 1️⃣ Clone the repository

```bash
git clone https://github.com/Ranjit2/jwt-rest-booking-api.git
cd jwt-rest-booking-api
composer install
```

### 2️⃣ Configure Database

Edit `config/config.php`:

```php
return [
    'db' => [
        'host' => '127.0.0.1',
        'dbname' => 'booking_app',
        'user' => 'root',
        'pass' => ''
    ],
    'jwt_secret' => 'your_secret_key_here'
];
```
## Run Tests
To run the Booking test, simply execute:
```bash
vendor/bin/phpunit tests/BookingTest.php 
```
---

## 📡 API Endpoints

### 🔑 Authentication

| Method | Endpoint       | Description                |
|--------|----------------|----------------------------|
| POST   | /api/register  | Register a new user        |
| POST   | /api/login     | Login and receive JWT token|

### 🏨 Bookings

| Method | Endpoint        | Description                         |
|--------|-----------------|-------------------------------------|
| POST   | /api/bookings   | Create new booking (requires Bearer token)|

### 🧾 Invoices

| Method | Endpoint                                                 | Description                                |
|--------|----------------------------------------------------------|--------------------------------------------|
| GET    | /api/download_invoice?booking_id=1    | Download booking invoice PDF (Bearer token required)|

---

## 🧪 Example Postman Payloads

**Register**

```json
{
    "name": "Ranjeet Karki",
    "email": "ranjeet@example.com",
    "password": "secret123"
}
```

**Login**

```json
{
    "email": "ranjeet@example.com",
    "password": "secret123"
}
```

**Create Booking**

```json
{
    "hotel_id": 2,
    "room_type": "Deluxe",
    "guests": 2,
    "check_in": "2025-10-15",
    "check_out": "2025-10-18"
}
```

> Use the JWT token from login as:

```
Authorization: Bearer <your_jwt_token>
```

---

## 🧩 Tech Stack

- PHP 8.1+  
- MySQL  
- Composer  
- Firebase JWT  
- DomPDF  
- PHPUnit
---

## 📄 License

MIT License — free to use, modify, and distribute.

---

## 💡 Author

**Ranjeet Karki**  
Full Stack PHP Developer — Passionate about clean architecture, scalable backend systems, and modern web applications 
🌐 [GitHub Profile](https://github.com/Ranjit2)
