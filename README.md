# 💳 Payment API

A secure, transaction-based Payment API implemented in pure PHP, designed to demonstrate backend engineering best practices such as transactional integrity, idempotency, and clean architecture.

This project simulates real-world payment workflows including account creation, deposits, withdrawals, and card-to-card transfers, while enforcing strict business rules at both the application and database levels.

The API is designed to be consumed by external systems (e.g., a web application for student payments), enabling simplified and secure payment flows while maintaining strong data consistency and validation guarantees.

---

## 📌 Project Goals

- Develop a strong understanding of how backend systems operate end-to-end
- Build backend functionality from first principles using pure PHP, including manual JSON handling and request/response processing
- Design APIs that are secure, testable, and production-oriented
- Practice automated testing, CI workflows, and environment isolation
- Implement a realistic payment backend with a focus on data integrity and business rules

---

## ✨ Features
- Secure API key authentication with hashed keys stored and verified in the database
- Card-based accounts
- Fund deposits and withdrawals
- Card-to-card transfers
- Idempotent requests (safe retries)
- MySQL transactional integrity
- Business rules enforced via SQL triggers
- Clear error handling (validation vs business vs system errors)
- Automated tests using pytest
- CI-ready with GitHub Actions

---

## 🧰 Tech Stack
- PHP (no framework)
- MySQL (relational database with transactions and triggers)
- Python (pytest for automated testing)
- GitHub Actions (CI for automated test execution)

---

## 🏗️ Architecture Overview

The API follows a layered architecture with clear separation of responsibilities:

Client
↓
HTTP Endpoint (Controller)
↓
Request Validation
↓
Service Layer (Business Logic)
↓
Repository Layer (SQL only)
↓
MySQL (Transactions + Triggers)

---

## 🧩 Design Principles
- No business logic in controllers
- Services coordinate workflows
- Repositories contain SQL only
- Database enforces critical invariants
- Errors are classified and normalized

---

## 🔐 Authentication

All endpoints require an API key sent via HTTP header:

X-API-Key: <your_api_key>


Security notes:
- API keys are **hashed** before storage
- Raw keys are never logged or committed
- Missing or invalid keys return `401 Unauthorized`

---

## 📡 API Endpoints

### Create Account

POST v2/Account/create_Account

Creates a new payment account and automatically generates a card associated with the account.

**Example Request**
```json
{
    "Name": "Ali Hasan",
    "Phone_Number": "58567265467",
    "email": "romanuso@hotmail.com",
    "Link_ID": "", 
    "balance": 20.00
}
```
Request Fields

    - name (string)
        Account holder’s full name

    - phone_number (string)
        Contact phone number

    - email (string)
        Account email address

    - link_id (string, optional)
        External identifier (e.g. student ID)

    - balance (number)
        Initial account balance


Behavior
- validate required fileds
- Checks if the phone number already exist 
    If yes, returns 409 Conflict
- Checks if the email already exists
    If yes, returns 409 Conflict 
- If link_id is provided:
    Checks if the link ID already exists 
        check if link id exisits
            If yes, returns 409 Conflict
- Creates a new account
- Generates a unique card linked to the account
- Initializes the account with the provided balance
- Validates input and rejects invalid data
- Enforces uniqueness and integrity rules at the database level

Notes
- Responses are returned in a normalized JSON format
- Validation, business, and system errors are clearly separated
- Card number and IBAN are generated at the database level


### Deposit

/v2/transactions/deposit

Adds funds to an account such as refund

**Example Request**
```json
{
  "card_number": "6397554063608454",
  "Amount": 20.00,
  "idempotency_key": "deposit-001"
}
```

Request Fields

    - card_number (string)
        Card number used to identify the target account
    
    - Amount (number)
       Amount to be added to the account

    - idempotency_key (string)
        Key used to prevent duplicate deposit requests

Behavior

- validate required fileds
- Validates that the transfer amount is positive
- Checks if the card number exists
    If not found, returns 404 Not Found 
- Hashes the request payload and checks it against stored idempotency records
    If the idempotency key exists with a different payload, returns 409 Conflict
    If the idempotency key exists with the same payload, returns the original transaction
- Adds funds to the account within a database transaction

Notes
- Responses are returned in a normalized JSON format
- Validation, business, and system errors are clearly separated



### withdraw

POST /v2/transactions/withdraw

Adds funds to an account such as refund

**Example Request**
```json
{
  "card_number": "6397554063608454",
  "Amount": 20.00,
  "product": "a note book",
  "idempotency_key": "note-book-6397554063608454"
}
```

Request Fields

    - card_number (string)
        Card number used to identify the target account
    
    - Amount (number)
       Amount to be added to the account

    - product (string)
        Description of the product or service being purchased

    - idempotency_key (string)
        Key used to prevent duplicate deposit requests

Behavior

- validate required fileds
- Validates that the transfer amount is positive
- Checks if the card number exists
    If not found, returns 404 Not Found 
- Hashes the request payload and checks it against stored idempotency records
    If the idempotency key exists with a different payload, returns 409 Conflict
    If the idempotency key exists with the same payload, returns the original transaction
- Verifies sufficient account balance
    If insufficient funds, returns 422 Unprocessable Entity
- Deducts funds within a database transaction

Notes
- Responses are returned in a normalized JSON format
- Validation, business, and system errors are clearly separated
- Balance integrity is enforced at both the application and database levels


### resolve

GET /v1/students/resolve

Resolves a student’s payment account and associated card using an external identifier (link_id)

**Example Request**

/v1/students/resolve?link_id=930

Request Fields

    - link_id
        External identifier used to resolve the student’s payment account

Behavior

- Validates that the link_id query parameter is provided 
    If missing or invalid, returns 400 Bad Request
- Fetches the student’s account and first associated card via the service layer
- If no account is found, returns 404 Not Found
- If an account exists but no card is associated:
    Returns 404 Not Found (or optionally triggers card creation, depending on configuration)


Notes
- Responses are returned in a normalized JSON format
- Validation, business, and system errors are clearly separated
- This endpoint is intended for account resolution, not fund movement



### transfer

/v2/Account/transfer

transfer funds beteen cards

**Example Request**
```json
{
    "from_iban": "FAKEec96cd4efa0411f0",
    "to_iban": "FAKEa0f0f6d9fa0711f0",
    "amount": 1.0
}
```

Request Fields

    - from_iban
        iban of the sender
    - to_iban
        iban of the recipient’s card
    - amount
        Amount to be transferred

Behavior

- validate required fileds
- Ensures sender and recipient IBANs are different
    If identical, returns 422 Unprocessable Entity
- Validates that the transfer amount is positive 
- Checks if the sender IBAN exists 
    If not found, returns 404 Not Found
- Checks if the recipient IBAN exists
    If not found, returns 404 Not Found
- Ensures balance consistency using database-level constraints and triggers

Notes
- Responses are returned in a normalized JSON format
- Validation, business, and system errors are clearly separated
- Partial transfers are prevented through transactional guarantees

---


## 🧪 Testing

Framework: pytest
Database: Dedicated test schema (isolated from development data)

What’s tested:

- Successful deposits, withdrawals, and transfers

- Validation failures

- Insufficient funds

- Idempotency behavior

- Authentication errors

Run tests:

pytest

## ⚙️ Environment Variables

Example .env file (not committed):

DB_HOST=127.0.0.1

DB_PORT=3306

DB_NAME=payment_systemdb

DB_USER=payuser

DB_PASS=********

API_KEY=********


## 🔁 Idempotency

Each transactional request requires an idempotency_key.

- Duplicate requests with the same key are safely ignored
- Prevents double-charging on retries
- Enforced at the database/repository level

## ⚠️ Error Handling
The API uses consistent and predictable HTTP status codes to clearly distinguish between different classes of errors.

HTTP Status Codes
- 400 Bad Request
    Validation errors (missing or malformed input)

- 401 Unauthorized
    Missing or invalid API key

- 422 Unprocessable Entity
    Business rule violations (e.g. insufficient funds, invalid transfers)

- 500 Internal Server Error
    Unexpected system errors

Error Safety

- Database errors and internal exceptions are never leaked to clients

- Internal failures are mapped to generic error responses

- Detailed error information is logged server-side only


## 🚀 CI (Continuous Integration)

- GitHub Actions workflow

- MySQL service container

- Schema loaded automatically

- pytest executed on each push

- Ensures:

    - Reproducible test runs

    - No environment drift

    - Safe refactoring

## 🐳 Docker

The API is fully Dockerized to provide a consistent and reproducible development environment.

Services

    PHP 8.x API container

    MySQL database container

    Automatic database schema and trigger initialization

Running with Docker

    docker compose up --build


The API will be available at:

    http://localhost:8000

Environment Notes

- When running with Docker, the API connects to the database via the Docker service name (DB_HOST=db)

- When running locally, the API can connect to a local MySQL instance using DB_HOST=127.0.0.1

- The same codebase works in both environments without modification