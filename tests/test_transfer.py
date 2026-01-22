import pytest

from conftest import INVALID_AMOUNTS

@pytest.mark.auth
def test_transfer_auth(client):
    """
    Valid API key must pass Auth middleware.
    Business logic may still reject the request.
    """
    # Send a request
    response = client.get(client.base_url + "/v2/Account/transfer")

    # Must not fail
    assert response.status_code != 401

@pytest.mark.auth
def test_transfer_json(client):
    """
    Endpoint must always return JSON.
    """
    # Send a request
    response = client.get(client.base_url + "/v2/Account/transfer")

    # API contract: JSON respons
    assert response.headers["Content-Type"].startswith("application/json")

    # Parse JSON payload
    data = response.json()

    # Checks the API returned a JSON object
    assert isinstance(data, dict)

@pytest.mark.business
def test_transfer_success(client):
    """
    card to card valid payload from an existing card must succeed.
    """
    # Prepare card-to-card transfer payload
    payload = {
        "from_iban": "FAKE9b354ec1f6f511f0",
        "to_iban": "FAKE9e2dce35f6f511f0",
        "amount": 1.0
    }

    # Send a request
    response = client.post(client.base_url + "/v2/Account/transfer",
                           json=payload)
    
    # Response Must Resource successfully created 201
    # Indicating a validation error. If the assertion fails, include the raw
    assert response.status_code == 201, response.text

    # Parse JSON payload
    data = response.json()

    # Checks the API returned a JSON object
    assert isinstance(data, dict)

@pytest.mark.business
def test_transfer_Insufficient_funds(client):
    """
    transfer from a card with lower balance then the amount must return 422.
    """
    # Prepare card-to-card transfer payload
    payload = {
        "from_iban": "FAKE67856477f46111f0",
        "to_iban": "FAKE794a1a52ea3911f0",
        "amount": 100000.00
    }

    # Send a request
    response = client.post(client.base_url + "/v2/Account/transfer",
                           json=payload)
    
    # Response Must fail 422 Unprocessable Content
    # Indicating a validation error. If the assertion fails, include the raw
    assert response.status_code == 422, response.text

    # Parse JSON payload
    data = response.json()

    # Errors MUST be returned under the `error` key
    assert "error" in data 

    # Assert correct error message
    assert data["error"] == "Insufficient funds"

@pytest.mark.business
def test_transfer_from_iban_not_found(client):
    """
    transfer from non-existent iban must return 404
    """
    # Prepare card-to-card transfer payload
    payload = {
        "from_iban": "NotFound67856477f46111f0",
        "to_iban": "FAKE9e2dce35f6f511f0",
        "amount": 5.00
    }

    # Send a request
    response = client.post(client.base_url + "/v2/Account/transfer",
                           json=payload)
    
    # Response Must fail 404 not found
    assert response.status_code == 404, response.text

    # Parse JSON payload
    data = response.json()

    # Errors MUST be returned under the `error` key
    assert "error" in data
    # Assert correct error message
    assert data["error"] == "from Iban not found"


@pytest.mark.business
def test_transfer_to_iban_not_found(client):
    """
    transfer to non-existent iban must return 404
    """
    # Prepare card-to-card transfer payload
    payload = {
        "from_iban": "FAKE9e2dce35f6f511f0",
        "to_iban": "NotFound67856477f46111f0",
        "amount": 5.00
    }

    # Send a request
    response = client.post(client.base_url + "/v2/Account/transfer",
                           json=payload)
    
    # Response Must fail 404 not found
    assert response.status_code == 404, response.text

    # Parse JSON payload
    data = response.json()

    # Errors MUST be returned under the `error` key
    assert "error" in data
    # Assert correct error message
    assert data["error"] == "reciver Iban not found"

@pytest.mark.business
def test_transfer_same_iban(client):
    """
    transfer to same iban must return 422
    """
    # Prepare card-to-card transfer payload
    payload = {
        "from_iban": "FAKE9e2dce35f6f511f0",
        "to_iban": "FAKE9e2dce35f6f511f0",
        "amount": 5.00
    }

    # Send a request
    response = client.post(client.base_url + "/v2/Account/transfer",
                           json=payload)
    
    # Response Must fail 422 Unprocessable Content
    assert response.status_code == 422, response.text

    # Parse JSON payload
    data = response.json()

    # Errors MUST be returned under the `error` key
    assert "error" in data

    # Assert correct error message
    assert data["error"] == "from_iban and to_iban must be different"

@pytest.mark.business
@pytest.mark.parametrize("amount", [0.00, -1.00])
def test_transfer_invalid_amount(client, amount):
    """
    transfer to with invalid amount must return 422
    """
    # Prepare card-to-card transfer payload
    payload = {
        "from_iban": "FAKE9b354ec1f6f511f0",
        "to_iban": "FAKE9e2dce35f6f511f0",
        "amount": amount
    }

    # Send a request
    response = client.post(client.base_url + "/v2/Account/transfer",
                           json=payload)
    
    # Response Must fail 422 Unprocessable Content
    assert response.status_code == 422, response.text

    # Parse JSON payload
    data = response.json()

    # Errors MUST be returned under the `error` key
    assert "error" in data
    # Assert correct error message
    assert data["error"] == "amount must be grater then 0"

@pytest.mark.business
@pytest.mark.parametrize(
    "payload",[
    {"from_iban": "FAKE9b354ec1f6f511f0", "to_iban": "FAKE9e2dce35f6f511f0"},
    {"from_iban": "FAKE9b354ec1f6f511f0", "amount": 1.00},
    {"to_iban": "FAKE9e2dce35f6f511f0","amount": 1.00}
    ]
)

def test_transfer_missing_required_fields(client, payload):
    """
    transfer to with missing fields must return 422
    """
    # Send a request
    response = client.post(client.base_url + "/v2/Account/transfer",
                           json=payload)
    
    # Response Must fail 422 Unprocessable Content
    assert response.status_code == 422, response.text

    # Errors MUST be returned under the `error` key
    assert "error" in response.json()

