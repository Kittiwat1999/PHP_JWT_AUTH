/**
 * Modern fetchData using the native Fetch API.
 */
async function fetchData(endpoint, method = "GET", body = null) {
    const accessToken = localStorage.getItem("access_token");
    const refreshToken = localStorage.getItem("refresh_token");
    const AccessTokenExpiry = localStorage.getItem("access_token_expiry");
    const username = localStorage.getItem("username");

    if (!accessToken || !refreshToken || !AccessTokenExpiry || !username) {
        alert("Please log in first!");
        window.location.href = "login.html";
        throw new Error("Unauthorized");
    }

    // Check for token expiration
    const now = (Math.floor(Date.now() / 1000));
    if (now >= parseInt(AccessTokenExpiry)) {
        console.log("Token expired, refreshing...");
        await refreshUserToken(refreshToken);
        // Recursively call fetchData with the new token
        return await fetchData(endpoint, method, body);
    }

    const options = {
        method: method,
        headers: {
            "Content-Type": "application/json",
            "Authorization": `Bearer ${accessToken}`
        }
    };

    if (body) {
        options.body = JSON.stringify(body);
    }

    try {
        const response = await fetch(CONFIG.BACKEND_URL + endpoint, options);
        
        if (response.status === 401) {
            window.location.href = "login.html";
            // throw new Error("Session expired");
        }

        const res = await response.json();

        if (!response.ok) {
            throw new Error(res.message || res.error || "Request failed");
        }

        return res;
    } catch (error) {
        console.error("Fetch error:", error);
        throw error;
    }
}

/**
 * Refreshes the user token using the native Fetch API.
 */
async function refreshUserToken(refreshToken) {
    const options = {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ refresh_token: refreshToken })
    };

    try {
        const response = await fetch(CONFIG.BACKEND_URL + "/public/refresh_token.php", options);
        const res = await response.json();
        if (res.success && res.data.access_token) {
            localStorage.setItem("access_token", res.data.access_token);
            localStorage.setItem("refresh_token", res.data.refresh_token);
            localStorage.setItem("access_token_expiry", res.data.access_token_expiry);
            localStorage.setItem("username", res.data.username);
            return true;
        } else {
            alert("Session expired. Please login again.");
            window.location.href = "login.html";
            throw new Error("Refresh failed");
        }
    } catch (error) {
        window.location.href = "login.html";
        throw error;
    }
}
