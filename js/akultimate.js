document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function getCSRFToken() {
        return csrfToken;
    }

    function fetchWithCSRF(url, options = {}) {
        const token = getCSRFToken();
        options.headers = {
            ...options.headers,
            'Content-Type': 'application/json',
        };

        if (options.method === 'POST' && options.body) {
            let body;
            try {
                body = JSON.parse(options.body);
            } catch (e) {
                body = {};
            }
            if (!body.csrf_token) {
                body.csrf_token = token;
            }
            options.body = JSON.stringify(body);
        }

        return fetch(url, options)
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        let errorMessage = text;
                        try {
                            const json = JSON.parse(text);
                            errorMessage = json.message || response.statusText;
                        } catch (e) {}
                        throw new Error('Error ' + response.status + ': ' + errorMessage);
                    });
                }
                return response.json();
            })
            .catch(error => {
                console.error('Error during fetch:', error);
                throw error;
            });
    }

    // Handle home button click
    const homeButton = document.getElementById('homeButton');
    if (homeButton) {
        homeButton.addEventListener('click', function () {
            window.location.href = '/';
        });
    }

    // Handle logo click
    const logoButton = document.getElementById('logoButton');
    if (logoButton) {
        logoButton.addEventListener('click', function () {
            window.location.href = '/';
        });
    }

    // Check if user is logged in (assuming you set this variable in your HTML)
    var isLoggedIn = typeof isLoggedIn !== 'undefined' ? isLoggedIn : false;

    // Handle registration button click
    const registrationBtn = document.getElementById('registerButton');
    if (registrationBtn) {
        registrationBtn.addEventListener('click', function (e) {
            e.preventDefault();

            if (isLoggedIn) {
                // Show warning modal
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = `
                    <div class="modal fade" id="warningModal" tabindex="-1" aria-labelledby="warningModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="warningModalLabel">Warning</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p>You are already logged in. You cannot register a new account while logged in.</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                const warningModalElement = tempDiv.firstElementChild;
                document.body.appendChild(warningModalElement);

                const warningModal = new bootstrap.Modal(warningModalElement);
                warningModal.show();

                warningModalElement.addEventListener('hidden.bs.modal', function () {
                    warningModalElement.remove();
                });
            } else {
                // Proceed to show the registration modal as before
                fetch('page/register.php')
                    .then(response => response.text())
                    .then(html => {
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = html;
                        const modalContainer = tempDiv.firstElementChild;

                        document.body.appendChild(modalContainer);

                        const registrationModal = new bootstrap.Modal(modalContainer);
                        registrationModal.show();

                        registrationModal._element.addEventListener('shown.bs.modal', function () {
                            const registrationForm = document.getElementById('registrationForm');
                            const passwordInput = document.getElementById('register_password');
                            const confirmPasswordInput = document.querySelector('input[name="confirm_password"]');
                            const passwordStrengthBar = document.getElementById('passwordStrengthBar');
                            const generatePasswordBtn = document.getElementById('generatePassword');
                            const captchaInput = document.querySelector('input[name="captcha"]');
                            const captchaImage = document.querySelector('.captcha-img');
                            const messageContainer = document.getElementById('messageContainer');

                            const tooltip = new bootstrap.Tooltip(generatePasswordBtn);

                            passwordInput.addEventListener('input', function () {
                                const strength = calculatePasswordStrength(passwordInput.value);
                                updatePasswordStrengthBar(strength);
                            });

                            generatePasswordBtn.addEventListener('click', function () {
                                const generatedPassword = generateStrongPassword();
                                passwordInput.value = generatedPassword;
                                confirmPasswordInput.value = generatedPassword;
                                passwordInput.type = 'text';
                                setTimeout(() => passwordInput.type = 'password', 10000);
                                updatePasswordStrengthBar(5);
                            });

                            registrationForm.addEventListener('submit', function (event) {
                                event.preventDefault();

                                const formData = new FormData(registrationForm);
                                const formJSON = {};
                                formData.forEach((value, key) => {
                                    formJSON[key] = value;
                                });
                                formJSON.csrf_token = getCSRFToken();

                                fetchWithCSRF('inc/register.php', {
                                    method: 'POST',
                                    body: JSON.stringify(formJSON),
                                })
                                    .then(data => {
                                        if (data.success) {
                                            messageContainer.innerHTML = '<div class="alert alert-success">Registration successful!</div>';
                                            registrationForm.reset();
                                        } else {
                                            messageContainer.innerHTML = '<div class="alert alert-danger">Error: ' + data.errors.join('<br>') + '</div>';
                                        }
                                        captchaImage.src = 'inc/captcha.php?action=image&rand=' + Math.random();
                                    })
                                    .catch(error => {
                                        messageContainer.innerHTML = '<div class="alert alert-danger">An unexpected error occurred. Please try again.</div>';
                                        console.error('Error:', error);
                                    });
                            });
                        });

                        registrationModal._element.addEventListener('hidden.bs.modal', function () {
                            modalContainer.remove();
                        });

                        function calculatePasswordStrength(password) {
                            let strength = 0;
                            if (password.length >= 8) strength++;
                            if (/[A-Z]/.test(password)) strength++;
                            if (/[a-z]/.test(password)) strength++;
                            if (/[0-9]/.test(password)) strength++;
                            if (/[@$!%*?&#]/.test(password)) strength++;
                            return strength;
                        }

                        function updatePasswordStrengthBar(strength) {
                            const width = (strength / 5) * 100 + '%';
                            passwordStrengthBar.style.width = width;

                            if (strength < 3) {
                                passwordStrengthBar.style.backgroundColor = 'red';
                            } else if (strength < 5) {
                                passwordStrengthBar.style.backgroundColor = 'orange';
                            } else {
                                passwordStrengthBar.style.backgroundColor = 'green';
                            }
                        }

                        function generateStrongPassword() {
                            const uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                            const lowercase = 'abcdefghijklmnopqrstuvwxyz';
                            const numbers = '0123456789';
                            const specialChars = '@$!%*?&#';
                            const allChars = uppercase + lowercase + numbers + specialChars;

                            let password = '';
                            password += uppercase[Math.floor(Math.random() * uppercase.length)];
                            password += lowercase[Math.floor(Math.random() * lowercase.length)];
                            password += numbers[Math.floor(Math.random() * numbers.length)];
                            password += specialChars[Math.floor(Math.random() * specialChars.length)];

                            for (let i = 4; i < 12; i++) {
                                password += allChars[Math.floor(Math.random() * allChars.length)];
                            }

                            return shuffleString(password);
                        }

                        function shuffleString(string) {
                            const array = string.split('');
                            for (let i = array.length - 1; i > 0; i--) {
                                const j = Math.floor(Math.random() * (i + 1));
                                [array[i], array[j]] = [array[j], array[i]];
                            }
                            return array.join('');
                        }
                    })
                    .catch(error => {
                        console.error('Error loading registration modal:', error);
                    });
            }
        });
    }

    // Handle login form submission
    const loginUsernameInput = document.getElementById('login_username');
    if (loginUsernameInput) {
        loginUsernameInput.addEventListener('input', function () {
            loginUsernameInput.value = loginUsernameInput.value.toLowerCase();
        });
    }

    // Handle login form submission
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function (event) {
            event.preventDefault();

            const username = loginUsernameInput.value;
            const password = document.getElementById('login_password').value;

            const data = {
                action: 'login',
                username: username,
                password: password,
            };

            fetchWithCSRF('inc/login.php', {
                method: 'POST',
                body: JSON.stringify(data),
            })
                .then(data => {
                    const loginMessageContainer = document.getElementById('loginMessageContainer');
                    if (data.success) {
                        loginMessageContainer.innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        loginMessageContainer.innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
                    }
                })
                .catch(error => {
                    console.error('Error during login:', error);
                });
        });
    }

    // Handle logout button click
    const logoutButton = document.getElementById('logoutButton');
    if (logoutButton) {
        logoutButton.addEventListener('click', function (event) {
            event.preventDefault();

            const data = {
                action: 'logout',
            };

            fetchWithCSRF('inc/login.php', {
                method: 'POST',
                body: JSON.stringify(data),
            })
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error during logout:', error);
                });
        });
    }

    // Function to attach event listeners to news item links
    function attachNewsLinks() {
        const newsLinks = document.querySelectorAll('.news-item-link');
        newsLinks.forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const newsId = link.getAttribute('data-news-id');

                fetch('page/news.php')
                    .then(response => response.text())
                    .then(html => {
                        const modalContainer = document.createElement('div');
                        modalContainer.innerHTML = html;
                        document.body.appendChild(modalContainer);

                        fetchWithCSRF('inc/news.php?id=' + newsId)
                            .then(data => {
                                if (data.success) {
                                    const modalTitle = document.getElementById('newsContentModalLabel');
                                    const modalBody = document.getElementById('newsContentBody');

                                    modalTitle.textContent = data.title;
                                    modalBody.innerHTML = data.content;

                                    const newsModal = new bootstrap.Modal(document.getElementById('newsContentModal'));
                                    newsModal.show();

                                    newsModal._element.addEventListener('hidden.bs.modal', function () {
                                        modalContainer.remove();
                                    });
                                } else {
                                    console.error('Error fetching news:', data.error);
                                }
                            })
                            .catch(error => {
                                console.error('Error loading news content:', error);
                            });
                    })
                    .catch(error => {
                        console.error('Error loading modal structure:', error);
                    });
            });
        });
    }

    // Function to attach event listeners to pagination links
    function attachPaginationLinks() {
        const paginationLinks = document.querySelectorAll('.pagination .page-link');
        paginationLinks.forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const page = link.getAttribute('data-page');

                fetch('index.php?page=' + page)
                    .then(response => response.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const newPageContent = doc.querySelector('.page-content').innerHTML;
                        const newPagination = doc.querySelector('.pagination').innerHTML;

                        const pageContent = document.querySelector('.page-content');
                        const pagination = document.querySelector('.pagination');

                        pageContent.innerHTML = newPageContent;
                        pagination.innerHTML = newPagination;

                        // Re-attach event listeners to the new news items and pagination links
                        attachNewsLinks();
                        attachPaginationLinks();
                    })
                    .catch(error => {
                        console.error('Error loading page content:', error);
                    });
            });
        });
    }

    // Initial attachment
    attachNewsLinks();
    attachPaginationLinks();

    // Handle download button click
    const downloadButton = document.querySelector('.start-game-link');
    if (downloadButton) {
        downloadButton.addEventListener('click', function (e) {
            e.preventDefault();

            const pageTitle = document.querySelector('.page-title');
            const pageContent = document.querySelector('.page-content');

            if (pageTitle) {
                pageTitle.innerHTML = '<span>Download</span>';
            }

            fetch('page/download.php')
                .then(response => response.text())
                .then(html => {
                    if (pageContent) {
                        pageContent.innerHTML = html;
                    }
                })
                .catch(error => {
                    console.error('Error loading download page:', error);
                });
        });
    }

    // Handle donation button click
    const donationButton = document.querySelector('.donation-link');
    if (donationButton) {
        donationButton.addEventListener('click', function (e) {
            e.preventDefault();

            const pageTitle = document.querySelector('.page-title');
            const pageContent = document.querySelector('.page-content');

            if (pageTitle) {
                pageTitle.innerHTML = '<span>Donation</span>';
            }

            fetch('page/donation.php')
                .then(response => response.text())
                .then(html => {
                    if (pageContent) {
                        pageContent.innerHTML = html;

                        const donateButtons = document.querySelectorAll('.donation-button');
                        donateButtons.forEach(function (button) {
                            button.addEventListener('click', function () {
                                const amount = button.getAttribute('data-amount');
                                const points = button.getAttribute('data-points');
                                const bonus = button.getAttribute('data-bonus');

                                const data = {
                                    amount: parseFloat(amount),
                                    points: parseInt(points),
                                    bonus: parseInt(bonus),
                                    csrf_token: getCSRFToken(),
                                };

                                fetchWithCSRF('inc/donation.php', {
                                    method: 'POST',
                                    body: JSON.stringify(data),
                                })
                                    .then(responseData => {
                                        if (responseData.success) {
                                            window.location.href = responseData.redirect_url;
                                        } else {
                                            const donationMessage = document.getElementById('donationMessage');
                                            donationMessage.innerHTML = '<div class="alert alert-danger">' + responseData.message + '</div>';
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Error processing donation:', error);
                                        const donationMessage = document.getElementById('donationMessage');
                                        donationMessage.innerHTML = '<div class="alert alert-danger">An error occurred while processing your donation. Please try again later.</div>';
                                    });
                            });
                        });
                    }
                })
                .catch(error => {
                    console.error('Error loading donation page:', error);
                });
        });
    }

    // Handle add news button click (for admins)
    const addNewsButton = document.getElementById('add-news-button');
    if (addNewsButton) {
        addNewsButton.addEventListener('click', function (e) {
            e.preventDefault();

            fetch('page/add_news.php')
                .then(response => response.text())
                .then(html => {
                    const modalContainer = document.createElement('div');
                    modalContainer.innerHTML = html;
                    document.body.appendChild(modalContainer);

                    const addNewsModal = new bootstrap.Modal(document.getElementById('addNewsModal'));
                    addNewsModal.show();

                    const addNewsForm = document.getElementById('addNewsForm');
                    const addNewsMessage = document.createElement('div');
                    addNewsForm.insertBefore(addNewsMessage, addNewsForm.firstChild);

                    if (addNewsForm) {
                        addNewsForm.addEventListener('submit', function (event) {
                            event.preventDefault();

                            const formData = new FormData(addNewsForm);
                            const data = {};
                            formData.forEach((value, key) => {
                                data[key] = value;
                            });

                            data.csrf_token = getCSRFToken();

                            fetchWithCSRF('inc/add_news.php', {
                                method: 'POST',
                                body: JSON.stringify(data),
                            })
                                .then(data => {
                                    if (data.success) {
                                        addNewsMessage.innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
                                        addNewsForm.reset();
                                        setTimeout(() => {
                                            addNewsModal.hide();
                                            window.location.reload();
                                        }, 2000);
                                    } else {
                                        addNewsMessage.innerHTML = '<div class="alert alert-danger">Error: ' + data.message + '</div>';
                                    }
                                })
                                .catch(error => {
                                    console.error('Error adding news:', error);
                                    addNewsMessage.innerHTML = '<div class="alert alert-danger">An unexpected error occurred. Please try again.</div>';
                                });
                        });
                    }

                    addNewsModal._element.addEventListener('hidden.bs.modal', function () {
                        modalContainer.remove();
                    });
                })
                .catch(error => {
                    console.error('Error loading add news modal:', error);
                });
        });
    }

    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});