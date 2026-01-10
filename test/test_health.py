import requests

BASE_URL = "http://localhost:8000"

def test_api_health():
    response = requests.get(f"{BASE_URL}/v1/ping")

    # HTTP status must be 200
    assert response.status_code == 200

    # Response must be in JSON
    data = response.json()
    assert isinstance(data, dict)

    # Contract validation, contract check
    assert "status" in data
