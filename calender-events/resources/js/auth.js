
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(loginForm);
            const submitBtn = loginForm.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            
            // Show loading state
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging in...';
            submitBtn.disabled = true;
            
            fetch(loginForm.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.errors) {
                    // Handle validation errors
                    Object.keys(data.errors).forEach(field => {
                        const errorElement = document.createElement('span');
                        errorElement.className = 'error-message';
                        errorElement.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${data.errors[field][0]}`;
                        
                        const inputGroup = document.querySelector(`input[name="${field}"]`).parentNode;
                        const existingError = inputGroup.querySelector('.error-message');
                        
                        if (existingError) {
                            existingError.remove();
                        }
                        
                        inputGroup.appendChild(errorElement);
                        inputGroup.querySelector('input').classList.add('is-invalid');
                    });
                } else if (data.redirect) {
                    // Successful login
                    window.location.href = data.redirect;
                }
            })
            .catch(error => {
                console.error('Error:', error);
            })
            .finally(() => {
                submitBtn.innerHTML = originalBtnText;
                submitBtn.disabled = false;
            });
        });
    }
});