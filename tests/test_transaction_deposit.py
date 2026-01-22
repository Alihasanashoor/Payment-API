import pytest

# Import Shared variables for business-rule tests
from conftest import INVALID_AMOUNTS, MISSING_REQUIRED_FIELDS

@pytest.mark.auth
def test_deposit_auth(client):
    """
    Valid API key must pass Auth middleware.
    Business logic may still reject the request.
    """
    # Send a request
    response = client.get(client.base_url + "/v2/transactions/deposit")
    
    # Must not fail
    assert response.status_code != 401

@pytest.mark.auth
def test_deposit_json(client):
    """
    Endpoint must always return JSON.
    """
    # Send a request
    response = client.get(client.base_url + "/v2/transactions/deposit")

    # API contract: JSON respons
    assert response.headers["Content-Type"].startswith("application/json")

    # Parse JSON payload
    data = response.json()

    # Checks the API returned a JSON object
    assert isinstance(data, dict)

@pytest.mark.business
def test_deposit_success(client, idempotency_key):
    """
    deposit a valid payload from an existing card must succeed.
    """
    # Prepare withdrawal request payload
    payload = {
        "card_id"           : 948,
        "Amount"            : 5.00,
        "product"           : "TEST deposit",
        "idempotency_key"   : f"deposit-success-{idempotency_key}"
    }   
    # Send a request
    response = client.post(client.base_url + "/v2/transactions/deposit",
                           json=payload)
    # Response Must Resource successfully created 201
    # Indicating a validation error. If the assertion fails, include the raw
    assert response.status_code == 201, response.text

    # Parse JSON payload
    data = response.json()
    # Checks the API returned a JSON object
    assert isinstance(data, dict)

@pytest.mark.business
def test_deposit_not_found(client):
    """
    depoaiting from a non-existent card must return 404.
    """
    # Prepare withdrawal request payload
    payload = {
        "card_id"           : 9999,
        "Amount"            : 5.00,
        "product"           : "TEST deposit",
        "idempotency_key"   : "deposit-test-not-found"
    }
    # Send a request
    response = client.post(client.base_url + "/v2/transactions/deposit",
                           json=payload)
    
    # Response Must fail 404 not found
    # Indicating a validation error. If the assertion fails, include the raw
    assert response.status_code == 404, response.text

    # Parse JSON payload
    data = response.json()

    # Errors MUST be returned under the `error` key
    assert "error" in data

    # Assert correct error message
    assert data["error"] == "Card not found"

@pytest.mark.business
# Run the same test multiple times with different inputs.
@pytest.mark.parametrize("Amount", INVALID_AMOUNTS )

def test_deposit_invalid_amount(client,Amount):
    """
    depositing with invalid amount must return 422.
    """
    # Prepare deposit request payload
    payload = {
        "card_id"           : 948,
        "Amount"            : Amount,
        "product"           : "TEST Withdrawal",
        "idempotency_key"   : "withdraw-test4-"
    }
    # Send a request
    response = client.post(client.base_url + "/v2/transactions/deposit",
                           json=payload)
    
    # Response Must fail 422 Unprocessable Content
    # Indicating a validation error. If the assertion fails, include the raw
    assert response.status_code == 422, response.text

    # Errors MUST be returned under the `error` key
    assert "error" in response.json()
@pytest.mark.business
@pytest.mark.parametrize("amount", [0.00, -1.00])
def test_deposit_Insufficient_funds(client, amount):
    """
    depositing a card with lower balance then the amount must return 422.
    """
    # Prepare withdrawal request payload
    payload = {
        "card_id": 948,
        "Amount": amount,
        "product": "ATM Withdrawal",
        "idempotency_key": "withdraw-insufficient-funds"
    }
    # Send a request
    response = client.post(client.base_url + "/v2/transactions/deposit",
                          json=payload)
    
    # Response Must fail 422 Unprocessable Content
    # Indicating a validation error. If the assertion fails, include the raw
    assert response.status_code == 422, response.text
    # Parse JSON payload
    data = response.json()
    # Errors MUST be returned under the `error` key
    assert "error" in data
    # Assert correct error message
    assert data["error"] == "amount must be grater then 0"


@pytest.mark.business
# Run the same test multiple times with different inputs.
@pytest.mark.parametrize("payload", MISSING_REQUIRED_FIELDS)

def test_deposit_missing_required_fields(client, payload):
    """
    depositing with missing required fields must return 422.
    """
    # Send a request
    response = client.post(client.base_url + "/v2/transactions/deposit",
                           json=payload)
    # Response Must fail 422 Unprocessable Content
    # Indicating a validation error. If the assertion fails, include the raw
    assert response.status_code == 422, response.text
    # Errors MUST be returned under the `error` key
    assert "error" in response.json()


@pytest.mark.slow
def test_deposit_idempotency(client, idempotency_key):
    """
    Verifies idempotent behavior for deposit requests.

    Repeating the same request with the same idempotency key
    must return the same HTTP status and response payload,
    without executing the transaction multiple times.
    """
    idem = idempotency_key
    payload = {
        "card_id": 948 ,
        "Amount": 1.00,
        "product": "Deposit test",
        "idempotency_key": f"deposit-{idem}"
    }

    # Send frist request
    response = client.post(client.base_url + "/v2/transactions/deposit", 
                           json=payload)
    
    # Replay the exact same request (same idempotency key + payload)
    response_2 = client.post(client.base_url + "/v2/transactions/deposit",
                             json=payload)

    # First request must succeed and create the resource
    # Indicating a validation error. If the assertion fails, include the raw
    assert response.status_code == 201, response.text
    
    # Second request reusing the same idempotency key with a different payload
    # must be rejected to prevent duplicate or conflicting operations
    # Indicating a validation error. If the assertion fails, include the raw
    assert response_2.status_code == 409, response_2.text

    # Parse JSON payload
    data = response_2.json()
    
    # Checks the API returned a JSON object
    assert isinstance(data, dict)

@pytest.mark.slow
def test_deposit_idempotency_different_payload_same_idempotency_key(client, idempotency_key):
    """
    Ensures idempotency keys cannot be reused with different payloads.

    The first request must be processed successfully.
    Any subsequent request using the same idempotency key
    but a different payload must be rejected to prevent
    conflicting or duplicate transactions.
    """
    idem = idempotency_key
    Frist_payload = {
        "card_id": 948 ,
        "Amount": 1.00,
        "product": "test-Deposit-new-idempotency_key",
        "idempotency_key": idem
    }

    Second_payload = {
        "card_id": 948 ,
        "Amount": 50.00,
        "product": "test-Deposit-same-idempotency_key",
        "idempotency_key": idem
    }
    
    # Send frist request
    response = client.post(client.base_url + "/v2/transactions/deposit",
                           json = Frist_payload)
    
    # Send second request
    response_2 = client.post(client.base_url + "/v2/transactions/deposit",
                             json = Second_payload)
    
    # First request must succeed and create the resource
    # Indicating a validation error. If the assertion fails, include the raw
    assert response.status_code == 201, response.text

    # Second request reusing the same idempotency key with a different payload
    # must be rejected to prevent duplicate or conflicting operations
    # Indicating a validation error. If the assertion fails, include the raw
    assert response_2.status_code == 409, response_2.text

    # Parse JSON payload
    data = response_2.json()

    # Ensure the API adheres to the JSON error contract
    # Checks the API returned a JSON object
    assert isinstance(data, dict)

