import pytest



@pytest.mark.auth
def test_create_account_auth(client):
    """
    Valid API key must pass Auth middleware.
    Business logic may still reject the request.
    """
    # Send a request
    response = client.get(client.base_url + "/v2/Account/create_Account")
    # Send a request
    assert response.status_code != 401

@pytest.mark.auth
def test_create_account_json(client):
    """
    Endpoint must always return JSON.
    """
    # Send a request
    response = client.get(client.base_url + "/v2/Account/create_Account")

    # API contract: JSON respons
    assert response.headers["Content-Type"].startswith("application/json")

    # Parse JSON payload
    data = response.json()

    # Checks the API returned a JSON object
    assert isinstance(data, dict)

@pytest.mark.business
def test_create_account_success(client, unique_email, unique_phone):
    """
    creating account with valid payload.
    """
    # Prepare withdrawal request payload
    payload = {
        "Name": "Test User",
        "Phone_Number": unique_phone,
        "email": unique_email,
        "Link_ID": "",
        "balance": 100.0
    }
    # Send a request
    responce = client.post(client.base_url + "/v2/Account/create_Account",
                           json=payload)
    
    # Response Must Resource successfully created 201
    assert responce.status_code == 201

    # Parse JSON payload
    data = responce.json()

    # Checks the API returned a JSON object
    assert isinstance(data, dict)

@pytest.mark.business
def test_create_account_success_with_Link_ID(client):
    #temp
    pass