document.getElementById('loginForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const name = document.getElementById('name').value.trim();
    const userId = document.getElementById('userId').value.trim();
    const password = document.getElementById('password').value.trim();
    const errorMessage = document.getElementById('error-message');

    errorMessage.textContent = '';

    if (!name || !userId || !password) {
        errorMessage.textContent = 'Please fill in all fields';
        return;
    }

    // Submit the form if validation passes
    this.submit();
});