import pytest

@pytest.mark.auth
def test_students_resolve_passes_auth(client):
    """
    Valid API key must pass Auth middleware.
    Business logic may still reject the request.
    """
    # Send a request
    response = client.get(client.base_url + "/v1/students/resolve")

    # Must NOT be auth failure
    assert response.status_code!= 401

@pytest.mark.auth
def test_students_resolve_returns_json(client):
    """
    Endpoint must always return JSON.
    """
    # Send a request
    response = client.get(client.base_url + "/v1/students/resolve")

    # API contract: JSON respons
    assert response.headers["Content-Type"].startswith("application/json")
    # Parse JSON payload
    data = response.json()
    # Checks the API returned a JSON object
    assert isinstance(data,dict)

@pytest.mark.auth
def test_student_resolve_error_contract(client):
    """
    Business errors must follow JSON error contract.
    """
    # Send a request
    response = client.get(client.base_url + "/v1/students/resolve" )
    # Parse JSON payload
    data = response.json()
    # Either success OR error — but never auth error
    assert "error" in data or "data" in data

@pytest.mark.business
def test_student_resolve_success(client):
    """
    Resolving an existing student must return the correct student data.
    """
    # Send a request
    response = client.get(client.base_url + "/v1/students/resolve" , 
                          params={"link_id": "001"})
    
    # Request must succeed with HTTP 200 OK 
    assert response.status_code == 200

    # Parse the JSON response body
    data = response.json()

    # Checks the API returned a JSON object
    assert isinstance(data, dict)

    # resolved student must include a link_id field and same user that was asked for "001"
    assert "link_id" in data
    assert data["link_id"] == "001"

@pytest.mark.business
def test_student_resolve_not_found(client):
    """
    Resolving an existing student must return the correct student data.
    """
    # Send a request
    response = client.get(client.base_url + "/v1/students/resolve", 
                          params={"link_id" : "99999"})
    
    # Request must fail with HTTP 404 not found 
    assert response.status_code == 404

    # Parse the JSON response body
    data = response.json()
    
    # Errors MUST be returned under the `error` key
    assert "error" in data
    # Assert correct error message
    assert data["error"] == "not found for this link_id or no card for this account"