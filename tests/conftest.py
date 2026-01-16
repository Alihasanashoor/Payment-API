import os
import pytest
import requests

# Shared invalid monetary values for business-rule tests
INVALID_AMOUNTS = [0 ,-1.00, -10.5]

# Shared missing required fields for business-rule tests
MISSING_REQUIRED_FIELDS = [
        {"Amount":1.00, "product": "TEST", "idempotency_key": "missing-card_id"},
        {"card_id": 932, "product": "TEST", "idempotency_key": "missing-amount"},
        {"card_id": 932, "Amount": 1.00, "idempotency_key": "missing-product"},
        {"card_id": 932, "Amount": 1.00, "product": "missing-idem"},
        ]


# Global configuration
@pytest.fixture(scope="session")
def base_url():
    """
    Base URL for the Payment API.
    Must be provided via environment variable.
    """
    url = os.getenv("API_BASE_URL")
    if not url:
        raise RuntimeError("API base URL environment variable is not set")
    return url

@pytest.fixture(scope="session")
def api_key():
    """
    Test API key used for authenticated requests.
    The raw key is sent in requests; only its hash exists in the DB.
    """
    key = os.getenv("API_KEY")
    if not key:
        raise RuntimeError("API Key environment variable is not set")
    return key

@pytest.fixture(scope="session")
def auth_headers(api_key):
    """
    Default authentication headers for API requests.
    """
    return{
        "X-API-Key" : api_key,
        "Content-Type": "application/json",
    }


# -------------------------------------------------------------------
# HTTP client fixture
# -------------------------------------------------------------------

@pytest.fixture
def client(base_url, auth_headers):
    """
    Reusable HTTP client for API requests.
    Automatically includes base URL and auth headers.
    """
    session = requests.Session()
    # Every request sent by this client will automatically include API key headers
    session.headers.update(auth_headers)    
    session.base_url = base_url         # client.get(client.base_url + "/v1/ping")
    return session