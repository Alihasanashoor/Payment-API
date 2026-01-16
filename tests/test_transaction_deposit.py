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
    # Must NOT be auth failure
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
def test_deposit_success(client):
    """
    Withdrawing a valid amount from an existing card must succeed.
    """
    # Prepare withdrawal request payload
    payload = {
        "card_id"           : 918,
        "Amount"            : 5.00,
        "product"           : "TEST deposit",
        "idempotency_key"   : "deposit-test2"
    }   
    # Send a request
    response = client.post(client.base_url + "/v2/transactions/deposit",
                           json=payload)
    # Response Must Resource successfully created 201
    assert response.status_code == 201

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
    assert response.status_code == 404

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
        "card_id"           : 918,
        "Amount"            : Amount,
        "product"           : "TEST Withdrawal",
        "idempotency_key"   : "withdraw-test4-"
    }
    # Send a request
    response = client.post(client.base_url + "/v2/transactions/deposit",
                           json=payload)
    
    # Response Must fail 422 Unprocessable Content
    assert response.status_code == 422

    # Errors MUST be returned under the `error` key
    assert "error" in response.json()

