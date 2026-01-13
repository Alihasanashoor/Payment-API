import pytest

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
def test_withdrawal_success(client):
    """
    Withdrawing a valid amount from an existing card must succeed.
    """
    # Prepare withdrawal request payload
    payload = {
        "card_id"           : 918,
        "Amount"            : 1.00,
        "product"           : "TEST Withdrawal",
        "idempotency_key"   : "withdraw-test4"
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
        "idempotency_key"   : "withdraw-test(2)"
    }
    # Send a request
    response = client.post(client.base_url + "/v2/transactions/withdraw", 
                           json=payload)
    # Response Must fail 404 not found
    assert response.status_code == 404

    # Parse JSON payload
    data = response.json()

    # Errors MUST be returned under the `error` key
    assert "error" in data

    # Assert correct error message
    assert data["error"] == "Card not found"

@pytest.mark.business
# Run the same test multiple times with different inputs.
@pytest.mark.parametrize("Amount" , [0,-1.00,-10.5])

def test_withdraw_invalid_amount(client, Amount):
    """
    Withdrawing with invalid amount must return 422.
    """
    # Prepare withdrawal request payload
    payload = {
        "card_id"           : 932,
        "Amount"            : Amount,
        "product"           : "TEST Withdrawal",
        "idempotency_key"   : "withdraw-test(2)"
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
    "payload",[
        {"Amount":1.00, "product": "TEST", "idempotency_key": "TEST1"},
        {"card_id": 932, "product": "TEST", "idempotency_key": "TEST2"},
        {"card_id": 932, "amount": 1.00, "idempotency_key": "TEST3"},
        {"card_id": 932, "amount": 1.00, "product": "TEST4"},
        ])

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

@pytest.mark.slow
def test_withdrawal_idempotency(client):
    """
    Verifies idempotent behavior for withdrawal requests.

    Repeating the same request with the same idempotency key
    must return the same HTTP status and response payload,
    without executing the transaction multiple times.
    """
    # Prepare withdrawal request payload
    payload = {
        "card_id": 932 ,
        "amount": 5.00,
        "product": "ATM Withdrawal",
        "idempotency_key": "withdraw-dub-idempotent-"
    }

    # Send frist request
    response = client.post(client.base_url + "/v2/transactions/withdraw", 
                           json=payload)
    
    # Replay the exact same request (same idempotency key + payload)
    response_2 = client.post(client.base_url + "/v2/transactions/withdraw",
                             json=payload)
    
    # Idempotency check:
    # Repeated requests with the same idempotency key must return
    # the same status code and response payload
    assert response.status_code == response_2.status_code
    assert response.json() == response_2.json()

@pytest.mark.slow
def test_withdraw_idempotency_different_payload_same_idempotency_key(client):
    """
    Ensures idempotency keys cannot be reused with different payloads.

    The first request must be processed successfully.
    Any subsequent request using the same idempotency key
    but a different payload must be rejected to prevent
    conflicting or duplicate transactions.
    """
    Frist_payload = {
        "card_id": 932 ,
        "Amount": 1.00,
        "product": "test-Withdrawal-new-idempotency_key",
        "idempotency_key": "test-idempotency_key(9)"
    }

    Second_payload = {
        "card_id": 932 ,
        "Amount": 50.00,
        "product": "test-Withdrawal-same-idempotency_key",
        "idempotency_key": "test-idempotency_key(9)"
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
