// src/utils/api.js

const API_BASE_URL = "http://localhost/api";

/**
 * Utility function to make API calls to the backend.
 * @param {string} endpoint The API endpoint (e.g., 'login', 'graphic-cards').
 * @param {string} method HTTP method (e.g., 'GET', 'POST', 'PUT', 'DELETE').
 * @param {object|null} data Data payload for POST/PUT requests.
 * @param {string|null} token Authentication token for protected routes.
 * @returns {Promise<object>} The JSON response from the API.
 * @throws {Error} If the API call fails or returns an error.
 */
export async function apiCall(
  endpoint,
  method = "GET",
  data = null,
  token = null
) {
  const headers = {
    "Content-Type": "application/json",
  };
  if (token) {
    headers["Authorization"] = `Bearer ${token}`;
  }

  const config = {
    method: method,
    headers: headers,
  };

  if (data) {
    config.body = JSON.stringify(data);
  }

  try {
    const response = await fetch(`${API_BASE_URL}/${endpoint}`, config);
    const result = await response.json();
    if (!response.ok) {
      // If response is not OK, throw an error with the message from the backend
      throw new Error(result.message || "Something went wrong on the server.");
    }
    return result;
  } catch (error) {
    // Log the error for debugging purposes
    console.error(`API call to ${endpoint} failed:`, error);
    // Re-throw the error so the calling component can handle it
    throw error;
  }
}
