<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Tripistry Signup Page</title>
        <style>
            html, body{
                height: 100%;
                margin: 0;
                padding: 0;
                font-family: 'Segoe UI', sans-serif;
                background: #f0f2f6;
                overflow: hidden;
            }
            .container{
                display: flex;
                width: 100%;
                height: 100vh;
                position: relative;
            }
            .left-panel{
                width:50%;
                position: relative;
                min-height: 100vh;
                background-image: linear-gradient(rgba(0,0,0,0.2), rgba(0,0,0,0.4)), url('travellers.jpg');
                background-repeat: no-repeat;
                background-size: cover;
                background-position: center center;
                color: white;
                box-sizing: border-box;
            }
            .logo{
                position: absolute;
                top: 32px;
                right: 32px;
                width: 130px;
                height: auto;
                z-index: 10;
                mix-blend-mode: multiply;
            }
            .right-panel{
                width: 50%;
                height: 100%;
                display: flex;
                align-items: flex-start;
                justify-content: center;
                padding: 60px 5%;
                background: #f1f5f9;
                overflow-y: auto;
                box-sizing: border-box;
                background-image: radial-gradient(#cbd5e1 1px, transparent 1px);
                background-size: 20px 20px;
            }
            .form-container{
                width: 100%;
                max-width: 520px;
            }
            .form-container h3{
                font-size: 32px;
                color: #0f172a;
                margin: 0 0 8px 0;
                text-align: center;
                font-weight: 700;
            }
            .form-container > p{
                color: #64748b;
                font-size: 15px;
                text-align: center;
                margin: 0 0 32px 0;
            }
            .form-subtitle {
                font-size: 16px;
                font-weight: 600;
                color: #1e293b;
                text-align: center;
                margin: 24px 0 4px 0;
            }
            .form-desc {
                font-size: 13px;
                color: #64748b;
                text-align: center;
                margin: 0 0 20px 0;
            }
            .role-selector{
                display: flex;
                gap: 16px;
                margin-bottom: 24px;
            }
            .role-card{
                flex: 1;
                padding: 16px;
                border: 2px solid #e2e8f0;
                border-radius: 12px;
                cursor: pointer;
                display: flex;
                align-items: center;
                gap: 14px;
                transition: all 0.2s ease;
                background: #ffffff;
            }
            .role-card div{
                display: flex;
                flex-direction: column;
                text-align: left;
            }
            .role-card strong{ font-size: 15px; color: #1e293b; }
            .role-card p{ margin: 4px 0 0 0; font-size: 13px; color: #64748b; }
            .role-card.active{ border-color: #2563eb; background: #2563eb; }
            .role-card.active strong{ color: #ffffff; }
            .role-card.active p{ color: #bfdbfe; }
            .role-card.active .icon{ filter: brightness(0) invert(1); }
            .form-group { margin-bottom: 16px; position: relative; }
            label{ font-weight: 500; font-size: 14px; color: #344054; display: block; margin-bottom: 6px; }
            .input-wrapper { position: relative; display: flex; align-items: center; }
            .input-wrapper .input-icon {
                position: absolute; left: 14px; font-size: 16px;
                color: #94a3b8; pointer-events: none; filter: grayscale(100%);
            }
            input[type="text"],input[type="email"],input[type="password"],input[type="date"],select,textarea{
                width: 100%;
                padding: 12px 14px 12px 42px;
                border: 1.5px solid #cbd5e1;
                border-radius: 8px;
                font-size: 14px;
                box-sizing: border-box;
                outline: none;
                transition: border-color 0.2s ease;
                font-family: inherit;
                background: #ffffff;
                color: #1e293b;
            }
            .no-icon{ padding-left: 14px; }
            textarea{ resize: vertical; min-height: 90px; }
            input:focus, select:focus, textarea:focus{
                border-color: #2563eb;
                box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
            }
            input[type="submit"]{
                width: 100%; padding: 15px; background: #2563eb; color: white;
                border: none; border-radius: 8px; font-size: 16px; font-weight: 600;
                cursor: pointer; margin-top: 8px;
            }
            input[type="submit"]:hover{ background: #1d4ed8; }
            input::placeholder, textarea::placeholder{ color: #94a3b8; }
            .btn-home {
                position: absolute; top: 20px; left: 20px; color: #ffffff;
                text-decoration: none; font-size: 14px; font-weight: 500;
                background: rgba(0,0,0,0.3); padding: 8px 14px; border-radius: 6px;
                z-index: 10; transition: background 0.2s;
            }
            .btn-home:hover{ background: rgba(0,0,0,0.5); }
            .footer-text{ text-align: center; margin-top: 24px; font-size: 14px; color: #64748b; }
            .footer-text a{ color: #2563eb; text-decoration: none; font-weight: 600; }
            .footer-text a:hover{ text-decoration: underline; }
            .error-msg{
                background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5;
                border-radius: 8px; padding: 10px 14px; font-size: 14px;
                margin-bottom: 16px; display: none;
            }
            .success-msg{
                background: #dcfce7; color: #15803d; border: 1px solid #86efac;
                border-radius: 8px; padding: 10px 14px; font-size: 14px;
                margin-bottom: 16px; display: none;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="left-panel">
                <a href="landing_page.php" class="btn-home">← Back to Home</a>
                <img src="Tripistry_logo.jpg" alt="Tripistry Logo" class="logo">
                <div style="position: absolute; bottom: 40px; left: 32px; color: white;">
                    <h1 style="font-size: 36px; margin-bottom: 8px;">Join Tripistry Today!</h1>
                    <p style="font-size: 16px; opacity: 0.85;">Create an account and start your journey with us.</p>
                </div>
            </div>

            <div class="right-panel">
                <div class="form-container">
                    <h3>Sign Up</h3>
                    <p>Create your account to get started</p>

                    <div class="role-selector">
                        <div class="role-card active" data-role="traveller" onclick="selectRole(this)">
                            <span class="icon">👤</span>
                            <div>
                                <strong>Traveller</strong>
                                <p>I want to explore</p>
                            </div>
                        </div>
                        <div class="role-card" data-role="agency" onclick="selectRole(this)">
                            <span class="icon">🏢</span>
                            <div>
                                <strong>Agency</strong>
                                <p>I want to list packages</p>
                            </div>
                        </div>
                    </div>

                    <div id="form-header-context">
                        <div class="form-subtitle">Traveller Sign Up</div>
                        <div class="form-desc">Fill in your details to create your traveller account</div>
                    </div>

                    <div id="msg-box-traveller" class="error-msg"></div>
                    <div id="success-box-traveller" class="success-msg"></div>

                    <!-- ======= TRAVELLER FORM ======= -->
                    <form id="traveller-form" action="api.php" method="POST">
                        <!-- type=Register, presence of fname/passport_no tells PHP it's a traveller -->
                        <input type="hidden" name="type" value="Register">

                        <div class="form-group">
                            <label for="fname">First Name:</label>
                            <div class="input-wrapper">
                                <span class="input-icon">👤</span>
                                <!-- name="fname" — matches $data["fname"] in PHP -->
                                <input type="text" id="fname" name="fname" placeholder="First Name" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="surname">Surname:</label>
                            <div class="input-wrapper">
                                <span class="input-icon">👤</span>
                                <!-- FIX: was id="fname" name="fname" — now correctly name="surname" -->
                                <input type="text" id="surname" name="surname" placeholder="Surname" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="t-email">Email:</label>
                            <div class="input-wrapper">
                                <span class="input-icon">✉</span>
                                <input type="email" id="t-email" name="email" placeholder="Email" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="t-password">Password:</label>
                            <div class="input-wrapper">
                                <span class="input-icon">🔒</span>
                                <input type="password" id="t-password" name="password" placeholder="Password" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="passport_no">Passport Number:</label>
                            <div class="input-wrapper">
                                <span class="input-icon">🛂</span>
                                <input type="text" id="passport_no" name="passport_no" placeholder="Passport Number" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="date_of_birth">Date of Birth:</label>
                            <div class="input-wrapper">
                                <span class="input-icon">📅</span>
                                <input type="date" id="date_of_birth" name="date_of_birth" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="gender">Gender:</label>
                            <select id="gender" name="gender" class="no-icon" required>
                                <option value="">(Select one)</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="nationality">Nationality:</label>
                            <div class="input-wrapper">
                                <span class="input-icon">🌍</span>
                                <input type="text" id="nationality" name="nationality" placeholder="Nationality" required>
                            </div>
                        </div>

                        <input type="submit" value="Register">
                    </form>

                    <div id="msg-box-agency" class="error-msg"></div>
                    <div id="success-box-agency" class="success-msg"></div>

                    <!-- ======= AGENCY FORM ======= -->
                    <form id="agency-form" action="api.php" method="POST" style="display:none;">
                        <!-- type=Register, presence of agency_name/registration_no tells PHP it's an agency -->
                        <input type="hidden" name="type" value="Register">

                        <div class="form-group">
                            <label for="agency_name">Agency Name:</label>
                            <div class="input-wrapper">
                                <span class="input-icon">🏢</span>
                                <input type="text" id="agency_name" name="agency_name" placeholder="Agency Name" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="a_email">Email:</label>
                            <div class="input-wrapper">
                                <span class="input-icon">✉</span>
                                <input type="email" id="a_email" name="a_email" placeholder="Email" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="a_pword">Password:</label>
                            <div class="input-wrapper">
                                <span class="input-icon">🔒</span>
                                <!-- FIX: was name="a_pword" already correct — keeping it -->
                                <input type="password" id="a_pword" name="a_pword" placeholder="Password" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="registration_no">Registration Number:</label>
                            <div class="input-wrapper">
                                <span class="input-icon">🪪</span>
                                <input type="text" id="registration_no" name="registration_no" placeholder="Registration Number" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="website">Website:</label>
                            <div class="input-wrapper">
                                <span class="input-icon">🌐</span>
                                <input type="text" id="website" name="website" placeholder="https://example.com">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Description:</label>
                            <div class="input-wrapper">
                                <span class="input-icon" style="top: 14px; transform: none;">📝</span>
                                <textarea id="description" name="description" placeholder="Enter agency description"></textarea>
                            </div>
                        </div>

                        <input type="submit" value="Register">
                    </form>

                    <p class="footer-text">Already have an account? <a href="login.php">Login</a></p>
                </div>
            </div>
        </div>

        <script>
            function selectRole(el) {
                document.querySelectorAll('.role-card').forEach(c => c.classList.remove('active'));
                el.classList.add('active');

                const isTraveller = el.getAttribute('data-role') === 'traveller';
                document.getElementById('traveller-form').style.display = isTraveller ? 'block' : 'none';
                document.getElementById('agency-form').style.display    = isTraveller ? 'none'  : 'block';

                document.getElementById('form-header-context').innerHTML = isTraveller ? `
                    <div class="form-subtitle">Traveller Sign Up</div>
                    <div class="form-desc">Fill in your details to create your traveller account</div>
                ` : `
                    <div class="form-subtitle">Agency Sign Up</div>
                    <div class="form-desc">Fill in your details to create your agency account</div>
                `;
            }

            // Intercept both forms with fetch so we can show inline errors/success
            // instead of a blank JSON page
            function attachFormHandler(formId, msgBoxId, successBoxId) {
                document.getElementById(formId).addEventListener('submit', async function(e) {
                    e.preventDefault();

                    const msgBox     = document.getElementById(msgBoxId);
                    const successBox = document.getElementById(successBoxId);
                    msgBox.style.display     = 'none';
                    successBox.style.display = 'none';

                    const formData = new FormData(this);
                    const body     = new URLSearchParams(formData).toString();

                    try {
                        const res  = await fetch('api.php', { method: 'POST', body: formData });
                        const json = await res.json();

                        if (json.status === 'success') {
                            // Store apikey for use across the app
                            localStorage.setItem('apikey',    json.data.apikey);
                            localStorage.setItem('user_type', json.data.user_type);
                            localStorage.setItem('user_id',   json.data.user_id);

                            successBox.textContent  = 'Account created! Redirecting...';
                            successBox.style.display = 'block';

                            setTimeout(() => {
                                window.location.href = 'landing_page.php';
                            }, 1500);
                        } else {
                            msgBox.textContent  = json.data || 'Something went wrong.';
                            msgBox.style.display = 'block';
                        }
                    } catch (err) {
                        msgBox.textContent  = 'Network error. Please try again.';
                        msgBox.style.display = 'block';
                    }
                });
            }

            attachFormHandler('traveller-form', 'msg-box-traveller', 'success-box-traveller');
            attachFormHandler('agency-form',    'msg-box-agency',    'success-box-agency');
        </script>
    </body>
</html>
