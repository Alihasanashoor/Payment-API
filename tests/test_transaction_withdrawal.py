import pytest



# Import Shared variables for business-rule tests
from conftest import INVALID_AMOUNTS, MISSING_REQUIRED_FIELDS, idempotency_key

@pytest.mark.auth
def test_withdrawal_auth(client):
    """
    Valid API key must pass Auth middleware.
    Business logic may still reject the request.
    """
    # Send a request
    response = client.get(client.base_url + "/v2/transactions/withdraw")
    
    # Must NOT be auth failure
    assert response.status_code != 401

@pytest.mark.auth
def test_withdrawal_json(client):
    """
    Endpoint must always return JSON.
    """
    # Send a request
    response = client.get(client.base_url + "/v2/transactions/withdraw")
    
    # API contract: JSON respons
    assert response.headers["Content-Type"].startswith("application/json")

    # Parse JSON payload
    data = response.json()

    # Checks the API returned a JSON object
    assert isinstance(data, dict)

@pytest.mark.business
def test_withdrawal_success(client, idempotency_key):
    """
    Withdrawing a valid amount from an existing card must succeed.
    """
    # Prepare withdrawal request payload
    payload = {
        "card_id"           : 918,
        "Amount"            : 1.00,
        "product"           : "TEST Withdrawal",
        "idempotency_key"   : f"withdrawal-success-{idempotency_key}"
}
    # Send a request
    response = client.post(client.base_url + "/v2/transactions/withdraw" ,
                           json=payload)
    
    # Response Must Resource successfully created 201
    assert response.status_code == 201

    # Parse JSON payload
    data = response.json()

    # Checks the API returned a JSON object
    assert isinstance(data, dict)


@pytest.mark.business
def test_withdrawal_card_not_found(client):
    """
    Withdrawing from a non-existent card must return 404.
    """
    # Prepare withdrawal request payload
    payload = {
        "card_id"           : 941,
        "Amount"            : 10.00,
        "product"           : "TEST Withdrawal",
        "idempotency_key"   : "withdraw-test-card-not-found"
    }
    # Send a request
    response = client.post(client.base_url + "/v2/transactions/withdraw", 
                           json=payload)
    
    assert response.status_code == 404

    # Parse JSON payload
    data = response.json()

    # Errors MUST be returned under the `error` key
    assert "error" in data

    # Assert correct error message
    assert data["error"] == "Card not found"

@pytest.mark.business
# Run the same test multiple times with different inputs.
@pytest.mark.parametrize("Amount" , INVALID_AMOUNTS)

def test_withdrawal_invalid_amount(client, Amount):
    """
    Withdrawing with invalid amount must return 422.
    """
    # Prepare withdrawal request payload
    payload = {
        "card_id"           : 932,
        "Amount"            : Amount,
        "product"           : "TEST Withdrawal",
        "idempotency_key"   : "withdraw-test-invalid-amount"
    }

    # Send a request
    response = client.post(client.base_url + "/v2/transactions/withdraw",
                           json= payload)
    
    # Response Must fail 422 Unprocessable Content
    assert response.status_code == 422

    # Errors MUST be returned under the `error` key
    assert "error" in response.json()

@pytest.mark.business
# Run the same test multiple times with different inputs.
@pytest.mark.parametrize(
    "payload",MISSING_REQUIRED_FIELDS)

def test_withdrawal_missing_required_fields(client, payload):
    """
    Withdrawing with missing required fields must return 422.
    """
    # Send a request
    response = client.post(client.base_url + "/v2/transactions/withdraw",
                           json=payload)
    
    # Response Must fail 422 Unprocessable Content
    assert response.status_code == 422
    # Errors MUST be returned under the `error` key
    assert "error" in response.json()

@pytest.mark.business
def test_withdrawal_Insufficient_funds(client):
    """
    Withdrawing from a card with lower balance then the amount must return 422.
    """
    # Prepare withdrawal request payload
    payload = {
        "card_id": 909,
        "Amount": 1000,
        "product": "ATM Withdrawal",
        "idempotency_key": "withdraw-insufficient-funds"
    }
    # Send a request
    response = client.post(client.base_url + "/v2/transactions/withdraw",
                           json=payload)
    # Response Must fail 422 Unprocessable Content
    assert response.status_code == 422

    # Parse JSON payload
    data = response.json()
    # Errors MUST be returned under the `error` key
    assert "error" in data
    # Assert correct error message
    assert data["error"] == "Insufficient funds"


@pytest.mark.slow
def test_withdrawal_idempotency(client, idempotency_key):
    """
    Verifies idempotent behavior for withdrawal requests.

    Repeating the same request with the same idempotency key
    must return the same HTTP status and response payload,
    without executing the transaction multiple times.
    """
    # Generate idempotency_key 
    idem = idempotency_key
    # Prepare withdrawal request payload
    payload = {
        "card_id": 932 ,
        "Amount": 1.00,
        "product": "ATM Withdrawal",
        "idempotency_key": f"withdrawal-{idem}"
    }

    # Send frist request
    response = client.post(client.base_url + "/v2/transactions/withdraw", 
                           json=payload)
    
    # Replay the exact same request (same idempotency key + payload)
    response_2 = client.post(client.base_url + "/v2/transactions/withdraw",
                             json=payload)
    # First request must succeed and create the resource
    assert response.status_code == 201
    
    # Second request reusing the same idempotency key with a different payload
    # must be rejected to prevent duplicate or conflicting operations
    assert response_2.status_code == 409

    # Parse JSON payload
    data = response_2.json()
    
    # Checks the API returned a JSON object
    assert isinstance(data, dict)

@pytest.mark.slow
def test_withdraw_idempotency_different_payload_same_idempotency_key(client, idempotency_key):
    
    """
    Ensures idempotency keys cannot be reused with different payloads.

    The first request must be processed successfully.
    Any subsequent request using the same idempotency key
    but a different payload must be rejected to prevent
    conflicting or duplicate transactions.
    """
    # Generate idempotency_key to be used in both payloads
    idem = idempotency_key
    Frist_payload = {
        "card_id": 932 ,
        "Amount": 1.00,
        "product": "test-Withdrawal-new-idempotency_key",
        "idempotency_key": f"test-{idem}"
    }

    Second_payload = {
        "card_id": 932 ,
        "Amount": 50.00,
        "product": "test-Withdrawal-same-idempotency_key",
        "idempotency_key": f"test-{idem}"
    }

    # Send frist request
    response = client.post(client.base_url + "/v2/transactions/withdraw",
                           json = Frist_payload)
    
    # Send second request
    response_2 = client.post(client.base_url + "/v2/transactions/withdraw",
                             json = Second_payload)
    
    # First request must succeed and create the resource
    assert response.status_code == 201

    # Second request reusing the same idempotency key with a different payload
    # must be rejected to prevent duplicate or conflicting operations
    assert response_2.status_code == 409

    # Parse JSON payload
    data = response_2.json()

    # Ensure the API adheres to the JSON error contract
    # Checks the API returned a JSON object
    assert isinstance(data, dict)
