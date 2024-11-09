"use strict";

(function () {
  /*
   * Initialize global components through loadComponent function
   */
  async function initGlobal() {
    try {
      await loadComponent('/jaywing-academy/src/components/footer.html', 'footer');

    } catch (error) {
      console.error('Error loading components:', error);
    }
  }

    /*
   * Takes url path and element id to load component
   * @param {string, string} path and element id to load component
   */
  async function loadComponent(url, elementId) {
      try {
        const response = await fetch(url);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const html = await response.text();
        const element = document.getElementById(elementId);
        if (element) {
            element.innerHTML = html;
            // Execute any scripts that were in the loaded content
            const scripts = element.getElementsByTagName('script');
            for (let script of scripts) {
                eval(script.innerHTML);
            }
        }
    } catch (error) {
        console.error(`Error loading ${url}:`, error);
    }
    }

  // Initialize global components when window is loaded
  window.addEventListener("load", initGlobal);

  async function handleLogin(event) {
    event.preventDefault();
    
    let profile = document.getElementById('profile-tab');

    const formData = new FormData();
    formData.append('email', document.getElementById('loginEmail').value);
    formData.append('password', document.getElementById('loginPassword').value);
    
    try {
      const response = await fetch('/jaywing-academy/src/data_src/api/login/login.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();

        if (data.success) {
          if (profile && data.isLoggedIn) {
              profile.classList.remove("hidden");
          }
          
          window.location.reload(); // Refresh page to show logged-in state
        } else {
            alert(data.message);
        }
    } catch (error) {
        console.error('Login error:', error);
        alert('Login failed. Please try again.');
    }
  }

  async function handleSignup(event) {
  event.preventDefault();

  if (document.getElementById('signupPassword').value !== document.getElementById('confirmPassword').value) {
      alert('Passwords do not match');
      return;
  }

  const formData = new FormData();
  formData.append('username', document.getElementById('signupUsername').value);
  formData.append('firstName', document.getElementById('signupFirstName').value);
  formData.append('lastName', document.getElementById('signupLastName').value);
  formData.append('email', document.getElementById('signupEmail').value);
  formData.append('password', document.getElementById('signupPassword').value);
  formData.append('confirm_password', document.getElementById('confirmPassword').value);

  try {
      const response = await fetch('/jaywing-academy/src/data_src/api/login/signup.php', {
          method: 'POST',
          body: formData
      });

      const data = await response.json();

      if (data.success) {
          window.location.reload();
      } else {
          alert(data.message);
      }
  } catch (error) {
      console.error('Signup error:', error);
      alert('Signup failed. Please try again.');
  }
  }

    // Expose functions to the global scope
    window.handleLogin = handleLogin;
    window.handleSignup = handleSignup;
}) ();