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

