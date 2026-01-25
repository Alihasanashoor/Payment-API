import pytest
import random


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
    creating account with valid payload and no link_id
    """
    # Prepare create account request payload
    payload = {
        "Name": "Test User",
        "Phone_Number": unique_phone,
        "email": unique_email,
        "Link_ID": "",
        "balance": 20.00
    }
    # Send a request
    response = client.post(client.base_url + "/v2/Account/create_Account",
                           json=payload)
    
    # Response Must Resource successfully created 201
    # Indicating a validation error. If the assertion fails, include the raw
    assert response.status_code == 201, response.text

    # Parse JSON payload
    data = response.json()

    # Checks the API returned a JSON object
    assert isinstance(data, dict)

@pytest.mark.business
def test_create_account_success_with_Link_ID(client, unique_email, unique_phone):
    """
    creating account with valid payload and link_id
    """
    
    # Prepare create account request payload
    payload = {
        "Name": "Test User",
        "Phone_Number": unique_phone,
        "email": unique_email,
        "Link_ID": f"9{random.randint(10, 99)}",
        "balance": 100.0
    }

    # Send a request
    response = client.post(client.base_url + "/v2/Account/create_Account",
                           json=payload)
     
    # Response Must Resource successfully created 201
    # Indicating a validation error. If the assertion fails, include the raw
    assert response.status_code == 201, response.text

    # Parse JSON payload
    data = response.json()

    # Checks the API returned a JSON object
    assert isinstance(data, dict)

@pytest.mark.business
@pytest.mark.parametrize("link_id",[
    "12",   # too short
    "1a3",  # letters
    "abc",  # letters only
    "12!",  # special chars
])
def test_create_account_invalid_link_id(client, unique_email,unique_phone, link_id):
    """
    creating account wiht invalid link_id must return 422.
    """
    # Prepare create account request payload
    payload = {
        "Name": "Test User",
        "Phone_Number": unique_phone,
        "email": unique_email,
        "Link_ID": link_id,
        "balance": 100.0
    }

    # Send a request
    response = client.post(client.base_url + "/v2/Account/create_Account",
                           json= payload)
    
    # Response Must fail 422 Unprocessable Content
    # Indicating a validation error. If the assertion fails, include the raw
    assert response.status_code == 422, response.text


@pytest.mark.business
@pytest.mark.parametrize("email", [
    "test",
    "test@",
    "test.com",
    "test@com",
    "@example.com",
])

def test_create_account_invalid_email(client, unique_phone, email):
    """
    creating account wiht invalid email must return 422.
    """
    # Prepare create account request payload
    payload = {
        "Name": "Test User",
        "Phone_Number": unique_phone,
        "email": email,
        "Link_ID": "",
        "balance": 20.0
    }
    # Send a request
    response = client.post(client.base_url + "/v2/Account/create_Account",
                           json=payload)
    
    # Response Must fail 422 Unprocessable Content
    # Indicating a validation error. If the assertion fails, include the raw
    assert response.status_code == 422, response.text


@pytest.mark.business
@pytest.mark.parametrize("phone_number", [
    "123sbc",
    "123-456",
    "12 345",
    "abcd",
])
def test_create_account_invalid_phone(client, unique_email, phone_number):
    """
    creating account wiht invalid phone number must return 422.
    """
    # Prepare create account request payload
    payload = {
        "Name": "Test User",
        "Phone_Number": phone_number,
        "email": unique_email,
        "Link_ID": "",
        "balance": 20.0
    }

    # Send a request
    response = client.post(client.base_url + "/v2/Account/create_Account",
                           json=payload)
    
    # Response Must fail 422 Unprocessable Content
    # Indicating a validation error. If the assertion fails, include the raw
    assert response.status_code == 422, response.text


@pytest.mark.business
@pytest.mark.parametrize("Balance", [
    0.00,
    -15.00,
    19.00,
])
def test_create_account_invalid_balance(client, unique_email, unique_phone, Balance):
    """
    creating account wiht invalid balance must return 422.
    """
    # Prepare create account request payload
    payload = {
        "Name": "Test User",
        "Phone_Number": unique_phone,
        "email": unique_email,
        "Link_ID": "",
        "balance": Balance
    }
    # Send a request
    response = client.post(client.base_url + "/v2/Account/create_Account",
                           json=payload)
    
    # Response Must fail 422 Unprocessable Content
    # Indicating a validation error. If the assertion fails, include the raw
    assert response.status_code == 422, response.text


@pytest.mark.business
@pytest.mark.parametrize("missing_field", [
    "Name",
    "Phone_Number",
    "email",
    "balance"
])
def test_create_account_missing_required_field(client, missing_field,unique_email,unique_phone):
    """
    creating account wiht missing required fields must return 422.
    """
    # Prepare create account request payload
    payload = {
        "Name": "Test User",
        "Phone_Number": unique_phone,
        "email": unique_email,
        "Link_ID": "",
        "balance": 20.00
    }
    # Remove one key from the dictionary called payload.
    payload.pop(missing_field, None)
    
    # Send a request
    response = client.post(client.base_url + "/v2/Account/create_Account",
                           json=payload)
    
    # Response Must fail 422 Unprocessable Content
    # Indicating a validation error. If the assertion fails, include the raw
    assert response.status_code == 422, response.text

@pytest.mark.business
def test_create_account_duplicate_email(client, unique_phone, unique_email):
    """
    creating account wiht duplicate email one must return 201 & second must return 409.
    """
    # Prepare create account request payload
    payload = {
        "Name": "Test User",
        "Phone_Number": unique_phone,
        "email": unique_email,
        "Link_ID": "",
        "balance": 20.00
    }

    # Send a request
    response = client.post(client.base_url + "/v2/Account/create_Account",
                           json=payload)
    
    # First request must succeed and create the resource 201
    assert response.status_code == 201, response.text
 
    # Use same payload but diffrent phone number
    payload_2 = {
        **payload,
        "Phone_Number": unique_email
    }


    # Send a request
    response_2 = client.post(client.base_url + "/v2/Account/create_Account",
                             json=payload)
    
    # Second request reusing the same email 
    # must be rejected to prevent duplicate or conflicting operations
    assert response_2.status_code == 409, response_2.text

@pytest.mark.business
def test_create_account_duplicate_phone_number(client, unique_email,unique_phone):
    """
    creating account wiht duplicate phone number one must return 201 & second must return 409.
    """
    # Prepare create account request payload
    payload = {
        "Name": "Test User",
        "Phone_Number": unique_phone,
        "email": unique_email,
        "Link_ID": "",
        "balance": 20.00
    }

    # Send a request
    response = client.post(client.base_url + "/v2/Account/create_Account",
                           json=payload)
    
    # First request must succeed and create the resource 201
    assert response.status_code == 201, response.text

    # Use same payload but diffrent email
    payload_2 = {
        **payload,
        "email": unique_email
    }
    
    # Send a request
    response_2 = client.post(client.base_url + "/v2/Account/create_Account",
                             json=payload_2)
    
    # Second request reusing the same email 
    # must be rejected to prevent duplicate or conflicting operations
    assert response_2.status_code == 409, response_2.text