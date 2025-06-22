// src/utils/api.js

const API_BASE_URL = "http://localhost/api";

/**
 * Utility function to make API calls to the backend.
 * @param {string} endpoint The API endpoint (e.g., 'login', 'graphic-cards').
 * @param {string} method HTTP method (e.g., 'GET', 'POST', 'PUT', 'DELETE').
 * @param {object|FormData|null} data Data payload for POST/PUT requests. Can be an object (for JSON) or FormData (for file uploads).
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
  const headers = {}; // Initialize headers object

  // Add Authorization header if a token is provided
  if (token) {
    headers["Authorization"] = `Bearer ${token}`;
  }

  const config = {
    method: method,
    headers: headers,
  };

  if (data) {
    // Check if the data is an instance of FormData (used for file uploads)
    if (data instanceof FormData) {
      // If it's FormData, the browser will automatically set the 'Content-Type'
      // header to 'multipart/form-data' with the correct boundary.
      // Explicitly setting 'Content-Type' here would break it.
      config.body = data;
      // Remove 'Content-Type' from headers if it was accidentally added for FormData
      delete config.headers["Content-Type"];
    } else {
      // If it's not FormData, assume it's a regular JSON payload
      headers["Content-Type"] = "application/json";
      config.body = JSON.stringify(data);
    }
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
