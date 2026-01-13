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
        "Amount"            : 10.00,
        "product"           : "TEST Withdrawal",
        "idempotency_key"   : "withdraw-test-918(2)"
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
        "card_id"           : 99999999,
        "Amount"            : 10.00,
        "product"           : "TEST Withdrawal",
        "idempotency_key"   : "withdraw-test"
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
def test_withdraw_invalid_amount(client):
    
    # Prepare withdrawal request payload
    paylaod = {
        "card_id"           : 918,
        "Amount"            : -5.00,
        "product"           : "TEST Withdrawal",
        "idempotency_key"   : "withdraw-test-918-invalid"
    }

    # Send a request
    response = client.post(client.base_url + "/v2/transactions/withdraw",
                           json=paylaod)
    
    # Response Must fail 422 Unprocessable Content
    assert response.status_code == 422

    # Parse JSON payload
    data = response.json()

    # Errors MUST be returned under the `error` key
    assert "error" in data

    # Assert correct error message
    assert data["error"] == "amount must be grater then 0"
