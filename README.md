### HAAS (Health As A Service)

OBJECTIVES
1. Login and registration for patients (token based authentication for the API)
2. Appointment booking for patients
3. Payment Integration (Multiple payment integration using both Paystack and Flutterwave)
4. Implement payment webhook to listen to payment confirmation and decline
5. Appointment status should change upon payment confirmation
6. Email at each action steps
7. All payments should be logged and tied to appointments
8. Write unit tests for all features using PHPUnit

## Set up

```
# Clone Project
git clone https://github.com/sambayour/haas

# Change Directory
cd haas

# Env File
rename .env.example file to .env or create .env file and update your database credential

# Generate Application Key
php artisan key:generate

# Run Migrations
php artisan migrate

# Start Project
php artisan serve

# Test
php artisan test

```

## Endpoint

baseurl local `localhost:8000/api/v1`

baseurl staging `https://squid-app-f8wsg.ondigitalocean.app/api/v1`

To login you need to do `baseurl/login` for example

A postman collection named `haas.postman_collection.json` is in the root folder.

example admin login for staging

```
{
    "email":"samuelolubayo@gmail.com",
    "password":"password"
}
```

The RESTFUL API leverage the following classes, methods, and functions

-   **Sanctum**
-   **Eloquent**
-   **Middleware**
-   **Testing**
-   **Mail**
-   **Validation**
-   **Factory**
-   **Migration**
-   **Accessors & Mutators**
-   **Database**
-   **Rate Limiting**
-   **Query Builder**
-   **API Resource**
-   **Relationship**

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
