/**
 * Cosmos Pet Care App - Main JavaScript
 * Handles authentication, API calls, and common functionality
 */

// API base URL - Absolute path to correctly point to the API directory
const API_BASE_URL = '/CosmosPetCareApp/php/api';

// User session management
const AUTH = {
  token: localStorage.getItem('token'),
  user: JSON.parse(localStorage.getItem('user') || 'null'),

  // Check if user is logged in
  isLoggedIn() {
    return !!this.token;
  },

  // Get current user role
  getRole() {
    return this.user ? this.user.role : null;
  },

  // Store auth data after login/registration
  setAuth(token, user) {
    this.token = token;
    this.user = user;
    localStorage.setItem('token', token);
    localStorage.setItem('user', JSON.stringify(user));
  },

  // Clear auth data on logout
  clearAuth() {
    this.token = null;
    this.user = null;
    localStorage.removeItem('token');
    localStorage.removeItem('user');
  }
};

// API client
const API = {
  // Helper for making API requests
  async request(endpoint, method = 'GET', data = null) {
    const url = `${API_BASE_URL}/${endpoint}`;
    
    const headers = {
      'Content-Type': 'application/json'
    };
    
    // Add auth token if available
    if (AUTH.token) {
      headers['Authorization'] = `Bearer ${AUTH.token}`;
    }
    
    const options = {
      method,
      headers
    };
    
    if (data && (method === 'POST' || method === 'PUT')) {
      options.body = JSON.stringify(data);
    }
    
    try {
      const response = await fetch(url, options);
      
      // Log response status for debugging
      console.log(`API Response (${endpoint}):`, response.status);
      
      // Try to get response text first
      const responseText = await response.text();
      console.log(`API Response Text (${endpoint}):`, responseText);
      
      // Try to parse as JSON
      try {
        const result = JSON.parse(responseText);
        
        // If unauthorized, clear auth and redirect to login
        if (response.status === 401) {
          console.log('Unauthorized access, clearing auth...');
          AUTH.clearAuth();
          window.location.href = '/CosmosPetCareApp/login.html';
          return null;
        }
        
        return result;
      } catch (parseError) {
        console.error(`JSON Parse Error (${endpoint}):`, parseError);
        throw new Error('Invalid JSON response from server');
      }
    } catch (error) {
      console.error(`API Request Error (${endpoint}):`, error);
      return { 
        status: 'error', 
        message: 'Network error or invalid response. Please try again.' 
      };
    }
  },
  
  // Auth endpoints
  auth: {
    async login(email, password) {
      return API.request('login.php', 'POST', { email, password });
    },
    
    async register(userData) {
      return API.request('register.php', 'POST', userData);
    }
  },
  
  // Pet endpoints
  pets: {
    async add(petData) {
      return API.request('pets_add.php', 'POST', petData);
    },
    
    async getByOwner() {
      return API.request(`pets_get.php?owner_id=${AUTH.user.id}`);
    }
  },
  
  // Appointment endpoints
  appointments: {
    async book(appointmentData) {
      return API.request('appointments_book.php', 'POST', appointmentData);
    },
    
    async getPending() {
      return API.request('appointments_pending.php');
    },
    
    async update(appointmentData) {
      return API.request('appointments_update.php', 'POST', appointmentData);
    },
    
    async getByOwner() {
      return API.request(`appointments_get.php?owner_id=${AUTH.user.id}`);
    }
  },
  
  // Payment endpoints
  payments: {
    async create(paymentData) {
      return API.request('payments_create.php', 'POST', paymentData);
    },
    
    async getAll() {
      return API.request('payments_view.php');
    }
  },
  
  // User management endpoints
  users: {
    async getAll() {
      return API.request('users_manage.php');
    },
    
    async getByRole(role) {
      return API.request(`users_manage.php?role=${role}`);
    },
    
    async create(userData) {
      return API.request('users_manage.php', 'POST', userData);
    },
    
    async update(userData) {
      return API.request('users_manage.php', 'PUT', userData);
    },
    
    async delete(userId) {
      return API.request(`users_manage.php?id=${userId}`, 'DELETE');
    }
  }
};

// Utility functions
const UTILS = {
  // Format date to local string
  formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString();
  },
  
  // Format currency
  formatCurrency(amount) {
    return new Intl.NumberFormat('en-BD', { 
      style: 'currency', 
      currency: 'BDT' 
    }).format(amount);
  },
  
  // Show alert message
  showAlert(message, type = 'success', container = '.alert-container') {
    const alertContainer = document.querySelector(container);
    if (!alertContainer) return;
    
    const alertEl = document.createElement('div');
    alertEl.className = `alert alert-${type} alert-dismissible fade show`;
    alertEl.innerHTML = `
      ${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    alertContainer.appendChild(alertEl);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
      const dismissBtn = alertEl.querySelector('.btn-close');
      if (dismissBtn) dismissBtn.click();
    }, 5000);
  },
  
  // Redirect based on user role
  redirectByRole() {
    if (!AUTH.isLoggedIn()) {
      window.location.href = '/CosmosPetCareApp/login.html';
      return;
    }
    
    const role = AUTH.getRole();
    switch (role) {
      case 'admin':
        window.location.href = '/CosmosPetCareApp/admin/dashboard.html';
        break;
      case 'vet':
        window.location.href = '/CosmosPetCareApp/vet/dashboard.html';
        break;
      case 'customer':
        window.location.href = '/CosmosPetCareApp/customer/dashboard.html';
        break;
      default:
        window.location.href = '/CosmosPetCareApp/login.html';
    }
  }
};

// Check authentication status on each page load
document.addEventListener('DOMContentLoaded', function() {
  // Update UI based on auth status
  if (AUTH.isLoggedIn()) {
    document.querySelectorAll('.user-name').forEach(el => {
      el.textContent = AUTH.user.name;
    });
    
    document.querySelectorAll('.user-role').forEach(el => {
      el.textContent = AUTH.user.role.charAt(0).toUpperCase() + AUTH.user.role.slice(1);
    });
    
    document.querySelectorAll('.auth-only').forEach(el => {
      el.classList.remove('d-none');
    });
    
    document.querySelectorAll('.guest-only').forEach(el => {
      el.classList.add('d-none');
    });
    
    // Show role-specific elements
    const role = AUTH.getRole();
    document.querySelectorAll(`.${role}-only`).forEach(el => {
      el.classList.remove('d-none');
    });
  } else {
    document.querySelectorAll('.auth-only').forEach(el => {
      el.classList.add('d-none');
    });
    
    document.querySelectorAll('.guest-only').forEach(el => {
      el.classList.remove('d-none');
    });
  }
  
  // Handle logout buttons
  document.querySelectorAll('.logout-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      AUTH.clearAuth();
      window.location.href = '/CosmosPetCareApp/login.html';
    });
  });
});

async function processPayment(e) {
  e.preventDefault();
  
  if (!currentPaymentId) {
    UTILS.showAlert('No payment selected', 'danger');
    return;
  }
  
  // Get form data
  const paymentData = {
    payment_id: currentPaymentId,
    card_name: document.getElementById('card-name').value,
    card_number: document.getElementById('card-number').value,
    expiry_date: document.getElementById('expiry-date').value,
    cvv: document.getElementById('cvv').value,
    zip_code: document.getElementById('zip-code').value
  };
  
  // Validate form - very basic validation for demo
  if (!paymentData.card_name || !paymentData.card_number || !paymentData.expiry_date || !paymentData.cvv || !paymentData.zip_code) {
    UTILS.showAlert('Please fill in all payment fields', 'danger');
    return;
  }
  
  try {
    // Show loading state
    const submitBtn = document.getElementById('submit-payment-btn');
    const originalBtnText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<span class="loading-spinner"></span> Processing...';
    submitBtn.disabled = true;
    
    // Process payment - This is a dummy implementation
    // In a real app, this would connect to a payment gateway
    setTimeout(async () => {
      try {
        // Generate random transaction ID
        const transactionId = 'TRX-' + Math.random().toString(36).substr(2, 8).toUpperCase();
        
        // Submit payment data
        const response = await fetch(`${API_BASE_URL}/payments_update.php`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${AUTH.token}`
          },
          body: JSON.stringify({
            payment_id: currentPaymentId,
            status: 'completed',
            transaction_id: transactionId
          })
        });
        const result = await response.json();
      
      if (result.status === 'success') {
        UTILS.showAlert('Payment processed successfully', 'success');
        // Redirect or update UI as needed
      } else {
        UTILS.showAlert(result.message || 'Payment processing failed', 'danger');
      }
    } catch (error) {
      console.error('Payment processing error:', error);
      UTILS.showAlert('Payment processing error. Please try again.', 'danger');
    } finally {
      // Reset loading state
      submitBtn.innerHTML = originalBtnText;
      submitBtn.disabled = false;
    }
  }, 2000);
} catch (error) {
  console.error('Payment processing error:', error);
  UTILS.showAlert('Payment processing error. Please try again.', 'danger');
}
}

async function loadPaymentDetails(paymentId) {
  try {
    // Always load payment details from API
    const response = await fetch(`${API_BASE_URL}/payments_view.php?id=${paymentId}`, {
      headers: { 'Authorization': `Bearer ${AUTH.token}` }
    });
    const payment = await response.json();
    
    // Render invoice items using payment.amount
    const amount = parseFloat(payment.amount);
    const description = payment.description || payment.service_type || 'Payment';
    const itemsHtml = `<tr><td>${description}</td><td class="text-end">$${amount.toFixed(2)}</td></tr>`;
    document.getElementById('invoice-items').innerHTML = itemsHtml;
    document.getElementById('invoice-total').textContent = `$${amount.toFixed(2)}`;
    document.getElementById('payment-amount').textContent = `$${amount.toFixed(2)}`;
    
    // Show or hide payment form
    if (payment.status === 'pending') {
      document.getElementById('payment-form').classList.remove('d-none');
    } else {
      document.getElementById('payment-form').classList.add('d-none');
    }
    
    // Show details view
    showPaymentDetailView();
  } catch (error) {
    console.error('Error loading payment details:', error);
    UTILS.showAlert('Error loading payment details. Please try again.', 'danger');
  }
}