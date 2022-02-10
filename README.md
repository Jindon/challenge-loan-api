
# Aspire Loan API (Code Challenge)

It is an app that allows authenticated users to go through a loan application.
All the loans will be assumed to have a “weekly” repayment frequency.

After the loan is approved, the user can submit the weekly loan repayments.
A simplified repay functionality, which doesn't check if the dates are
correct but will just set the weekly amount to be repaid.




## Tech

- Laravel 9, PHP 8.1 (uses enums)

- Auth scafold using [Laravel Breeze API](https://laravel.com/docs/9.x/starter-kits#breeze-and-next)

- Uses [Spatie Laravel Query Builder](https://spatie.be/docs/laravel-query-builder/v5/introduction) for easy API queries (filters, includes, etc.)


## Installation

The app can be installed and served using the `initial_setup.bash` script file.
Or follow the steps below to install manually:

After cloning the project, copy the `.env.example` file and rename it to `.env`.

Update the `DB_DATABASE`, `DB_USERNAME` and `DB_PASSWORD` values to your local database setup.

Run the following commands from the project directory:

```bash
  composer install
  --------------
  php artisan key:generate
  --------------
  php artisan migrate
  --------------
  php artisan serve
```



## Running Tests

To run tests, run the following command

```bash
  php artisan test
```


## Documentation

The API documentation is provided as a Postman Collection and
can be found in the project root. `Aspire Loan API.postman_collection.json`.

Import the collection into your Postman client and setup an environment with the following
key value pairs

```
api_url : http://127.0.0.1:8000

frontend_host : localhost:3000

xsrf-cookie

------------------
#xsrf-cookie will be authomatically added when you run the register request
# Also the api_url and frontend_host corresponds to the APP_URL
and FRONTEND_URL values in .env file.
```

_**The app uses sanctum cookie based authentication, so these configs are important_
## Features

- User Login, Register
- User Loan Application
- Console command to approve loan. `php artisan loan:approve {loan_id}`
- Loan Payments: Single or All at once


## Instructions on testing with Postman

- Register a user first. This will automatically login the user
- To login, you need to logout if already logged in
- Create a loan using the Create Loan Application request
- To approve the loan run `php artisan loan:approve {loan_id}`. This will generate all the Payments based on the term of the loan
- To make a payment use the Make Payment request. Make sure to provide the correct amount and currency_code.
- To pay all pending payments and close the loan, use the Make Full Payment request.

_**Note: Amounts are stored in the lowest denomination of the currency.
But API response will convert it to the actual amount (amount / 100)
So you need to convert the amount in the API response to the lowest denomination
(amount * 100).**_


## More Information

To know more about the choices I have made while building this project and what could be improved upon
view this notion document below.

[Aspire Code Challenge Documentation](https://lapis-talos-abb.notion.site/Aspire-Code-Challenge-Documentation-7b3be8d37fbb44fc880a876cb228c148)

