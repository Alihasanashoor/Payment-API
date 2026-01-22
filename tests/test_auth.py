import requests
import pytest
    
@pytest.mark.auth
def test_missing_api_key(base_url):
    """
    Protected endpoint without API key must be rejected.
    """
    response = requests.get(base_url+ "/v1/students/resolve")

    assert response.status_code == 401, response.text

    data = response.json()
    assert data["error"] == "Missing API key"

@pytest.mark.auth
def test_invalid_api_key(base_url):
    """
    Protected endpoint with invalid API key must be rejected.
    """
    headers = {
        "X-API-Key": "invalid-api-key",
        "Content-Type": "application/json"
    }

    response = requests.get(base_url + "/v1/students/resolve", headers=headers)

    assert response.status_code == 401

    data= response.json()
    assert data["error"] == "Invalid or expired API key"